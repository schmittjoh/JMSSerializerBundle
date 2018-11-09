<?php

namespace JMS\SerializerBundle\Tests\ContextFactory;

use JMS\Serializer\Context;
use JMS\Serializer\SerializationContext;
use JMS\SerializerBundle\ContextFactory\ConfiguredContextFactory;
use PHPUnit\Framework\TestCase;

/**
 * Class ConfiguredContextFactoryTest
 */
class ConfiguredContextFactoryTest extends TestCase
{
    /**
     * testCreateSerializationContext
     *
     * @param string $version
     * @param bool $serializeNulls
     * @param array $attributes
     * @param array $groups
     * @param string $expectedInterface
     * @param string $expectedContextClass
     * @param string $factoryMethod
     *
     * @return void
     * @dataProvider contextConfigDataProvider
     */
    public function testCreateSerializationContext(
        $version,
        $serializeNulls,
        array $attributes,
        array $groups,
        $expectedInterface,
        $expectedContextClass,
        $factoryMethod
    )
    {
        $object = new ConfiguredContextFactory();

        $object->setVersion($version);
        $object->setSerializeNulls($serializeNulls);
        $object->setGroups($groups);
        $object->setAttributes($attributes);

        $this->assertInstanceOf($expectedInterface, $object);

        $context = $object->$factoryMethod();
        /** @var Context $context */
        $this->assertInstanceOf($expectedContextClass, $context);
        if ($context instanceof SerializationContext) {
            $this->assertSame($serializeNulls, $context->shouldSerializeNull());
        }

        $this->assertSame($version, $context->getAttribute('version'));
        $this->assertSame($groups, $context->getAttribute('groups'));
        foreach ($attributes as $k => $v) {
            $this->assertSame($v, $context->getAttribute($k));
        }
    }

    public function contextConfigDataProvider()
    {
        return [
            [
                sprintf('%d.%d.%d', mt_rand(1, 10), mt_rand(1, 10), mt_rand(1, 10)),
                true,
                [
                    'x' => mt_rand(0, PHP_INT_MAX),
                ],
                ['Default', 'Registration'],
                'JMS\Serializer\ContextFactory\SerializationContextFactoryInterface',
                'JMS\Serializer\SerializationContext',
                'createSerializationContext'
            ],
            [
                sprintf('%d.%d.%d', mt_rand(1, 10), mt_rand(1, 10), mt_rand(1, 10)),
                true,
                [
                    'x' => mt_rand(0, PHP_INT_MAX),
                ],
                ['Default', 'Registration'],
                'JMS\Serializer\ContextFactory\DeserializationContextFactoryInterface',
                'JMS\Serializer\DeserializationContext',
                'createDeserializationContext'
            ],
        ];
    }
}
