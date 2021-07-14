<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\Debug;

use JMS\SerializerBundle\Debug\EventDispatcher\TraceableEventDispatcher;
use JMS\SerializerBundle\Debug\Handler\TraceableHandlerRegistry;
use JMS\SerializerBundle\Debug\Metadata\MetadataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector as BaseDataCollector;
use Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;

final class DataCollector extends BaseDataCollector implements LateDataCollectorInterface
{
    private $eventDispatcher;
    private $handler;
    private $metadataCollector;

    public function __construct(
        TraceableEventDispatcher $eventDispatcher,
        TraceableHandlerRegistry $handler,
        MetadataCollector $metadataCollector
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->handler = $handler;
        $this->metadataCollector = $metadataCollector;

        $this->reset();
    }

    public function collect(Request $request, Response $response, \Throwable $exception = null)
    {
    }

    public function reset(): void
    {
        $this->data['handlers'] = [];
        $this->data['metadata'] = [];
        $this->data['listeners'] = [];
    }

    public function getName(): string
    {
        return 'jms_serializer';
    }

    public function addTriggeredEvent(array $call): void
    {
        $this->data['listeners'][] = $call;
    }

    public function getTriggeredListeners(): array
    {
        return $this->data['listeners']['called'];
    }

    public function getNotTriggeredListeners(): array
    {
        return $this->data['listeners']['not_called'];
    }

    public function getTriggeredHandlers(): array
    {
        return $this->data['handlers']['called'];
    }

    public function getNotTriggeredHandlers(): array
    {
        return $this->data['handlers']['not_called'];
    }

    public function getLoadedMetadata(): array
    {
        return $this->data['metadata'];
    }

    public function lateCollect(): void
    {
        $this->data['listeners'] = [
            'called'     => $this->eventDispatcher->getTriggeredListeners(),
            'not_called' => $this->eventDispatcher->getNotTriggeredListeners(),
        ];

        $this->data['handlers'] = [
            'called'     => $this->handler->getTriggeredHandlers(),
            'not_called' => $this->handler->getNotTriggeredHandlers(),
        ];

        $this->data['metadata'] = $this->metadataCollector->getLoadedMetadata();
    }
}
