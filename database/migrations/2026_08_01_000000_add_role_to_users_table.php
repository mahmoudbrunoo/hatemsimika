<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['student', 'teacher', 'admin'])
                ->default('student')
                ->after('status');
        });

        // من كان مسؤولاً في نظام spatie القديم (super_admin / assistant) يبقى مسؤولاً
        DB::table('users')
            ->whereIn('id', function ($query) {
                $query->select('model_has_roles.model_id')
                    ->from('model_has_roles')
                    ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                    ->where('model_has_roles.model_type', 'App\\Models\\User')
                    ->whereIn('roles.name', ['super_admin', 'assistant']);
            })
            ->update(['role' => 'admin']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
