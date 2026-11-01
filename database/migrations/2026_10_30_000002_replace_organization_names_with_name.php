<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('organizations')->orderBy('id')->each(function ($organization) {
            if (mb_strlen(trim($organization->firstname.' '.$organization->lastname)) > 255) {
                throw new RuntimeException('Organization '.$organization->id.' has a combined name longer than 255 characters. Shorten it before migrating.');
            }
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
        });

        DB::table('organizations')->orderBy('id')->each(function ($organization) {
            DB::table('organizations')->where('id', $organization->id)->update([
                'name' => trim($organization->firstname.' '.$organization->lastname),
            ]);
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
            $table->dropColumn(['firstname', 'lastname']);
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('firstname')->default('');
            $table->string('lastname')->default('');
        });

        // The original split cannot be recovered; preserve the full name.
        DB::table('organizations')->update(['firstname' => DB::raw('name')]);

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
