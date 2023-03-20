<?php

declare(strict_types=1);

namespace DependencyInjection;

use JMS\Serializer\Metadata\Driver\AnnotationDriver;
use JMS\Serializer\Metadata\Driver\XmlDriver;
use JMS\Serializer\Metadata\Driver\YamlDriver;
use JMS\SerializerBundle\DependencyInjection\Compiler\AddMetadataDriversToChainPass;
use Metadata\Driver\DriverChain;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AddMetadataDriversToChainPassTest extends TestCase
{
    public function testAddsTaggedMetadataDriversToTheChain()
    {
        $container = new ContainerBuilder();

        $chainDefinition = $container->register('jms_serializer.metadata_driver', DriverChain::class);

        $container->register('jms_serializer.metadata.yaml_driver', YamlDriver::class)
            ->addTag('jms_serializer.metadata_driver', ['priority' => 30]);

        $container->register('jms_serializer.metadata.xml_driver', XmlDriver::class)
            ->addTag('jms_serializer.metadata_driver', ['priority' => 20]);

        $container->register('jms_serializer.metadata.annotation_driver', AnnotationDriver::class)
            ->addTag('jms_serializer.metadata_driver', ['priority' => 10]);

        (new AddMetadataDriversToChainPass())->process($container);

        $this->assertCount(3, $chainDefinition->getMethodCalls());
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testDoesNothingIfTheDriverChainIsNotRegistered()
    {
        (new AddMetadataDriversToChainPass())->process(new ContainerBuilder());
    }
}
