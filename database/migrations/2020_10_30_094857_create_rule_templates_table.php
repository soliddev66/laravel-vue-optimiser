<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRuleTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rule_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('from')->unsigned();
            $table->integer('exclude_day')->unsigned();
            $table->integer('run_type')->unsigned(); // Alert, Execute, Execute & Alert
            $table->integer('interval_amount')->unsigned();
            $table->integer('interval_unit')->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rule_templates');
    }
}
