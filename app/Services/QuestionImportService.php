<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\Question;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * استيراد أسئلة الامتحان بالجملة من ملف CSV أو Excel (xlsx)
 * بدون حزم خارجية: CSV عبر fgetcsv، و xlsx عبر ZipArchive/SimpleXML.
 *
 * كل سطر يُتحقق منه على حدة — الأسطر الصحيحة تُستورد والخاطئة تُبلَّغ برقمها.
 */
class QuestionImportService
{
    /** الحد الأقصى لعدد الأسئلة في الملف الواحد */
    public const MAX_ROWS = 500;

    /** حروف الاختيارات بالترتيب: العمود option_a يقابل A وهكذا */
    protected const OPTION_LETTERS = ['a', 'b', 'c', 'd', 'e', 'f'];

    /** أعمدة القالب القياسية */
    public const COLUMNS = [
        'type', 'question_text', 'question_image_url', 'question_audio_url',
        'option_a', 'option_b', 'option_c', 'option_d', 'option_e', 'option_f',
        'correct_answer', 'points', 'subject', 'hint',
        'explanation', 'explanation_image_url', 'explanation_video_url',
    ];

    /** أسماء بديلة مقبولة لرؤوس الأعمدة */
    protected const HEADER_ALIASES = [
        'body' => 'question_text',
        'question' => 'question_text',
        'image_url' => 'question_image_url',
        'question_image' => 'question_image_url',
        'audio_url' => 'question_audio_url',
        'question_audio' => 'question_audio_url',
        'answer' => 'correct_answer',
        'correct' => 'correct_answer',
        'correct_option' => 'correct_answer',
        'degree' => 'points',
        'marks' => 'points',
        'score' => 'points',
        'answer_explanation' => 'explanation',
        'explanation_image' => 'explanation_image_url',
        'explanation_video' => 'explanation_video_url',
    ];

    /**
     * تنفيذ الاستيراد وإرجاع النتيجة.
     *
     * @return array{imported: int, errors: string[]}
     */
    public function import(Exam $exam, UploadedFile $file): array
    {
        $rows = $this->readRows($file);

        if (count($rows) < 2) {
            throw new \RuntimeException('الملف فارغ أو لا يحتوي على أي أسطر أسئلة بعد سطر العناوين.');
        }

        $headerMap = $this->mapHeader(array_shift($rows));

        if (! in_array('question_text', $headerMap, true)) {
            throw new \RuntimeException('سطر العناوين غير صحيح — لم يتم العثور على عمود question_text. حمّل القالب الجاهز واستخدم نفس أسماء الأعمدة.');
        }

        if (count($rows) > self::MAX_ROWS) {
            throw new \RuntimeException('الحد الأقصى '.self::MAX_ROWS.' سؤال في الملف الواحد — قسّم الملف على أكثر من مرة.');
        }

        $imported = 0;
        $errors = [];
        $position = (int) $exam->questions()->max('position');

        foreach ($rows as $index => $cells) {
            $lineNumber = $index + 2; // +1 لسطر العناوين و +1 لأن الترقيم يبدأ من 1

            $row = $this->rowToAssoc($cells, $headerMap);

            if ($row === null) {
                continue; // سطر فارغ بالكامل
            }

            $error = $this->importRow($exam, $row, $position);

            if ($error !== null) {
                $errors[] = "السطر {$lineNumber}: {$error}";
            } else {
                $imported++;
                $position++;
            }
        }

        return ['imported' => $imported, 'errors' => $errors];
    }

