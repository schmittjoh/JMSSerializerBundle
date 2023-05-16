<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\Debug;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector as BaseDataCollector;
use Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;

/**
 * @internal
 */
final class DataCollector extends BaseDataCollector implements LateDataCollectorInterface
{
    private $eventDispatcher;
    private $instance;
    private $handler;
    private $metadataFactory;
    private $locator;
    private $loadedDirs;
    private $runsListener;

    public function __construct(
        string $instance,
        array $loadedDirs,
        TraceableEventDispatcher $eventDispatcher,
        TraceableHandlerRegistry $handler,
        TraceableMetadataFactory $metadataFactory,
        TraceableFileLocator $locator,
        RunsListener $runsListener
    ) {
        $this->instance = $instance;
        $this->eventDispatcher = $eventDispatcher;
        $this->handler = $handler;
        $this->metadataFactory = $metadataFactory;
        $this->locator = $locator;
        $this->loadedDirs = $loadedDirs;
        $this->runsListener = $runsListener;

        $this->reset();
    }

    public function collect(Request $request, Response $response, \Throwable $exception = null): void
    {
    }

    public function reset(): void
    {
        $this->data['handlers'] = [];
        $this->data['metadata'] = [];
        $this->data['listeners'] = [];
        $this->data['metadata_files'] = [];
        $this->data['loaded_dirs'] = [];
        $this->data['runs'] = [];
        $this->data['instance'] = $this->instance;
    }

    public function getInstance(): string
    {
        return $this->data['instance'];
    }

    public function getName(): string
    {
        if (($this->instance ?? $this->data['instance']) === 'default'){
            return 'jms_serializer';
        }

        return 'jms_serializer_'. ($this->instance ?? $this->data['instance']);
    }

    public function getNumListeners($type): int
    {
        return array_sum(array_map(function ($l){
            return count($l);
        }, $this->data['listeners'][$type]));
    }

    public function getNumHandlers($type): int
    {
        return array_sum(array_map(function ($l){
            return count($l);
        }, $this->data['handlers'][$type]));
    }

    public function getTriggeredListeners(): array
    {
        return $this->data['listeners']['called'];
    }

    public function getRuns($direction): array
    {
        return $this->data['runs'][$direction] ?? [];
    }

    public function getLoadedDirs(): array
    {
        return $this->data['loaded_dirs'];
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

    public function getMetadataFiles(): array
    {
        return $this->data['metadata_files'];
    }

    public function getTriggeredEvents()
    {
        return $this->data['triggered_events'];
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

        $this->data['metadata'] = $this->metadataFactory->getLoadedMetadata();
        $this->data['metadata_files'] = $this->locator->getAttemptedFiles();
        $this->data['loaded_dirs'] = $this->loadedDirs;
        $this->data['runs'] = $this->runsListener->getRuns();
        $this->data['triggered_events'] = $this->eventDispatcher->getTriggeredEvents();
        ksort($this->data['loaded_dirs']);
        ksort($this->data['metadata_files']);
        ksort($this->data['metadata']);
    }
}
