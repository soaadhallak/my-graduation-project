<?php

use App\Models\Project;
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
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Project::class)->constrained()->cascadeOnDelete();
            $table->string('token');
            $table->string('email');
            $table->enum('status', ['pending', 'accepted', 'revoked','expired'])->default('pending');
            $table->enum('role', ['project_manager', 'developer', 'tester'])->default('developer');
            $table->timestamp('expires_at')->nullable();
            $table->unique(['project_id','email']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