    /** محتوى ملف القالب الجاهز (CSV بترميز UTF-8 مع BOM ليفتح صحيحاً في Excel) */
    public function templateCsv(): string
    {
        $rows = [
            self::COLUMNS,
            [
                'mcq', 'ما عاصمة جمهورية مصر العربية؟', 'https://example.com/images/question1.jpg', '',
                'القاهرة', 'الإسكندرية', 'الجيزة', 'أسوان', '', '',
                'A', '2', 'جغرافيا', 'فكر في أكبر مدينة من حيث السكان',
                'القاهرة هي العاصمة وأكبر المدن', 'https://example.com/images/explain1.jpg', 'https://example.com/videos/explain1.mp4',
            ],
            [
                'mcq', 'كم عدد أيام السنة الميلادية البسيطة؟', '', '',
                '360', '365', '366', '370', '', '',
                'B', '1', '', '', 'السنة البسيطة 365 يوماً والكبيسة 366', '', '',
            ],
            [
                'essay', 'اشرح الفرق بين التشبيه والاستعارة مع ذكر مثال لكل منهما.', '', '',
                '', '', '', '', '', '',
                '', '5', 'بلاغة', '', '', '', '',
            ],
        ];

        $handle = fopen('php://temp', 'r+');

        foreach ($rows as $row) {
            fputcsv($handle, $row, ',', '"', '');
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return "\xEF\xBB\xBF".$csv;
    }

    // ------------------------------------------------------------ استيراد سطر واحد

    /** يستورد سطراً واحداً ويرجع null عند النجاح أو رسالة الخطأ */
    protected function importRow(Exam $exam, array $row, int $position): ?string
    {
        $row = $this->normalizeRow($row);

        $validator = Validator::make($row, [
            'type' => ['required', Rule::in([Question::TYPE_MCQ, Question::TYPE_ESSAY])],
            'question_text' => ['required', 'string'],
            'question_image_url' => ['nullable', 'url', 'max:500'],
            'question_audio_url' => ['nullable', 'url', 'max:500'],
            'points' => ['required', 'numeric', 'min:0.25', 'max:100'],
            'subject' => ['nullable', 'string', 'max:60'],
            'hint' => ['nullable', 'string'],
            'explanation' => ['nullable', 'string'],
            'explanation_image_url' => ['nullable', 'url', 'max:500'],
            'explanation_video_url' => ['nullable', 'url', 'max:500'],
        ], [
            'type.in' => 'نوع السؤال يجب أن يكون mcq أو essay.',
        ], [
            'type' => 'نوع السؤال', 'question_text' => 'نص السؤال',
            'question_image_url' => 'رابط صورة السؤال', 'question_audio_url' => 'رابط صوت السؤال',
            'points' => 'الدرجة', 'subject' => 'المادة/الفرع', 'hint' => 'التلميح',
            'explanation' => 'شرح الإجابة', 'explanation_image_url' => 'رابط صورة الشرح',
            'explanation_video_url' => 'رابط فيديو الشرح',
        ]);

        if ($validator->fails()) {
            return implode(' — ', $validator->errors()->all());
        }

        $options = [];
        $correctLetter = null;

        if ($row['type'] === Question::TYPE_MCQ) {
            foreach (self::OPTION_LETTERS as $letter) {
                if (($row["option_{$letter}"] ?? '') !== '') {
                    $options[$letter] = $row["option_{$letter}"];
                }
            }

            if (count($options) < 2) {
                return 'السؤال الاختياري يحتاج اختيارين على الأقل (option_a و option_b).';
            }

            $correctLetter = $this->resolveCorrectLetter($row['correct_answer'] ?? '', $options);

            if ($correctLetter === null) {
                return 'الإجابة الصحيحة (correct_answer) غير محددة أو لا تطابق أي اختيار — استخدم A-F أو 1-6 أو نص الاختيار نفسه.';
            }
        }

        DB::transaction(function () use ($exam, $row, $options, $correctLetter, $position) {
            $question = $exam->questions()->create([
                'type' => $row['type'],
                'body' => $row['question_text'],
                'subject' => $row['subject'] ?: null,
                'points' => $row['points'],
                'image_path' => $row['question_image_url'] ?: null,
                'audio_path' => $row['question_audio_url'] ?: null,
                'hint' => $row['hint'] ?: null,
                'explanation' => $row['explanation'] ?: null,
                'explanation_image' => $row['explanation_image_url'] ?: null,
                'explanation_video_url' => $row['explanation_video_url'] ?: null,
                'position' => $position + 1,
            ]);

            $optionPosition = 0;

            foreach ($options as $letter => $body) {
                $question->options()->create([
                    'body' => $body,
                    'is_correct' => $letter === $correctLetter,
                    'position' => $optionPosition++,
                ]);
            }
        });

        return null;
    }

    /** توحيد قيم السطر: النوع والدرجة والأرقام العربية */
    protected function normalizeRow(array $row): array
    {
        $type = mb_strtolower(trim($row['type'] ?? ''));

        $row['type'] = match (true) {
            in_array($type, ['essay', 'مقالي'], true) => Question::TYPE_ESSAY,
            in_array($type, ['mcq', 'multiple_choice', 'choice', 'اختياري', 'اختيار من متعدد'], true) => Question::TYPE_MCQ,
            // بدون نوع صريح: وجود اختيارات يعني سؤالاً اختيارياً وإلا فمقالي
            $type === '' => $this->hasAnyOption($row) ? Question::TYPE_MCQ : Question::TYPE_ESSAY,
            default => $type,
        };

        $row['points'] = $this->convertArabicDigits($row['points'] ?? '') ?: '1';
        $row['correct_answer'] = $this->convertArabicDigits(trim($row['correct_answer'] ?? ''));

        return $row;
    }

    protected function hasAnyOption(array $row): bool
    {
        foreach (self::OPTION_LETTERS as $letter) {
            if (($row["option_{$letter}"] ?? '') !== '') {
                return true;
            }
        }

        return false;
    }

    /**
     * تحويل قيمة correct_answer إلى حرف العمود المقابل:
     * A-F أو 1-6 أو الحرف العربي أو نص الاختيار نفسه.
     */
    protected function resolveCorrectLetter(string $value, array $options): ?string
    {
        if ($value === '') {
            return null;
        }

        $normalized = mb_strtolower(trim($value));

        $arabicLetters = ['أ' => 'a', 'ا' => 'a', 'ب' => 'b', 'ج' => 'c', 'د' => 'd', 'ه' => 'e', 'هـ' => 'e', 'و' => 'f'];
        $normalized = $arabicLetters[$normalized] ?? $normalized;

        if (preg_match('/^[1-6]$/', $normalized)) {
            $normalized = self::OPTION_LETTERS[(int) $normalized - 1];
        }

        if (in_array($normalized, self::OPTION_LETTERS, true)) {
            return array_key_exists($normalized, $options) ? $normalized : null;
        }

        // مطابقة نص الاختيار نفسه
        foreach ($options as $letter => $body) {
            if (mb_strtolower(trim($body)) === mb_strtolower(trim($value))) {
                return $letter;
            }
        }

        return null;
    }

    // ------------------------------------------------------------ قراءة الملفات

    /** @return array<int, array<int, string>> */
    protected function readRows(UploadedFile $file): array
    {
        $path = $file->getRealPath();
        $extension = mb_strtolower($file->getClientOriginalExtension());
        $signature = (string) file_get_contents($path, false, null, 0, 8);

        // ملفات xlsx هي أرشيف ZIP يبدأ بـ PK — نتعرف عليها من المحتوى لا الامتداد فقط
        if (str_starts_with($signature, 'PK')) {
            return $this->readXlsx($path);
        }

        // صيغة xls القديمة (ملف OLE ثنائي) غير مدعومة بدون حزم خارجية
        if (str_starts_with($signature, "\xD0\xCF\x11\xE0")) {
            throw new \RuntimeException('صيغة .xls القديمة غير مدعومة — افتح الملف في Excel واحفظه بصيغة .xlsx أو .csv ثم أعد الرفع.');
        }

        if (! in_array($extension, ['csv', 'txt', 'xls', 'xlsx'], true)) {
            throw new \RuntimeException('صيغة الملف غير مدعومة — المسموح: CSV أو Excel (xlsx).');
        }

        return $this->readCsv((string) file_get_contents($path));
    }

    /** @return array<int, array<int, string>> */
    protected function readCsv(string $contents): array
    {
        // إزالة BOM وتحويل الترميز إن لم يكن UTF-8 (ملفات Excel العربية غالباً Windows-1256)
        if (str_starts_with($contents, "\xEF\xBB\xBF")) {
            $contents = substr($contents, 3);
        }

        if (! mb_check_encoding($contents, 'UTF-8')) {
            $contents = mb_convert_encoding($contents, 'UTF-8', 'Windows-1256');
        }

        // بعض إصدارات Excel تصدّر CSV بفاصلة منقوطة أو Tab
        $firstLine = strtok($contents, "\r\n") ?: '';
        $delimiter = ',';
        $best = substr_count($firstLine, ',');

        foreach ([';', "\t"] as $candidate) {
            if (substr_count($firstLine, $candidate) > $best) {
                $best = substr_count($firstLine, $candidate);
                $delimiter = $candidate;
            }
        }

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $contents);
        rewind($handle);

        $rows = [];

        while (($cells = fgetcsv($handle, null, $delimiter, '"', '')) !== false) {
            $rows[] = array_map(fn ($cell) => trim((string) $cell), $cells);
        }

        fclose($handle);

        return $rows;
    }

