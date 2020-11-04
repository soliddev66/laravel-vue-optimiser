<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRuleConditionTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rule_condition_templates', function (Blueprint $table) {
            $table->id();
            $table->integer('rule_condition_group_template_id')->unsigned();
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
        Schema::dropIfExists('rule_condition_templates');
    }
}
