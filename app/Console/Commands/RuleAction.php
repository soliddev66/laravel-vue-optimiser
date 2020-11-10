<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Rule;

class RuleAction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rule:action {ruleId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $ruleId = $this->argument('ruleId');

        $rule = Rule::find($ruleId);

        $timeRangeClass = 'App\\Utils\\TimeFrames\\' . $rule->timeRange->provider;

        $timeRange = (new $timeRangeClass)->get();

        var_dump($timeRange[0]->format('Y-m-d'));
        var_dump($timeRange[1]->format('Y-m-d'));

        foreach ($rule->campaigns as $campaign) {
            $redTracks = $campaign->redtrackReport()->whereBetween('date', [$timeRange[0]->format('Y-m-d'), $timeRange[1]->format('Y-m-d')])->get();

            if (count($redTracks)) {
                $this->checkCondition($campaign, $redTracks);
            }
        }

        return 0;
    }

    private function checkCondition($campaign, $redTracks)
    {

    }
}
