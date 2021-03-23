<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Rule;
use App\Models\RuleRuleAction;

class RemoveColumnsRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $rules = Rule::all();

        foreach ($rules as $rule) {
            (new RuleRuleAction([
                'rule_id' => $rule->id,
                'rule_action_id' => $rule->rule_action_id,
                'action_data' => $rule->action_data
            ]))->save();
        }

        Schema::table('rules', function (Blueprint $table) {
            $table->dropColumn('rule_action_id');
            $table->dropColumn('action_data');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rules', function (Blueprint $table) {
            $table->integer('rule_action_id')->unsigned();
            $table->text('action_data')->nullable();
        });
    }
}
