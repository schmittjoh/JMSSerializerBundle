<?php

namespace JMS\SerializerBundle\Tests\DependencyInjection;

use JMS\Serializer\Exception\RuntimeException;
use JMS\SerializerBundle\DependencyInjection\Compiler\CustomHandlersPass;
use JMS\SerializerBundle\DependencyInjection\JMSSerializerExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class CustomHandlerPassTest extends TestCase
{
    /**
     * @param array $configs
     * @return ContainerBuilder
     */
    private function getContainer(array $configs = array())
    {
        $loader = new JMSSerializerExtension();
        $container = new ContainerBuilder();

        $container->setParameter('kernel.debug', true);
        $container->setParameter('kernel.cache_dir', sys_get_temp_dir() . '/serializer');
        $container->setParameter('kernel.bundles', array());
        $container->setParameter('kernel.bundles', array());

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

    public function testHandler()
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

        $args = $container->getDefinition('jms_serializer.handler_registry')->getArguments();

        $this->assertSame([
            2 => ['DateTime' => ['json' => ['my_service', 'deserializeDateTimeFromjson']]],
            1 => ['DateTime' => ['json' => ['my_service', 'serializeDateTimeTojson']]]
        ], $args[1]);
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

        $args = $container->getDefinition('jms_serializer.handler_registry')->getArguments();

        $this->assertEquals([
            2 => ['DateTime' => ['json' => [new Reference('my_service'), 'deserializeDateTimeFromjson']]],
            1 => ['DateTime' => ['json' => [new Reference('my_service'), 'serializeDateTimeTojson']]]
        ], $args[1]);
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

        $args = $container->getDefinition('jms_serializer.handler_registry')->getArguments();

        $this->assertSame([
            1 => ['DateTime' => ['json' => ['my_service', 'serializeDateTimeTojson']]]
        ], $args[1]);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The direction "bar" of tag "jms_serializer.handler" of service "my_service" does not exist
     */
    public function testHandlerIncorrectDirection()
    {
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

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Each tag named "jms_serializer.handler" of service "my_service" must have at least two attributes: "type" and "format"
     */
    public function testHandlerMustHaveTypeAndFormat()
    {
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

        $args = $container->getDefinition('jms_serializer.handler_registry')->getArguments();

        $this->assertSame([
            2 => ['DateTime' => ['json' => ['my_custom_service', 'deserializeDateTimeFromjson']]],
            1 => ['DateTime' => ['json' => ['my_custom_service', 'serializeDateTimeTojson']]]
        ], $args[1]);
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
            'priority' => -100
        ]);
        $container->setDefinition('my_custom_explicit_service', $userExplicitDef);

        $pass = new CustomHandlersPass();
        $pass->process($container);

        $args = $container->getDefinition('jms_serializer.handler_registry')->getArguments();

        $this->assertSame([
          2 => ['DateTime' => ['json' => ['my_custom_explicit_service', 'deserializeDateTimeFromjson']]],
          1 => ['DateTime' => ['json' => ['my_custom_explicit_service', 'serializeDateTimeTojson']]]
        ], $args[1]);
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

        $args = $container->getDefinition('jms_serializer.handler_registry')->getArguments();

        $this->assertSame([
            1 => [
                'Custom' => ['json' => ['my_service', 'serialize']],
                'Custom<?>' => ['json' => ['my_service', 'serialize']],
            ],
            2 => [
                'Custom' => ['json' => ['my_service', 'deserialize']],
                'Custom<?>' => ['json' => ['my_service', 'deserialize']],
            ]
        ], $args[1]);
    }

    public function testSubscribingHandler()
    {
        $container = $this->getContainer();

        $def = new Definition('JMS\SerializerBundle\Tests\DependencyInjection\Fixture\SubscribingHandler');
        $def->addTag('jms_serializer.subscribing_handler');
        $container->setDefinition('my_service', $def);

        $pass = new CustomHandlersPass();
        $pass->process($container);

        $args = $container->getDefinition('jms_serializer.handler_registry')->getArguments();

        $this->assertSame([
            1 => ['DateTime' => ['json' => ['my_service', 'onDateTime']]]
        ], $args[1]);
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

        $args = $container->getDefinition('jms_serializer.handler_registry')->getArguments();

        $this->assertEquals([
            1 => ['DateTime' => ['json' => [new Reference('my_service'), 'onDateTime']]]
        ], $args[1]);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The service "my_service" must implement the SubscribingHandlerInterface
     */
    public function testSubscribingHandlerInterface()
    {
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

        $args = $container->getDefinition('jms_serializer.handler_registry')->getArguments();

        $this->assertSame([
            1 => ['DateTime' => ['json' => ['my_custom_service', 'onDateTime']]]
        ], $args[1]);
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

        $args = $container->getDefinition('jms_serializer.handler_registry')->getArguments();

        $this->assertSame([
            1 => ['DateTime' => ['json' => ['my_custom_explicit_service', 'onDateTime']]]
        ], $args[1]);
    }

}
