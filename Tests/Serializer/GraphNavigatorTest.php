<?php

namespace JMS\SerializerBundle\Tests\Serializer;

use JMS\SerializerBundle\Serializer\Construction\UnserializeObjectConstructor;
use JMS\SerializerBundle\Serializer\Handler\HandlerRegistry;
use JMS\SerializerBundle\Serializer\EventDispatcher\EventDispatcher;
use Doctrine\Common\Annotations\AnnotationReader;
use JMS\SerializerBundle\Metadata\Driver\AnnotationDriver;
use JMS\SerializerBundle\Serializer\GraphNavigator;
use Metadata\MetadataFactory;

class GraphNavigatorTest extends \PHPUnit_Framework_TestCase
{
    private $metadataFactory;
    private $handlerRegistry;
    private $objectConstructor;
    private $exclusionStrategy;
    private $dispatcher;
    private $navigator;
    private $visitor;

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Resources are not supported in serialized data.
     */
    public function testResourceThrowsException()
    {
        $this->navigator->accept(STDIN, null, $this->visitor);
    }

    public function testNavigatorPassesInstanceOnSerialization()
    {
        $object = new SerializableClass;
        $metadata = $this->metadataFactory->getMetadataForClass(get_class($object));

        $exclusionStrategy = $this->getMock('JMS\SerializerBundle\Serializer\Exclusion\ExclusionStrategyInterface');
        $this->navigator = new GraphNavigator(GraphNavigator::DIRECTION_SERIALIZATION, $this->metadataFactory, 'foo', $this->handlerRegistry, $this->objectConstructor, $exclusionStrategy, $this->dispatcher);

        $self = $this;
        $exclusionStrategy->expects($this->once())
            ->method('shouldSkipClass')
            ->with(
                $metadata,
                $this->logicalAnd(
                    $this->isInstanceOf('JMS\SerializerBundle\Serializer\NavigatorContext'),
                    $this->callback(function($navigatorContext) use ($self, $object) {
                        $self->assertEquals($object, $navigatorContext->getObject());
                        $self->assertEquals(0, $navigatorContext->getDepth());
                        $self->assertEquals(GraphNavigator::DIRECTION_SERIALIZATION, $navigatorContext->getDirection());

                        return true;
                    })
                )
            )
        ;
        $exclusionStrategy->expects($this->once())
            ->method('shouldSkipProperty')
            ->with(
                $metadata->propertyMetadata['foo'],
                $this->logicalAnd(
                    $this->isInstanceOf('JMS\SerializerBundle\Serializer\NavigatorContext'),
                    $this->callback(function($navigatorContext) use ($self, $object) {
                        $self->assertEquals($object, $navigatorContext->getObject());
                        $self->assertEquals(1, $navigatorContext->getDepth());
                        $self->assertEquals(GraphNavigator::DIRECTION_SERIALIZATION, $navigatorContext->getDirection());

                        return true;
                    })
                )
            )
        ;

        $this->navigator->accept($object, null, $this->visitor);
    }

    public function testNavigatorPassesNullOnDeserialization()
    {
        $class = __NAMESPACE__.'\SerializableClass';
        $metadata = $this->metadataFactory->getMetadataForClass($class);

        $exclusionStrategy = $this->getMock('JMS\SerializerBundle\Serializer\Exclusion\ExclusionStrategyInterface');
        $this->navigator = new GraphNavigator(GraphNavigator::DIRECTION_DESERIALIZATION, $this->metadataFactory, 'foo', $this->handlerRegistry, $this->objectConstructor, $exclusionStrategy, $this->dispatcher);

        $self = $this;

        $exclusionStrategy->expects($this->once())
            ->method('shouldSkipClass')
            ->with(
                $metadata,
                $this->logicalAnd(
                    $this->isInstanceOf('JMS\SerializerBundle\Serializer\NavigatorContext'),
                    $this->callback(function($navigatorContext) use ($self) {
                        $self->assertEquals(null, $navigatorContext->getObject());
                        $self->assertEquals(0, $navigatorContext->getDepth());
                        $self->assertEquals(GraphNavigator::DIRECTION_DESERIALIZATION, $navigatorContext->getDirection());

                        return true;
                    })
                )
            )
        ;

        $exclusionStrategy->expects($this->once())
            ->method('shouldSkipProperty')
            ->with(
                $metadata->propertyMetadata['foo'],
                $this->logicalAnd(
                    $this->isInstanceOf('JMS\SerializerBundle\Serializer\NavigatorContext'),
                    $this->callback(function($navigatorContext) use ($self) {
                        $self->assertEquals(null, $navigatorContext->getObject());
                        $self->assertEquals(1, $navigatorContext->getDepth());
                        $self->assertEquals(GraphNavigator::DIRECTION_DESERIALIZATION, $navigatorContext->getDirection());

                        return true;
                    })
                )
            )
        ;

        $this->navigator->accept('random', array('name' => $class, 'params' => array()), $this->visitor);
    }

    protected function setUp()
    {
        $this->visitor = $this->getMock('JMS\SerializerBundle\Serializer\VisitorInterface');
        $this->dispatcher = new EventDispatcher();
        $this->handlerRegistry = new HandlerRegistry();
        $this->objectConstructor = new UnserializeObjectConstructor();

        $this->metadataFactory = new MetadataFactory(new AnnotationDriver(new AnnotationReader()));
        $this->navigator = new GraphNavigator(GraphNavigator::DIRECTION_SERIALIZATION, $this->metadataFactory, 'foo', $this->handlerRegistry, $this->objectConstructor, null, $this->dispatcher);
    }
}

class SerializableClass
{
    public $foo = 'bar';
}
