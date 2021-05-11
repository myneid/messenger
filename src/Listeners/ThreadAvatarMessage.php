<?php

namespace RTippin\Messenger\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use RTippin\Messenger\Actions\Messages\StoreSystemMessage;
use RTippin\Messenger\Events\ThreadAvatarEvent;
use RTippin\Messenger\Services\SystemMessageService;
use Throwable;

class ThreadAvatarMessage implements ShouldQueue
{
    /**
     * The name of the queue the job should be sent to.
     *
     * @var string|null
     */
    public string $queue = 'messenger';

    /**
     * @var SystemMessageService
     */
    private SystemMessageService $service;

    /**
     * @var StoreSystemMessage
     */
    private StoreSystemMessage $storeSystemMessage;

    /**
     * Create the event listener.
     *
     * @param SystemMessageService $service
     * @param StoreSystemMessage $storeSystemMessage
     */
    public function __construct(SystemMessageService $service, StoreSystemMessage $storeSystemMessage)
    {
        $this->storeSystemMessage = $storeSystemMessage;
        $this->service = $service;
    }

    /**
     * Handle the event.
     *
     * @param ThreadAvatarEvent $event
     * @return void
     * @throws Throwable
     */
    public function handle(ThreadAvatarEvent $event): void
    {
        $this->storeSystemMessage->execute(...$this->systemMessage($event));
    }

    /**
     * @param ThreadAvatarEvent $event
     * @return array
     */
    private function systemMessage(ThreadAvatarEvent $event): array
    {
        return $this->service
            ->setStoreData($event->thread, $event->provider)
            ->makeGroupAvatarChanged();
    }
}
