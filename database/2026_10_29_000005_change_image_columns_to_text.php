<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Widen avatar_path and book_cover_path to TEXT so PostgreSQL can store
     * persistent data URIs as well as standard file paths.
     */
    public function up(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'avatar_path')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('avatar_path')->nullable()->change();
            });
        }

        if (Schema::hasTable('quotes') && Schema::hasColumn('quotes', 'book_cover_path')) {
            Schema::table('quotes', function (Blueprint $table) {
                $table->text('book_cover_path')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'avatar_path')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('avatar_path')->nullable()->change();
            });
        }

        if (Schema::hasTable('quotes') && Schema::hasColumn('quotes', 'book_cover_path')) {
            Schema::table('quotes', function (Blueprint $table) {
                $table->string('book_cover_path')->nullable()->change();
            });
        }
    }
};