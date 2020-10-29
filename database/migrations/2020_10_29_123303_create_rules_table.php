<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('rule_group_id')->unsigned();
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
        Schema::dropIfExists('rules');
    }
}
