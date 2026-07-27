<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('source', 20)->default('purchase'); // purchase|code|admin|migration
            $table->string('status', 20)->default('active');   // active|expired|revoked
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'course_id']);
        });

        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('type', 10)->default('percent'); // percent|fixed
            $table->decimal('value', 10, 2);
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->string('cover_path')->nullable();
            $table->string('preview_pdf_path')->nullable(); // معاينة الكتاب
            $table->unsignedInteger('stock')->nullable();   // null = غير محدود
            $table->unsignedTinyInteger('academic_year')->nullable()->index();
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number', 30)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            // wallet | manual_vodafone | manual_instapay | paymob_card | paymob_wallet | fawry | center_code
            $table->string('payment_method', 30)->default('manual_vodafone');
            $table->string('status', 20)->default('PENDING')->index(); // PENDING|PAID|FAILED|REJECTED|REFUNDED
            $table->string('receipt_path')->nullable();   // سكرين شوت التحويل
            $table->string('sender_phone', 20)->nullable(); // الرقم المحول منه
            $table->text('admin_note')->nullable();
            $table->string('gateway_reference')->nullable(); // مرجع بوابة الدفع
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->morphs('purchasable'); // Course أو Book
            $table->string('title');       // لقطة من الاسم وقت الشراء
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);          // موجب شحن / سالب خصم
            $table->decimal('balance_before', 10, 2);
            $table->decimal('balance_after', 10, 2);
            $table->string('type', 30); // charge|purchase|refund|admin_adjust|center_code
            $table->string('note')->nullable();
            $table->nullableMorphs('reference'); // Order مثلا
            $table->timestamps();
        });

        Schema::create('center_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->decimal('value', 10, 2)->default(0);      // قيمة شحن
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete(); // أو فتح كورس مباشرة
            $table->string('batch', 40)->nullable()->index();
            $table->foreignId('used_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('center_codes');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('books');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('enrollments');
    }
};
