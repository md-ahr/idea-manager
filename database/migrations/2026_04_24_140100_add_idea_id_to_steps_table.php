<?php

use App\Models\Idea;
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
        if (Schema::hasColumn('steps', 'idea_id')) {
            return;
        }

        Schema::table('steps', function (Blueprint $table) {
            $table->foreignIdFor(Idea::class)->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('steps', 'idea_id')) {
            return;
        }

        Schema::table('steps', function (Blueprint $table) {
            $table->dropForeign(['idea_id']);
            $table->dropColumn('idea_id');
        });
    }
};