    /** قارئ xlsx خفيف: يفك أرشيف ZIP ويقرأ الورقة الأولى والنصوص المشتركة */
    protected function readXlsx(string $path): array
    {
        $zip = new \ZipArchive;

        if ($zip->open($path) !== true) {
            throw new \RuntimeException('تعذر فتح ملف Excel — تأكد أن الملف سليم وبصيغة .xlsx.');
        }

        try {
            $shared = $this->sharedStrings($zip);
            $sheetXml = $this->firstSheetXml($zip);
        } finally {
            $zip->close();
        }

        $sheet = simplexml_load_string($sheetXml);

        if ($sheet === false || ! isset($sheet->sheetData)) {
            throw new \RuntimeException('تعذر قراءة بيانات ورقة Excel.');
        }

        $rows = [];

        foreach ($sheet->sheetData->row as $rowNode) {
            $cells = [];
            $fallbackIndex = 0;

            foreach ($rowNode->c as $cellNode) {
                $reference = preg_replace('/\d+/', '', (string) $cellNode['r']);
                $columnIndex = $reference !== '' ? $this->columnIndex($reference) : $fallbackIndex;
                $fallbackIndex = $columnIndex + 1;

                $cells[$columnIndex] = trim($this->cellValue($cellNode, $shared));
            }

            if ($cells === []) {
                $rows[] = [];

                continue;
            }

            $row = array_fill(0, max(array_keys($cells)) + 1, '');

            foreach ($cells as $index => $value) {
                $row[$index] = $value;
            }

            $rows[] = $row;
        }

        return $rows;
    }

