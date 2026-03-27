<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->enum('status_new', ['draft', 'submitted', 'accepted', 'declined', 'corrections', 'deleted'])->default('draft');
        });

        DB::table('forms')->update(['status_new' => DB::raw('status')]);

        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('forms', function (Blueprint $table) {
            $table->renameColumn('status_new', 'status');
        });
    }

    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->enum('status_old', ['draft', 'submitted', 'accepted', 'declined', 'corrections'])->default('draft');
        });

        DB::table('forms')->where('status', '!=', 'deleted')->update(['status_old' => DB::raw('status')]);
        DB::table('forms')->where('status', 'deleted')->update(['status_old' => 'draft']);

        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('forms', function (Blueprint $table) {
            $table->renameColumn('status_old', 'status');
        });
    }
};
