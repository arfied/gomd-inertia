<?php

namespace App\Application\Agent\Handlers;

use App\Application\Agent\Jobs\VerifyAgentCredentialsJob;
use App\Application\Agent\Jobs\CheckAgentLicenseJob;
use App\Application\Agent\Jobs\CreateAgentReferralLinksJob;
use App\Application\Agent\Jobs\SendAgentWelcomeKitJob;
use App\Application\Agent\Jobs\AssignAgentMentorJob;
use App\Application\Agent\Jobs\ScheduleAgentTrainingJob;
use App\Application\Agent\Jobs\ActivateAgentAccountJob;
use App\Domain\Agent\Events\AgentRegistered;
use App\Domain\Agent\Events\AgentCredentialsVerified;
use App\Domain\Agent\Events\AgentLicenseChecked;
use App\Domain\Agent\Events\AgentReferralLinksCreated;
use App\Domain\Agent\Events\AgentWelcomeKitSent;
use App\Domain\Agent\Events\AgentMentorAssigned;
use App\Domain\Agent\Events\AgentTrainingScheduled;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * AgentOnboardingSagaHandler - Orchestrates the Agent Onboarding Saga.
 *
 * Listens to domain events and dispatches next steps in the onboarding process.
 *
 * Saga Flow:
 * AgentRegistered → VerifyCredentials → CheckLicense →
 * CreateReferralLinks → SendWelcomeKit → AssignMentor →
 * ScheduleTraining → ActivateAccount
 */
class AgentOnboardingSagaHandler implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle AgentRegistered event - dispatch credential verification.
     */
    public function handleAgentRegistered(AgentRegistered $event): void
    {
        dispatch(new VerifyAgentCredentialsJob(
            $event->aggregateUuid,
            $event->payload
        ))->onQueue('agent-onboarding');
    }

    /**
     * Handle AgentCredentialsVerified event - dispatch license check.
     */
    public function handleAgentCredentialsVerified(AgentCredentialsVerified $event): void
    {
        dispatch(new CheckAgentLicenseJob(
            $event->aggregateUuid,
            $event->payload
        ))->onQueue('agent-onboarding');
    }

    /**
     * Handle AgentLicenseChecked event - dispatch referral link creation.
     */
    public function handleAgentLicenseChecked(AgentLicenseChecked $event): void
    {
        dispatch(new CreateAgentReferralLinksJob(
            $event->aggregateUuid,
            $event->payload
        ))->onQueue('agent-onboarding');
    }

    /**
     * Handle AgentReferralLinksCreated event - dispatch welcome kit sending.
     */
    public function handleAgentReferralLinksCreated(AgentReferralLinksCreated $event): void
    {
        dispatch(new SendAgentWelcomeKitJob(
            $event->aggregateUuid,
            $event->payload
        ))->onQueue('agent-onboarding');
    }

    /**
     * Handle AgentWelcomeKitSent event - dispatch mentor assignment.
     */
    public function handleAgentWelcomeKitSent(AgentWelcomeKitSent $event): void
    {
        dispatch(new AssignAgentMentorJob(
            $event->aggregateUuid,
            $event->payload
        ))->onQueue('agent-onboarding');
    }

    /**
     * Handle AgentMentorAssigned event - dispatch training scheduling.
     */
    public function handleAgentMentorAssigned(AgentMentorAssigned $event): void
    {
        dispatch(new ScheduleAgentTrainingJob(
            $event->aggregateUuid,
            $event->payload
        ))->onQueue('agent-onboarding');
    }

    /**
     * Handle AgentTrainingScheduled event - dispatch account activation.
     */
    public function handleAgentTrainingScheduled(AgentTrainingScheduled $event): void
    {
        dispatch(new ActivateAgentAccountJob(
            $event->aggregateUuid,
            $event->payload
        ))->onQueue('agent-onboarding');
    }
}

