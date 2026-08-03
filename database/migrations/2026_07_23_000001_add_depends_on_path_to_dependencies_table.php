<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dependencies', function (Blueprint $table) {
            $table->string('depends_on_path')->nullable()->after('depends_on');
            $table->index(['project_id', 'depends_on_path']);
            $table->index(['project_id', 'file_path']);
        });
    }

    public function down(): void
    {
        Schema::table('dependencies', function (Blueprint $table) {
            $table->dropIndex(['project_id', 'depends_on_path']);
            $table->dropIndex(['project_id', 'file_path']);
            $table->dropColumn('depends_on_path');
        });
    }
};
