<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bug_submissions', function (Blueprint $table) {
            $table->string('pull_request_number')->unique();
            $table->string('review_branch')->nullable();
            $table->string('status')->default('pending'); 
            $table->text('rejection_reason')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bug_submissions', function (Blueprint $table) {
            $table->dropColumn('pull_request_number');
            $table->dropColumn('review_branch');
            $table->dropColumn('status');
            $table->dropColumn('rejection_reason');
        });
    }
};
