<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('jms_serializer.data_collector', \JMS\SerializerBundle\Debug\DataCollector::class)
        ->args([
            'default',
            [],
            service('jms_serializer.event_dispatcher'),
            service('jms_serializer.traceable_handler_registry'),
            service('jms_serializer.traceable_metadata_factory'),
            service('jms_serializer.metadata.file_locator'),
            service('jms_serializer.traceable_runs_listener'),
        ])
        ->tag('jms_serializer.profiler')
        ->tag('data_collector', ['id' => 'jms_serializer', 'template' => '@JMSSerializer/Collector/panel.html.twig']);

    $services->set('jms_serializer.event_dispatcher', \JMS\SerializerBundle\Debug\TraceableEventDispatcher::class)
        ->private()
        ->args([service('jms_serializer.event_dispatcher.service_locator')]);

    $services->set('jms_serializer.traceable_runs_listener', \JMS\SerializerBundle\Debug\RunsListener::class)
        ->private()
        ->tag('jms_serializer.event_listener', ['event' => 'serializer.pre_serialize', 'method' => 'saveRunInfo'])
        ->tag('jms_serializer.event_listener', ['event' => 'serializer.pre_deserialize', 'method' => 'saveRunInfo'])
        ->tag('jms_serializer.profiler');

    $services->set('jms_serializer.traceable_metadata_factory', \JMS\SerializerBundle\Debug\TraceableMetadataFactory::class)
        ->private()
        ->decorate('jms_serializer.metadata_factory', null, -128)
        ->args([service('jms_serializer.traceable_metadata_factory.inner')])
        ->tag('jms_serializer.profiler');

    $services->set('jms_serializer.traceable_handler_registry', \JMS\SerializerBundle\Debug\TraceableHandlerRegistry::class)
        ->private()
        ->decorate('jms_serializer.handler_registry', null, -128)
        ->args([service('jms_serializer.traceable_handler_registry.inner')])
        ->tag('jms_serializer.profiler');

    $services->set('jms_serializer.metadata.traceable_file_locator', \JMS\SerializerBundle\Debug\TraceableFileLocator::class)
        ->private()
        ->decorate('jms_serializer.metadata.file_locator', null, -128)
        ->args([service('jms_serializer.metadata.traceable_file_locator.inner')]);
};
