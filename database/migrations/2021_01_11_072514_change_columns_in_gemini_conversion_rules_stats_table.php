<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnsInGeminiConversionRulesStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gemini_conversion_rules_stats', function (Blueprint $table) {
            $table->string('keyword_value')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gemini_conversion_rules_stats', function (Blueprint $table) {
            $table->string('keyword_value')->change();
        });
    }
}