    /** @return string[] */
    protected function sharedStrings(\ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');

        if ($xml === false) {
            return [];
        }

        $document = simplexml_load_string($xml);

        if ($document === false) {
            return [];
        }

        $strings = [];

        foreach ($document->si as $item) {
            if (isset($item->t)) {
                $strings[] = (string) $item->t;

                continue;
            }

            // نص منسق مقسم على أجزاء <r><t>
            $parts = '';

            foreach ($item->r as $run) {
                $parts .= (string) $run->t;
            }

            $strings[] = $parts;
        }

        return $strings;
    }

    /** مسار الورقة الأولى من workbook.xml وعلاقاته، مع مسار افتراضي شائع */
    protected function firstSheetXml(\ZipArchive $zip): string
    {
        $workbook = $zip->getFromName('xl/workbook.xml');
        $rels = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if ($workbook !== false && $rels !== false) {
            $workbookXml = simplexml_load_string($workbook);
            $relsXml = simplexml_load_string($rels);

            if ($workbookXml !== false && $relsXml !== false && isset($workbookXml->sheets->sheet[0])) {
                $relationId = (string) $workbookXml->sheets->sheet[0]
                    ->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];

                foreach ($relsXml->Relationship as $relationship) {
                    if ((string) $relationship['Id'] === $relationId) {
                        $target = ltrim((string) $relationship['Target'], '/');
                        $target = str_starts_with($target, 'xl/') ? $target : 'xl/'.$target;
                        $sheetXml = $zip->getFromName($target);

                        if ($sheetXml !== false) {
                            return $sheetXml;
                        }
                    }
                }
            }
        }

        $fallback = $zip->getFromName('xl/worksheets/sheet1.xml');

        if ($fallback === false) {
            throw new \RuntimeException('تعذر العثور على ورقة البيانات داخل ملف Excel.');
        }

        return $fallback;
    }

    /** قيمة الخلية حسب نوعها (نص مشترك / نص مباشر / رقم / منطقي) */
    protected function cellValue(\SimpleXMLElement $cell, array $shared): string
    {
        return match ((string) $cell['t']) {
            's' => $shared[(int) $cell->v] ?? '',
            'inlineStr' => (string) ($cell->is->t ?? ''),
            'b' => ((string) $cell->v) === '1' ? '1' : '0',
            default => (string) $cell->v,
        };
    }

    /** تحويل حرف العمود (A, B, ..., AA) إلى رقم يبدأ من صفر */
    protected function columnIndex(string $letters): int
    {
        $index = 0;

        foreach (str_split(strtoupper($letters)) as $letter) {
            $index = $index * 26 + (ord($letter) - 64);
        }

        return $index - 1;
    }

    // ------------------------------------------------------------ سطر العناوين

    /**
     * ربط رقم كل عمود باسمه القياسي مع قبول الأسماء البديلة.
     *
     * @return array<int, string>
     */
    protected function mapHeader(array $header): array
    {
        $map = [];

        foreach ($header as $index => $raw) {
            $key = mb_strtolower(trim(str_replace("\xEF\xBB\xBF", '', (string) $raw)));
            $key = str_replace([' ', '-'], '_', $key);
            $key = self::HEADER_ALIASES[$key] ?? $key;

            if (in_array($key, self::COLUMNS, true)) {
                $map[$index] = $key;
            }
        }

        return $map;
    }

    /** تحويل خلايا السطر إلى مصفوفة بأسماء الأعمدة — null إذا كان السطر فارغاً */
    protected function rowToAssoc(array $cells, array $headerMap): ?array
    {
        $row = array_fill_keys(self::COLUMNS, '');
        $filled = '';

        foreach ($headerMap as $index => $key) {
            $row[$key] = trim((string) ($cells[$index] ?? ''));
            $filled .= $row[$key];
        }

        return $filled === '' ? null : $row;
    }

    protected function convertArabicDigits(string $value): string
    {
        return strtr($value, [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }
}
