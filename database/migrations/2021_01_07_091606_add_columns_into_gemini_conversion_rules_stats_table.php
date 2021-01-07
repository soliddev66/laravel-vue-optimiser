<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsIntoGeminiConversionRulesStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gemini_conversion_rules_stats', function (Blueprint $table) {
            $table->double('post_click_conversion_value')->after('post_click_conversions')->nullable();
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
            $table->dropColumn('post_click_conversion_value');
        });
    }
}
