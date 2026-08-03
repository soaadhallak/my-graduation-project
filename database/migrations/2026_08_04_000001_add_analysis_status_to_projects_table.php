<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('analysis_status')->default('idle')->after('description');
            $table->text('analysis_error')->nullable()->after('analysis_status');
            $table->timestamp('analysis_started_at')->nullable()->after('analysis_error');
            $table->timestamp('analysis_finished_at')->nullable()->after('analysis_started_at');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'analysis_status',
                'analysis_error',
                'analysis_started_at',
                'analysis_finished_at',
            ]);
        });
    }
};
