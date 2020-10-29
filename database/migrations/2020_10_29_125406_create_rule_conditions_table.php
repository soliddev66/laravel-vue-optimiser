<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRuleConditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rule_conditions', function (Blueprint $table) {
            $table->id();
            $table->integer('rule_condition_group_id')->unsigned();
            $table->integer('rule_condition_type_id')->unsigned();
            $table->integer('operation')->unsigned();
            $table->integer('amount')->unsigned();
            $table->integer('unit')->unsigned();
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
        Schema::dropIfExists('rule_conditions');
    }
}
