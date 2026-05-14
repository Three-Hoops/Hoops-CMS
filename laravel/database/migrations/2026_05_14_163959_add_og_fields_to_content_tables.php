<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['posts', 'pages'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('og_title')->nullable()->after('meta_keywords');
                $table->string('og_description', 500)->nullable()->after('og_title');
            });
        }
    }

    public function down(): void
    {
        foreach (['posts', 'pages'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn(['og_title', 'og_description']);
            });
        }
    }
};
