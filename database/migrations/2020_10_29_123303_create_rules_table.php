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
        Schema::create('rule_groups', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('rule_condition_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('provider');
            $table->tinyInteger('report_source')->unsigned();
            $table->integer('rule_condition_type_group_id')->unsigned();
            $table->timestamps();
        });

        Schema::create('rule_condition_type_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('rules', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned();
            $table->string('name');
            $table->integer('rule_action_id')->unsigned();
            $table->integer('rule_group_id')->unsigned();
            $table->integer('from')->unsigned()->nullable();
            $table->integer('exclude_day')->unsigned()->nullable();
            $table->tinyInteger('is_widget_included')->unsigned()->nullable();
            $table->text('widget')->unsigned()->nullable();
            $table->integer('run_type')->unsigned()->nullable(); // Alert, Execute, Execute & Alert
            $table->integer('interval_amount')->unsigned();
            $table->integer('interval_unit')->unsigned();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('rule_condition_groups', function (Blueprint $table) {
            $table->id();
            $table->integer('rule_id')->unsigned();
            $table->timestamps();
        });

        Schema::create('rule_conditions', function (Blueprint $table) {
            $table->id();
            $table->integer('rule_condition_group_id')->unsigned();
            $table->integer('rule_condition_type_id')->unsigned();
            $table->integer('operation')->unsigned();
            $table->decimal('amount', 10, 2);
            $table->integer('unit')->unsigned();
            $table->timestamps();
        });

        Schema::create('rule_campaigns', function (Blueprint $table) {
            $table->id();
            $table->integer('rule_id')->unsigned();
            $table->integer('campaign_id')->unsigned();
            $table->timestamps();
        });

        Schema::create('rule_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('rule_action_id')->unsigned();
            $table->integer('from')->unsigned();
            $table->integer('exclude_day')->unsigned();
            $table->integer('run_type')->unsigned(); // Alert, Execute, Execute & Alert
            $table->integer('interval_amount')->unsigned();
            $table->integer('interval_unit')->unsigned();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('rule_condition_templates', function (Blueprint $table) {
            $table->id();
            $table->integer('rule_condition_group_template_id')->unsigned();
            $table->integer('rule_condition_type_id')->unsigned();
            $table->integer('operation')->unsigned();
            $table->decimal('amount', 10, 2);
            $table->integer('unit')->unsigned();
            $table->timestamps();
        });

        Schema::create('rule_condition_type_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('rule_condition_group_templates', function (Blueprint $table) {
            $table->id();
            $table->integer('rule_template_id')->unsigned();
            $table->timestamps();
        });

        Schema::create('rule_data_from_options', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('provider');
            $table->integer('excluded_day_id');
            $table->timestamps();
        });

        Schema::create('rule_actions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('provider');
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
        Schema::dropIfExists('rule_groups');
        Schema::dropIfExists('rule_condition_types');
        Schema::dropIfExists('rule_condition_type_groups');
        Schema::dropIfExists('rules');
        Schema::dropIfExists('rule_condition_groups');
        Schema::dropIfExists('rule_conditions');
        Schema::dropIfExists('rule_campaigns');
        Schema::dropIfExists('rule_templates');
        Schema::dropIfExists('rule_condition_templates');
        Schema::dropIfExists('rule_condition_type_templates');
        Schema::dropIfExists('rule_condition_group_templates');
        Schema::dropIfExists('rule_data_from_options');
        Schema::dropIfExists('rule_actions');
    }
}
