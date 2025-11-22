<?php

use App\Application\Clinical\Commands\CreateQuestionnaire;
use App\Application\Clinical\Handlers\CreateQuestionnaireHandler;
use App\Domain\Events\DomainEvent;
use App\Domain\Clinical\Events\QuestionnaireCreated;
use App\Models\StoredEvent;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;

it('persists a QuestionnaireCreated event via the handler', function () {
    $fakeStore = new class implements EventStoreContract {
        /** @var array<int, DomainEvent> */
        public array $stored = [];

        public function store(DomainEvent $event): StoredEvent
        {
            $this->stored[] = $event;

            return new class extends StoredEvent {
                public function __construct() {}
            };
        }
    };

    $fakeDispatcher = new class implements Dispatcher {
        /** @var array<int, DomainEvent> */
        public array $dispatched = [];

        public function dispatch($event, $payload = [])
        {
            $this->dispatched[] = $event;
        }

        public function listen($events, $listener = null) {}
        public function hasListeners($eventName) { return false; }
        public function subscribe($subscriber) {}
        public function until($event, $payload = []) {}
        public function flush($event = null) {}
    };

    $handler = new CreateQuestionnaireHandler($fakeStore, $fakeDispatcher);

    $command = new CreateQuestionnaire(
        questionnaireUuid: 'questionnaire-uuid-123',
        title: 'Health Assessment',
        description: 'Initial assessment',
        questions: [['id' => 1, 'text' => 'How are you?']],
        createdBy: 1,
        patientId: 'patient-123',
        metadata: ['source' => 'api'],
    );

    $handler->handle($command);

    expect($fakeStore->stored)->toHaveCount(1);
    expect($fakeStore->stored[0])->toBeInstanceOf(QuestionnaireCreated::class);
    expect($fakeDispatcher->dispatched)->toHaveCount(1);
});

