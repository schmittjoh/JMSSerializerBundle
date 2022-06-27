<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\Tests\DependencyInjection;

use JMS\SerializerBundle\DependencyInjection\Compiler\CustomHandlersPass;
use JMS\SerializerBundle\DependencyInjection\JMSSerializerExtension;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class CustomHandlerPassTest extends TestCase
{
    /**
     * @param array $configs
     *
     * @return ContainerBuilder
     */
    private function getContainer(array $configs = [])
    {
        $loader = new JMSSerializerExtension();
        $container = new ContainerBuilder();

        $container->setParameter('kernel.debug', true);
        $container->setParameter('kernel.cache_dir', sys_get_temp_dir() . '/serializer');
        $container->setParameter('kernel.bundles', []);
        $container->setParameter('kernel.bundles_metadata', []);

        $loader->load(['jms_serializer' => $configs], $container);

        // remove other subscribers
        foreach (array_keys($container->findTaggedServiceIds('jms_serializer.handler')) as $id) {
            $container->removeDefinition($id);
        }

        foreach (array_keys($container->findTaggedServiceIds('jms_serializer.subscribing_handler')) as $id) {
            $container->removeDefinition($id);
        }

        return $container;
    }

    public function testHandlerx()
    {
        $container = $this->getContainer();

        $def = new Definition('Foo');
        $def->addTag('jms_serializer.handler', [
            'type' => 'DateTime',
            'format' => 'json',
        ]);
        $container->setDefinition('my_service', $def);

        $pass = new CustomHandlersPass();
        $pass->process($container);

        $handlers = $this->getRegisteredHandlers($container);

        $this->assertSame([
            2 => ['DateTime' => ['json' => ['my_service', 'deserializeDateTimeFromjson']]],
            1 => ['DateTime' => ['json' => ['my_service', 'serializeDateTimeTojson']]],
        ], $handlers);
    }

    public function testHandlerCanBePrivate()
    {
        $container = $this->getContainer();

        $def = new Definition('Foo');
        $def->setPublic(false);
        $def->addTag('jms_serializer.handler', [
            'type' => 'DateTime',
            'format' => 'json',
        ]);
        $container->setDefinition('my_service', $def);

        $pass = new CustomHandlersPass();
        $pass->process($container);

        $this->assertEquals([
            2 => ['DateTime' => ['json' => [new Reference('my_service'), 'deserializeDateTimeFromjson']]],
            1 => ['DateTime' => ['json' => [new Reference('my_service'), 'serializeDateTimeTojson']]],
        ], $this->getRegisteredHandlers($container));
    }

    public function testHandlerDirection()
    {
        $container = $this->getContainer();

        $def = new Definition('Foo');
        $def->addTag('jms_serializer.handler', [
            'type' => 'DateTime',
            'format' => 'json',
            'direction' => 'SERIALIZATION',
        ]);
        $container->setDefinition('my_service', $def);

        $pass = new CustomHandlersPass();
        $pass->process($container);

        $this->assertSame([
            1 => ['DateTime' => ['json' => ['my_service', 'serializeDateTimeTojson']]],
        ], $this->getRegisteredHandlers($container));
    }

    public function testHandlerIncorrectDirection()
    {
        $this->expectExceptionMessage('The direction "bar" of tag "jms_serializer.handler" of service "my_service" does not exist');
        $this->expectException(RuntimeException::class);

        $container = $this->getContainer();

        $def = new Definition('Foo');
        $def->addTag('jms_serializer.handler', [
            'type' => 'DateTime',
            'format' => 'json',
            'direction' => 'bar',
        ]);
        $container->setDefinition('my_service', $def);

        $pass = new CustomHandlersPass();
        $pass->process($container);
    }

    public function testHandlerMustHaveTypeAndFormat()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Each tag named "jms_serializer.handler" of service "my_service" must have at least two attributes: "type" and "format"');

        $container = $this->getContainer();

        $def = new Definition('Foo');
        $def->addTag('jms_serializer.handler');
        $container->setDefinition('my_service', $def);

        $pass = new CustomHandlersPass();
        $pass->process($container);
    }

    public function testHandlerMustPrioritizeUserDefined()
    {
        $container = $this->getContainer();

        $def = new Definition('JMS\Serializer\Foo');
        $def->addTag('jms_serializer.handler', [
            'type' => 'DateTime',
            'format' => 'json',
        ]);
        $container->setDefinition('my_service', $def);

        $userDef = new Definition('Bar');
        $userDef->addTag('jms_serializer.handler', [
            'type' => 'DateTime',
            'format' => 'json',
        ]);
        $container->setDefinition('my_custom_service', $userDef);

        $pass = new CustomHandlersPass();
        $pass->process($container);

        $this->assertSame([
            2 => ['DateTime' => ['json' => ['my_custom_service', 'deserializeDateTimeFromjson']]],
            1 => ['DateTime' => ['json' => ['my_custom_service', 'serializeDateTimeTojson']]],
        ], $this->getRegisteredHandlers($container));
    }

    public function testHandlerMustRespectPriorities()
    {
        $container = $this->getContainer();

        $def = new Definition('JMS\Serializer\Foo');
        $def->addTag('jms_serializer.handler', [
            'type' => 'DateTime',
            'format' => 'json',
        ]);
        $container->setDefinition('my_service', $def);

        $userDef = new Definition('Bar');
        $userDef->addTag('jms_serializer.handler', [
            'type' => 'DateTime',
            'format' => 'json',
        ]);
        $container->setDefinition('my_custom_service', $userDef);

        $userExplicitDef = new Definition('Baz');
        $userExplicitDef->addTag('jms_serializer.handler', [
            'type' => 'DateTime',
            'format' => 'json',
            'priority' => -100,
        ]);
        $container->setDefinition('my_custom_explicit_service', $userExplicitDef);

        $pass = new CustomHandlersPass();
        $pass->process($container);

        $this->assertSame([
            2 => ['DateTime' => ['json' => ['my_custom_explicit_service', 'deserializeDateTimeFromjson']]],
            1 => ['DateTime' => ['json' => ['my_custom_explicit_service', 'serializeDateTimeTojson']]],
        ], $this->getRegisteredHandlers($container));
    }

    public function testHandlerCanBeRegisteredForMultipleTypesOrDirections()
    {
        $container = $this->getContainer();

        $def = new Definition('JMS\Serializer\Foo');
        $def->addTag('jms_serializer.handler', [
            'type' => 'Custom',
            'direction' => 'serialization',
            'format' => 'json',
            'method' => 'serialize',
        ]);
        $def->addTag('jms_serializer.handler', [
            'type' => 'Custom',
            'direction' => 'deserialization',
            'format' => 'json',
            'method' => 'deserialize',
        ]);
        $def->addTag('jms_serializer.handler', [
            'type' => 'Custom<?>',
            'direction' => 'serialization',
            'format' => 'json',
            'method' => 'serialize',
        ]);
        $def->addTag('jms_serializer.handler', [
            'type' => 'Custom<?>',
            'direction' => 'deserialization',
            'format' => 'json',
            'method' => 'deserialize',
        ]);
        $container->setDefinition('my_service', $def);

        $pass = new CustomHandlersPass();
        $pass->process($container);

        $this->assertSame([
            1 => [
                'Custom' => ['json' => ['my_service', 'serialize']],
                'Custom<?>' => ['json' => ['my_service', 'serialize']],
            ],
            2 => [
                'Custom' => ['json' => ['my_service', 'deserialize']],
                'Custom<?>' => ['json' => ['my_service', 'deserialize']],
            ],
        ], $this->getRegisteredHandlers($container));
    }

    public function testSubscribingHandler()
    {
        $container = $this->getContainer();

        $def = new Definition('JMS\SerializerBundle\Tests\DependencyInjection\Fixture\SubscribingHandler');
        $def->addTag('jms_serializer.subscribing_handler');
        $container->setDefinition('my_service', $def);

        $pass = new CustomHandlersPass();
        $pass->process($container);

        $this->assertSame([
            1 => ['DateTime' => ['json' => ['my_service', 'onDateTime']]],
        ], $this->getRegisteredHandlers($container));
    }

    public function testSubscribingHandlerCanBePrivate()
    {
        $container = $this->getContainer();

        $def = new Definition('JMS\SerializerBundle\Tests\DependencyInjection\Fixture\SubscribingHandler');
        $def->addTag('jms_serializer.subscribing_handler');
        $def->setPublic(false);
        $container->setDefinition('my_service', $def);

        $pass = new CustomHandlersPass();
        $pass->process($container);

        $this->assertEquals([
            1 => ['DateTime' => ['json' => [new Reference('my_service'), 'onDateTime']]],
        ], $this->getRegisteredHandlers($container));
    }

    public function testSubscribingHandlerInterface()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The service "my_service" must implement the SubscribingHandlerInterface');

        $container = $this->getContainer();

        $def = new Definition('JMS\SerializerBundle\Tests\DependencyInjection\Fixture\SimpleObject');
        $def->addTag('jms_serializer.subscribing_handler');
        $container->setDefinition('my_service', $def);

        $pass = new CustomHandlersPass();
        $pass->process($container);
    }

    public function testSubscribingHandlerMustPrioritizeUserDefined()
    {
        $container = $this->getContainer();

        $def = new Definition('JMS\SerializerBundle\Tests\DependencyInjection\Fixture\SubscribingHandler');
        $def->addTag('jms_serializer.subscribing_handler');
        $container->setDefinition('my_service', $def);

        $userDef = new Definition('JMS\SerializerBundle\Tests\DependencyInjection\Fixture\UserDefined\UserDefinedSubscribingHandler');
        $userDef->addTag('jms_serializer.subscribing_handler');
        $container->setDefinition('my_custom_service', $userDef);

        $pass = new CustomHandlersPass();
        $pass->process($container);

        $this->assertSame([
            1 => ['DateTime' => ['json' => ['my_custom_service', 'onDateTime']]],
        ], $this->getRegisteredHandlers($container));
    }

    public function testSubscribingHandlerMustRespectPriorities()
    {
        $container = $this->getContainer();

        $def = new Definition('JMS\SerializerBundle\Tests\DependencyInjection\Fixture\SubscribingHandler');
        $def->addTag('jms_serializer.subscribing_handler');
        $container->setDefinition('my_service', $def);

        $userDef = new Definition('JMS\SerializerBundle\Tests\DependencyInjection\Fixture\UserDefined\UserDefinedSubscribingHandler');
        $userDef->addTag('jms_serializer.subscribing_handler');
        $container->setDefinition('my_custom_service', $userDef);

        $userExplicitDef = new Definition('JMS\SerializerBundle\Tests\DependencyInjection\Fixture\UserDefined\UserDefinedSubscribingHandler');
        $userExplicitDef->addTag('jms_serializer.subscribing_handler', ['priority' => -100]);
        $container->setDefinition('my_custom_explicit_service', $userExplicitDef);

        $pass = new CustomHandlersPass();
        $pass->process($container);

        $this->assertSame([
            1 => ['DateTime' => ['json' => ['my_custom_explicit_service', 'onDateTime']]],
        ], $this->getRegisteredHandlers($container));
    }

    private function buildHandlersFromCalls(array $calls): array
    {
        $handlers = [];
        foreach ($calls as $callData) {
            if ('registerHandler' !== $callData[0]) {
                continue;
            }

            [$direction, $type, $format, $handler] = $callData[1];
            $handlers[$direction][$type][$format] = $handler;
        }

        return $handlers;
    }

    private function getRegisteredHandlers(ContainerBuilder $containerBuilder): array
    {
        $calls = $containerBuilder->findDefinition('jms_serializer.handler_registry')->getMethodCalls();

        return $this->buildHandlersFromCalls($calls);
    }
}
