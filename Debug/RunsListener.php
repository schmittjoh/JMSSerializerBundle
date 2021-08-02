<?php declare(strict_types=1);

namespace JMS\SerializerBundle\Debug;

use JMS\Serializer\EventDispatcher\Event;

/**
 * @internal
 */
final class RunsListener
{
    private $runs = [];

    public function saveRunInfo(Event $event)
    {
        $context = $event->getContext();
        $this->runs[$context->getDirection()][spl_object_hash($context)] = true;
    }

    public function getRuns(): array
    {
        return $this->runs;
    }
}
