<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('jms_serializer.event_dispatcher', \JMS\Serializer\EventDispatcher\LazyEventDispatcher::class)
        ->private()
        ->args([service('jms_serializer.event_dispatcher.service_locator')]);

    $services->set('jms_serializer.event_dispatcher.service_locator', \Symfony\Component\DependencyInjection\ServiceLocator::class)
        ->private()
        ->args([
            [
                // this argument is not needed and uses a not existing service.
                // at some point symfony tries to replace/merge service locators by replacing them with their hashes
                // and since is common service locators, it is common to have collisions.
                // But somehow symfony does not detect that we change the arguments if it in a compiler pass
                'jms_serializer.event_dispatcher.service_locator' => service('jms_serializer.event_dispatcher.service_locator'),
            ],
        ])
        ->tag('container.service_locator');

    $services->set('jms_serializer.doctrine_proxy_subscriber', \JMS\Serializer\EventDispatcher\Subscriber\DoctrineProxySubscriber::class)
        ->private()
        ->args([
            true,
            false,
        ])
        ->tag('jms_serializer.event_subscriber');

    $services->set('jms_serializer.stopwatch_subscriber', \JMS\SerializerBundle\Serializer\StopwatchEventSubscriber::class)
        ->private()
        ->args([
            service('debug.stopwatch'),
            'jms_serializer',
        ])
        ->tag('jms_serializer.event_subscriber');

    $services->set('jms_serializer.enum_subscriber', \JMS\Serializer\EventDispatcher\Subscriber\EnumSubscriber::class)
        ->private()
        ->tag('jms_serializer.event_subscriber');

    $services->set('jms_serializer.handler_registry', \JMS\Serializer\Handler\LazyHandlerRegistry::class)
        ->args([service('jms_serializer.handler_registry.service_locator')]);

    $services->set('jms_serializer.handler_registry.service_locator', \Symfony\Component\DependencyInjection\ServiceLocator::class)
        ->private()
        ->args([
            [
                // this argument is not needed and uses a not existing service.
                // at some point symfony tries to replace/merge service locators by replacing them with their hashes
                // and since is common service locators, it is common to have collisions.
                // But somehow symfony does not detect that we change the arguments if it in a compiler pass
                'jms_serializer.handler_registry.service_locator' => service('jms_serializer.handler_registry.service_locator'),
            ],
        ])
        ->tag('container.service_locator');

    $services->set('jms_serializer.enum_handler', \JMS\Serializer\Handler\EnumHandler::class)
        ->private()
        ->tag('jms_serializer.subscribing_handler');

    $services->set('jms_serializer.union_handler', \JMS\Serializer\Handler\UnionHandler::class)
        ->private()
        ->tag('jms_serializer.subscribing_handler');

    $services->set('jms_serializer.array_collection_handler', \JMS\Serializer\Handler\ArrayCollectionHandler::class)
        ->private()
        ->args([false])
        ->tag('jms_serializer.subscribing_handler');

    $services->set('jms_serializer.constraint_violation_handler', \JMS\Serializer\Handler\ConstraintViolationHandler::class)
        ->private()
        ->tag('jms_serializer.subscribing_handler');

    $services->set('jms_serializer.datetime_handler', \JMS\Serializer\Handler\DateHandler::class)
        ->private()
        ->args([
            '', // default_format
            '', // default_timezone
            '', // cdata
            '', // default_deserialization_formats
        ])
        ->tag('jms_serializer.subscribing_handler');

    $services->set('jms_serializer.form_error_handler', \JMS\Serializer\Handler\FormErrorHandler::class)
        ->private()
        ->args([service('translator')->nullOnInvalid()])
        ->tag('jms_serializer.subscribing_handler');

    $services->set('jms_serializer.iterator_handler', \JMS\Serializer\Handler\IteratorHandler::class)
        ->private()
        ->tag('jms_serializer.subscribing_handler');

    $services->set('jms_serializer.symfony_uid_handler', \JMS\Serializer\Handler\SymfonyUidHandler::class)
        ->private()
        ->args([
            '', // default_format
            '', // cdata
        ])
        ->tag('jms_serializer.subscribing_handler');

    // Metadata Drivers
    $services->set('jms_serializer.type_parser', \JMS\Serializer\Type\Parser::class)
        ->private()
        ->tag('jms_serializer.instance_global');

    $services->set('jms_serializer.metadata.file_locator', \Metadata\Driver\FileLocator::class)
        ->private()
        ->args([
            [], // Namespace Prefixes mapping to Directories
        ]);

    $services->set('jms_serializer.metadata.yaml_driver', \JMS\Serializer\Metadata\Driver\YamlDriver::class)
        ->private()
        ->args([
            service('jms_serializer.metadata.file_locator'),
            service('jms_serializer.naming_strategy'),
            service('jms_serializer.type_parser')->nullOnInvalid(),
            null, // expression evaluator
        ]);

    $services->set('jms_serializer.metadata.xml_driver', \JMS\Serializer\Metadata\Driver\XmlDriver::class)
        ->private()
        ->args([
            service('jms_serializer.metadata.file_locator'),
            service('jms_serializer.naming_strategy'),
            service('jms_serializer.type_parser')->nullOnInvalid(),
            null, // expression evaluator
        ]);

    $services->set('jms_serializer.metadata.annotation_driver', \JMS\Serializer\Metadata\Driver\AnnotationDriver::class)
        ->private()
        ->args([
            service('annotation_reader'),
            service('jms_serializer.naming_strategy'),
            service('jms_serializer.type_parser')->nullOnInvalid(),
            null, // expression evaluator
        ]);

    $services->set('jms_serializer.metadata.annotation_or_attribute_driver', \JMS\Serializer\Metadata\Driver\AnnotationOrAttributeDriver::class)
        ->private()
        ->args([
            service('jms_serializer.naming_strategy'),
            service('jms_serializer.type_parser')->nullOnInvalid(),
            null,
            service('annotation_reader')->nullOnInvalid(),
        ]);

    $services->set('jms_serializer.metadata.null_driver', \JMS\Serializer\Metadata\Driver\NullDriver::class)
        ->private()
        ->args([service('jms_serializer.naming_strategy')]);

    $services->set('jms_serializer.metadata_driver', \Metadata\Driver\DriverChain::class)
        ->private()
        ->args([
            ['' => null], // list of metadata drivers
        ]);

    // extra metadata driver
    $services->set('jms_serializer.metadata.doctrine_type_driver', \JMS\Serializer\Metadata\Driver\DoctrineTypeDriver::class)
        ->private()
        ->args([
            '',
            service('doctrine'),
            service('jms_serializer.type_parser'),
        ]);

    $services->set('jms_serializer.metadata.doctrine_phpcr_type_driver', \JMS\Serializer\Metadata\Driver\DoctrinePHPCRTypeDriver::class)
        ->private()
        ->args([
            '',
            service('doctrine_phpcr'),
            service('jms_serializer.type_parser'),
        ]);

    $services->set('jms_serializer.metadata.default_value_property_driver', \JMS\Serializer\Metadata\Driver\DefaultValuePropertyDriver::class)
        ->private()
        ->args([
            service('jms_serializer.metadata.default_value_property_driver.inner'),
            service('jms_serializer.type_parser')->nullOnInvalid(),
        ]);

    $services->set('jms_serializer.metadata.typed_properties_driver', \JMS\Serializer\Metadata\Driver\TypedPropertiesDriver::class)
        ->private()
        ->args([
            service('jms_serializer.metadata.typed_properties_driver.inner'),
            service('jms_serializer.type_parser')->nullOnInvalid(),
        ]);

    $services->set('jms_serializer.metadata.enum_driver', \JMS\Serializer\Metadata\Driver\EnumPropertiesDriver::class)
        ->private()
        ->args([service('jms_serializer.metadata.enum_driver.inner')]);

    $services->set('jms_serializer.metadata.doc_block_driver', \JMS\Serializer\Metadata\Driver\DocBlockDriver::class)
        ->private()
        ->args([
            service('jms_serializer.metadata.doc_block_driver.inner'),
            service('jms_serializer.type_parser')->nullOnInvalid(),
        ]);

    $services->set('jms_serializer.metadata.service_locator', \Symfony\Component\DependencyInjection\ServiceLocator::class)
        ->private()
        ->args([['metadata_driver' => service('jms_serializer.metadata_driver')]])
        ->tag('container.service_locator');

    $services->set('jms_serializer.metadata.lazy_loading_driver', \Metadata\Driver\LazyLoadingDriver::class)
        ->private()
        ->args([
            service('jms_serializer.metadata.service_locator'),
            'metadata_driver',
        ]);

    // Metadata Factory
    $services->set('jms_serializer.metadata.cache.file_cache', \Metadata\Cache\FileCache::class)
        ->private()
        ->args([
            '', // Directory
        ]);

    $services->alias('jms_serializer.metadata.cache', 'jms_serializer.metadata.cache.file_cache')
        ->private();

    $services->set('jms_serializer.metadata_factory', \Metadata\MetadataFactory::class)
        ->private()
        ->args([
            service('jms_serializer.metadata.lazy_loading_driver'),
            \Metadata\ClassHierarchyMetadata::class,
            '',
        ])
        ->call('setCache', [service('jms_serializer.metadata.cache')->ignoreOnInvalid()]);

    // Exclusion Strategies
    $services->set('jms_serializer.version_exclusion_strategy', \JMS\Serializer\Exclusion\VersionExclusionStrategy::class)
        ->private()
        ->abstract();

    // Naming Strategies
    $services->set('jms_serializer.camel_case_naming_strategy', \JMS\Serializer\Naming\CamelCaseNamingStrategy::class)
        ->private()
        ->args([
            '', // separator
            '', // lowercase
        ]);

    $services->set('jms_serializer.identical_property_naming_strategy', \JMS\Serializer\Naming\IdenticalPropertyNamingStrategy::class)
        ->private();

    $services->set('jms_serializer.serialized_name_annotation_strategy', \JMS\Serializer\Naming\SerializedNameAnnotationStrategy::class)
        ->private()
        ->args([service('jms_serializer.camel_case_naming_strategy')]);

    $services->alias('jms_serializer.naming_strategy', 'jms_serializer.serialized_name_annotation_strategy')
        ->private();

    // Object Constructors
    $services->set('jms_serializer.doctrine_object_constructor', \JMS\Serializer\Construction\DoctrineObjectConstructor::class)
        ->private()
        ->args([
            service('doctrine')->ignoreOnInvalid(),
            service('jms_serializer.object_constructor'),
            'null',
        ]);

    $services->set('jms_serializer.doctrine_phpcr_object_constructor', \JMS\Serializer\Construction\DoctrineObjectConstructor::class)
        ->private()
        ->args([
            service('doctrine_phpcr')->ignoreOnInvalid(),
            service('jms_serializer.object_constructor'),
        ]);

    $services->set('jms_serializer.object_constructor', \JMS\Serializer\Construction\UnserializeObjectConstructor::class)
        ->private();

    $services->set('jms_serializer.serializer', \JMS\Serializer\Serializer::class)
        ->private()
        ->args([
            service('jms_serializer.metadata_factory'),
            ['2' => service('jms_serializer.deserialization_graph_navigator_factory'), '1' => service('jms_serializer.serialization_graph_navigator_factory')],
            [], // Serialization Visitors
            [], // Deserialization Visitors
            service('jms_serializer.serialization_context_factory'),
            service('jms_serializer.deserialization_context_factory'),
            service('jms_serializer.type_parser')->nullOnInvalid(),
        ]);

    $services->set('jms_serializer.deserialization_graph_navigator_factory', \JMS\Serializer\GraphNavigator\Factory\DeserializationGraphNavigatorFactory::class)
        ->private()
        ->args([
            service('jms_serializer.metadata_factory'),
            service('jms_serializer.handler_registry'),
            service('jms_serializer.object_constructor'),
            service('jms_serializer.accessor_strategy'),
            service('jms_serializer.event_dispatcher'),
            null, // expression evaluator
        ]);

    $services->set('jms_serializer.serialization_graph_navigator_factory', \JMS\Serializer\GraphNavigator\Factory\SerializationGraphNavigatorFactory::class)
        ->private()
        ->args([
            service('jms_serializer.metadata_factory'),
            service('jms_serializer.handler_registry'),
            service('jms_serializer.accessor_strategy'),
            service('jms_serializer.event_dispatcher'),
            null, // expression evaluator
        ]);

    $services->alias('jms_serializer.deserialization_context_factory', 'jms_serializer.configured_deserialization_context_factory')
        ->public();

    $services->alias('jms_serializer.serialization_context_factory', 'jms_serializer.configured_serialization_context_factory')
        ->public();

    // Preferred Alias
    $services->alias('jms_serializer', 'jms_serializer.serializer')
        ->public();

    $services->alias(\JMS\Serializer\SerializerInterface::class, 'jms_serializer.serializer')
        ->private();

    $services->alias(\JMS\Serializer\ArrayTransformerInterface::class, 'jms_serializer.serializer')
        ->private();

    // expression language components
    $services->set('jms_serializer.expression_evaluator', \JMS\Serializer\Expression\ExpressionEvaluator::class)
        ->args([
            service('jms_serializer.expression_language'),
            ['container' => service('service_container')],
        ])
        ->tag('jms_serializer.instance_global');

    $services->set('jms_serializer.expression_language', \Symfony\Component\ExpressionLanguage\ExpressionLanguage::class)
        ->private()
        ->tag('jms_serializer.instance_global');

    $services->set('jms_serializer.expression_language.function_provider', \JMS\SerializerBundle\ExpressionLanguage\BasicSerializerFunctionsProvider::class)
        ->private()
        ->tag('jms.expression.function_provider');

    // Twig Extension
    $services->set('jms_serializer.twig_extension.serializer', \JMS\Serializer\Twig\SerializerExtension::class)
        ->private()
        ->args([
            service('jms_serializer.serializer'),
            'jms_',
        ])
        ->tag('twig.extension');

    $services->set('jms_serializer.twig_extension.runtime_serializer', \JMS\Serializer\Twig\SerializerRuntimeExtension::class)
        ->private()
        ->args(['jms_'])
        ->tag('twig.extension');

    $services->set('jms_serializer.twig_extension.serializer_runtime_helper', \JMS\Serializer\Twig\SerializerRuntimeHelper::class)
        ->private()
        ->args([service('jms_serializer.serializer')])
        ->tag('twig.runtime');

    // PHP templating helper
    $services->set('jms_serializer.templating.helper.serializer', \JMS\SerializerBundle\Templating\SerializerHelper::class)
        ->args([service('jms_serializer.serializer')])
        ->tag('templating.helper', ['alias' => 'jms_serializer']);

    // accessor strategy
    $services->alias('jms_serializer.accessor_strategy', 'jms_serializer.accessor_strategy.default')
        ->private();

    $services->set('jms_serializer.accessor_strategy.default', \JMS\Serializer\Accessor\DefaultAccessorStrategy::class)
        ->private()
        ->args([null]);

    // Visitors
    $services->set('jms_serializer.json_serialization_visitor', \JMS\Serializer\Visitor\Factory\JsonSerializationVisitorFactory::class)
        ->private()
        ->tag('jms_serializer.serialization_visitor', ['format' => 'json']);

    $services->set('jms_serializer.json_deserialization_visitor', \JMS\Serializer\Visitor\Factory\JsonDeserializationVisitorFactory::class)
        ->private()
        ->args([false])
        ->tag('jms_serializer.deserialization_visitor', ['format' => 'json']);

    $services->set('jms_serializer.xml_serialization_visitor', \JMS\Serializer\Visitor\Factory\XmlSerializationVisitorFactory::class)
        ->private()
        ->tag('jms_serializer.serialization_visitor', ['format' => 'xml']);

    $services->set('jms_serializer.xml_deserialization_visitor', \JMS\Serializer\Visitor\Factory\XmlDeserializationVisitorFactory::class)
        ->private()
        ->tag('jms_serializer.deserialization_visitor', ['format' => 'xml']);

    $services->set('jms_serializer.cache.cache_clearer', \JMS\SerializerBundle\Cache\CacheClearer::class)
        ->private()
        ->args([service('jms_serializer.metadata.cache')])
        ->tag('kernel.cache_clearer');

    $services->set('jms_serializer.cache.cache_warmer', \JMS\SerializerBundle\Cache\CacheWarmer::class)
        ->private()
        ->args([
            [], // included dirs
            service('jms_serializer.metadata_factory'),
            [], // excluded dirs
        ])
        ->tag('kernel.cache_warmer');

    // context factories
    $services->set('jms_serializer.configured_serialization_context_factory', \JMS\SerializerBundle\ContextFactory\ConfiguredContextFactory::class)
        ->private();

    $services->set('jms_serializer.configured_deserialization_context_factory', \JMS\SerializerBundle\ContextFactory\ConfiguredContextFactory::class)
        ->private();

    $services->alias(\JMS\Serializer\ContextFactory\SerializationContextFactoryInterface::class, 'jms_serializer.configured_serialization_context_factory')
        ->private();

    $services->alias(\JMS\Serializer\ContextFactory\DeserializationContextFactoryInterface::class, 'jms_serializer.configured_deserialization_context_factory')
        ->private();
};
