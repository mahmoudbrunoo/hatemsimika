<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// عداد الامتحان سيرفري بحت: المحاولة اللي عدّى وقتها والطالب برة المنصة
// بتتسلم وتتصحح تلقائياً بآخر مسودة محفوظة حتى لو الطالب مرجعش تاني
Schedule::command('exams:submit-expired')->everyMinute();
