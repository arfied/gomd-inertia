<?php

namespace App\Console\Commands;

use App\Application\Agent\Handlers\CreateAgentReferralLinksHandler;
use App\Models\Agent;
use Illuminate\Console\Command;

class CreateAgentReferralLinksCommand extends Command
{
    protected $signature = 'agent:create-referral-links {--agent-id= : Create referral links for a specific agent}';

    protected $description = 'Create referral links for agents';

    public function handle(): int
    {
        $agentId = $this->option('agent-id');

        if ($agentId) {
            $agent = Agent::find($agentId);
            if (!$agent) {
                $this->error("Agent with ID {$agentId} not found");
                return 1;
            }

            (new CreateAgentReferralLinksHandler())->handle($agentId);
            $this->info("Referral links created for agent {$agent->user->name}");
            return 0;
        }

        // Create for all agents without referral links
        $agents = Agent::whereDoesntHave('referralLinks')->get();

        if ($agents->isEmpty()) {
            $this->info('All agents already have referral links');
            return 0;
        }

        $this->info("Creating referral links for {$agents->count()} agents...");

        foreach ($agents as $agent) {
            (new CreateAgentReferralLinksHandler())->handle($agent->id);
            $this->line("✓ Created referral links for {$agent->user->name}");
        }

        $this->info('Done!');
        return 0;
    }
}

