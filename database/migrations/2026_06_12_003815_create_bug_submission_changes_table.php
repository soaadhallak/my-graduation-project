<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\BugSubmission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bug_submission_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(BugSubmission::class)->constrained()->onDelete('cascade');
            $table->string('file'); 
            $table->longText('diff');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bug_submission_changes');
    }
};
