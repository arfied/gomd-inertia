<?php

namespace App\Domain\Signup;

use App\Domain\Events\DomainEvent;
use App\Domain\Shared\AggregateRoot;
use App\Domain\Signup\Events\ConditionSelected;
use App\Domain\Signup\Events\MedicationSelected;
use App\Domain\Signup\Events\PaymentProcessed;
use App\Domain\Signup\Events\PlanSelected;
use App\Domain\Signup\Events\QuestionnaireCompleted;
use App\Domain\Signup\Events\SignupFailed;
use App\Domain\Signup\Events\SignupStarted;
use App\Domain\Signup\Events\SubscriptionCreated;

class SignupAggregate extends AggregateRoot
{
    public string $signupId;
    public string $userId;
    public string $signupPath;
    public ?string $medicationId = null;
    public ?string $conditionId = null;
    public ?string $planId = null;
    public array $questionnaireResponses = [];
    public string $status = 'pending'; // pending, completed, failed
    public ?string $paymentId = null;
    public ?string $subscriptionId = null;

    public static function startSignup(string $signupId, string $userId, string $signupPath): self
    {
        $aggregate = new self();
        $aggregate->signupId = $signupId;
        $aggregate->userId = $userId;
        $aggregate->signupPath = $signupPath;

        $aggregate->recordThat(new SignupStarted($signupId, $userId, $signupPath));

        return $aggregate;
    }

    public function selectMedication(string $medicationId): void
    {
        $this->recordThat(new MedicationSelected($this->signupId, $medicationId));
    }

    public function selectCondition(string $conditionId): void
    {
        $this->recordThat(new ConditionSelected($this->signupId, $conditionId));
    }

    public function selectPlan(string $planId): void
    {
        $this->recordThat(new PlanSelected($this->signupId, $planId));
    }

    public function completeQuestionnaire(array $responses): void
    {
        $this->recordThat(new QuestionnaireCompleted($this->signupId, $responses));
    }

    public function processPayment(string $paymentId, float $amount, string $status): void
    {
        $this->recordThat(new PaymentProcessed($this->signupId, $paymentId, $amount, $status));
    }

    public function createSubscription(string $subscriptionId): void
    {
        $this->recordThat(new SubscriptionCreated(
            $this->signupId,
            $subscriptionId,
            $this->userId,
            $this->planId ?? '',
            $this->medicationId,
            $this->conditionId,
        ));
    }

    public function fail(string $reason, string $message): void
    {
        $this->recordThat(new SignupFailed($this->signupId, $reason, $message));
    }

    protected function apply(DomainEvent $event): void
    {
        match ($event::class) {
            SignupStarted::class => $this->applySignupStarted($event),
            MedicationSelected::class => $this->applyMedicationSelected($event),
            ConditionSelected::class => $this->applyConditionSelected($event),
            PlanSelected::class => $this->applyPlanSelected($event),
            QuestionnaireCompleted::class => $this->applyQuestionnaireCompleted($event),
            PaymentProcessed::class => $this->applyPaymentProcessed($event),
            SubscriptionCreated::class => $this->applySubscriptionCreated($event),
            SignupFailed::class => $this->applySignupFailed($event),
            default => null,
        };
    }

    private function applySignupStarted(SignupStarted $event): void
    {
        $this->signupId = $event->signupId;
        $this->userId = $event->userId;
        $this->signupPath = $event->signupPath;
        $this->status = 'pending';
    }

    private function applyMedicationSelected(MedicationSelected $event): void
    {
        $this->medicationId = $event->medicationId;
    }

    private function applyConditionSelected(ConditionSelected $event): void
    {
        $this->conditionId = $event->conditionId;
    }

    private function applyPlanSelected(PlanSelected $event): void
    {
        $this->planId = $event->planId;
    }

    private function applyQuestionnaireCompleted(QuestionnaireCompleted $event): void
    {
        $this->questionnaireResponses = $event->responses;
    }

    private function applyPaymentProcessed(PaymentProcessed $event): void
    {
        $this->paymentId = $event->paymentId;
    }

    private function applySubscriptionCreated(SubscriptionCreated $event): void
    {
        $this->subscriptionId = $event->subscriptionId;
        $this->status = 'completed';
    }

    private function applySignupFailed(SignupFailed $event): void
    {
        $this->status = 'failed';
    }
}

