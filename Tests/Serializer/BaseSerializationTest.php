<?php

/*
 * Copyright 2011 Johannes M. Schmitt <schmittjoh@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace JMS\SerializerBundle\Tests\Serializer;

use JMS\SerializerBundle\Serializer\GraphNavigator;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\IdentityTranslator;
use JMS\SerializerBundle\Serializer\EventDispatcher\Subscriber\DoctrineProxySubscriber;
use JMS\SerializerBundle\Serializer\Handler\HandlerRegistry;
use JMS\SerializerBundle\Serializer\EventDispatcher\EventDispatcher;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\SerializerBundle\Metadata\Driver\AnnotationDriver;
use JMS\SerializerBundle\Serializer\Construction\UnserializeObjectConstructor;
use JMS\SerializerBundle\Serializer\Handler\ArrayCollectionHandler;
use JMS\SerializerBundle\Serializer\Handler\ConstraintViolationHandler;
use JMS\SerializerBundle\Serializer\Handler\DateTimeHandler;
use JMS\SerializerBundle\Serializer\Handler\DeserializationHandlerInterface;
use JMS\SerializerBundle\Serializer\Handler\DoctrineProxyHandler;
use JMS\SerializerBundle\Serializer\Handler\FormErrorHandler;
use JMS\SerializerBundle\Serializer\Handler\ObjectBasedCustomHandler;
use JMS\SerializerBundle\Serializer\Handler\SerializationHandlerInterface;
use JMS\SerializerBundle\Serializer\JsonDeserializationVisitor;
use JMS\SerializerBundle\Serializer\JsonSerializationVisitor;
use JMS\SerializerBundle\Serializer\Naming\CamelCaseNamingStrategy;
use JMS\SerializerBundle\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\SerializerBundle\Serializer\Serializer;
use JMS\SerializerBundle\Serializer\VisitorInterface;
use JMS\SerializerBundle\Serializer\XmlDeserializationVisitor;
use JMS\SerializerBundle\Serializer\XmlSerializationVisitor;
use JMS\SerializerBundle\Serializer\YamlSerializationVisitor;
use JMS\SerializerBundle\Tests\Fixtures\AccessorOrderChild;
use JMS\SerializerBundle\Tests\Fixtures\AccessorOrderParent;
use JMS\SerializerBundle\Tests\Fixtures\Author;
use JMS\SerializerBundle\Tests\Fixtures\AuthorList;
use JMS\SerializerBundle\Tests\Fixtures\AuthorReadOnly;
use JMS\SerializerBundle\Tests\Fixtures\BlogPost;
use JMS\SerializerBundle\Tests\Fixtures\CircularReferenceParent;
use JMS\SerializerBundle\Tests\Fixtures\Comment;
use JMS\SerializerBundle\Tests\Fixtures\CurrencyAwareOrder;
use JMS\SerializerBundle\Tests\Fixtures\CurrencyAwarePrice;
use JMS\SerializerBundle\Tests\Fixtures\CustomDeserializationObject;
use JMS\SerializerBundle\Tests\Fixtures\GetSetObject;
use JMS\SerializerBundle\Tests\Fixtures\GroupsObject;
use JMS\SerializerBundle\Tests\Fixtures\IndexedCommentsBlogPost;
use JMS\SerializerBundle\Tests\Fixtures\InlineParent;
use JMS\SerializerBundle\Tests\Fixtures\Log;
use JMS\SerializerBundle\Tests\Fixtures\ObjectWithLifecycleCallbacks;
use JMS\SerializerBundle\Tests\Fixtures\ObjectWithVersionedVirtualProperties;
use JMS\SerializerBundle\Tests\Fixtures\ObjectWithVirtualProperties;
use JMS\SerializerBundle\Tests\Fixtures\Order;
use JMS\SerializerBundle\Tests\Fixtures\Price;
use JMS\SerializerBundle\Tests\Fixtures\SimpleObject;
use JMS\SerializerBundle\Tests\Fixtures\ObjectWithNullProperty;
use JMS\SerializerBundle\Tests\Fixtures\SimpleObjectProxy;
use JMS\SerializerBundle\Tests\Fixtures\Article;
use JMS\SerializerBundle\Tests\Fixtures\Input;
use JMS\SerializerBundle\Tests\Fixtures\ObjectWithEmptyHash;
use Metadata\MetadataFactory;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Yaml\Inline;

abstract class BaseSerializationTest extends \PHPUnit_Framework_TestCase
{
    protected $factory;
    protected $dispatcher;
    protected $serializer;
    protected $handlerRegistry;
    protected $serializationVisitors;
    protected $deserializationVisitors;

    public function testSerializeNullArray()
    {
        $arr = array('foo' => 'bar', 'baz' => null, null);

        $this->serializer->setSerializeNull(true);
        $this->assertEquals($this->getContent('nullable'), $this->serializer->serialize($arr, $this->getFormat()));
        $this->serializer->setSerializeNull(false);
    }

    public function testSerializeNullObject()
    {
        $obj = new ObjectWithNullProperty('foo', 'bar');

        $this->serializer->setSerializeNull(true);
        $this->assertEquals($this->getContent('simple_object_nullable'), $this->serializer->serialize($obj, $this->getFormat()));
        $this->serializer->setSerializeNull(false);
    }

    public function testNull()
    {
        $this->assertEquals($this->getContent('null'), $this->serialize(null));

        if ($this->hasDeserializer()) {
            $this->assertEquals(null, $this->deserialize($this->getContent('null'), 'NULL'));
        }
    }

    public function testString()
    {
        $this->assertEquals($this->getContent('string'), $this->serialize('foo'));

        if ($this->hasDeserializer()) {
            $this->assertEquals('foo', $this->deserialize($this->getContent('string'), 'string'));
        }
    }

    /**
     * @dataProvider getBooleans
     */
    public function testBooleans($strBoolean, $boolean)
    {
        $this->assertEquals($this->getContent('boolean_'.$strBoolean), $this->serialize($boolean));

        if ($this->hasDeserializer()) {
            $this->assertSame($boolean, $this->deserialize($this->getContent('boolean_'.$strBoolean), 'boolean'));
        }
    }

    public function getBooleans()
    {
        return array(array('true', true), array('false', false));
    }

    /**
     * @dataProvider getNumerics
     */
    public function testNumerics($key, $value)
    {
        $this->assertEquals($this->getContent($key), $this->serialize($value));

        if ($this->hasDeserializer()) {
            $this->assertEquals($value, $this->deserialize($this->getContent($key), is_double($value) ? 'double' : 'integer'));
        }
    }

    public function getNumerics()
    {
        return array(
            array('integer', 1),
            array('float', 4.533),
            array('float_trailing_zero', 1.0),
        );
    }

    public function testSimpleObject()
    {
        $this->assertEquals($this->getContent('simple_object'), $this->serialize($obj = new SimpleObject('foo', 'bar')));

        if ($this->hasDeserializer()) {
            $this->assertEquals($obj, $this->deserialize($this->getContent('simple_object'), get_class($obj)));
        }
    }

    public function testArrayStrings()
    {
        $data = array('foo', 'bar');
        $this->assertEquals($this->getContent('array_strings'), $this->serialize($data));

        if ($this->hasDeserializer()) {
            $this->assertEquals($data, $this->deserialize($this->getContent('array_strings'), 'array<string>'));
        }
    }

    public function testArrayBooleans()
    {
        $data = array(true, false);
        $this->assertEquals($this->getContent('array_booleans'), $this->serialize($data));

        if ($this->hasDeserializer()) {
            $this->assertEquals($data, $this->deserialize($this->getContent('array_booleans'), 'array<boolean>'));
        }
    }

    public function testArrayIntegers()
    {
        $data = array(1, 3, 4);
        $this->assertEquals($this->getContent('array_integers'), $this->serialize($data));

        if ($this->hasDeserializer()) {
            $this->assertEquals($data, $this->deserialize($this->getContent('array_integers'), 'array<integer>'));
        }
    }

    public function testArrayFloats()
    {
        $data = array(1.34, 3.0, 6.42);
        $this->assertEquals($this->getContent('array_floats'), $this->serialize($data));

        if ($this->hasDeserializer()) {
            $this->assertEquals($data, $this->deserialize($this->getContent('array_floats'), 'array<double>'));
        }
    }

    public function testArrayObjects()
    {
        $data = array(new SimpleObject('foo', 'bar'), new SimpleObject('baz', 'boo'));
        $this->assertEquals($this->getContent('array_objects'), $this->serialize($data));

        if ($this->hasDeserializer()) {
            $this->assertEquals($data, $this->deserialize($this->getContent('array_objects'), 'array<JMS\SerializerBundle\Tests\Fixtures\SimpleObject>'));
        }
    }

    public function testArrayMixed()
    {
        $this->assertEquals($this->getContent('array_mixed'), $this->serialize(array('foo', 1, true, new SimpleObject('foo', 'bar'), array(1, 3, true))));
    }

    public function testBlogPost()
    {
        $post = new BlogPost('This is a nice title.', $author = new Author('Foo Bar'), new \DateTime('2011-07-30 00:00', new \DateTimeZone('UTC')));
        $post->addComment($comment = new Comment($author, 'foo'));

        $this->assertEquals($this->getContent('blog_post'), $this->serialize($post));

        if ($this->hasDeserializer()) {
            $deserialized = $this->deserialize($this->getContent('blog_post'), get_class($post));
            $this->assertEquals('2011-07-30T00:00:00+0000', $this->getField($deserialized, 'createdAt')->format(\DateTime::ISO8601));
            $this->assertAttributeEquals('This is a nice title.', 'title', $deserialized);
            $this->assertAttributeSame(false, 'published', $deserialized);
            $this->assertAttributeEquals(new ArrayCollection(array($comment)), 'comments', $deserialized);
            $this->assertAttributeEquals($author, 'author', $deserialized);
        }
    }

    public function testReadOnly()
    {
        $author = new AuthorReadOnly(123, 'Ruud Kamphuis');
        $this->assertEquals($this->getContent('readonly'), $this->serialize($author));

        if ($this->hasDeserializer()) {
            $deserialized = $this->deserialize($this->getContent('readonly'), get_class($author));
            $this->assertNull($this->getField($deserialized, 'id'));
            $this->assertEquals('Ruud Kamphuis', $this->getField($deserialized, 'name'));
        }
    }

    public function testPrice()
    {
        $price = new Price(3);
        $this->assertEquals($this->getContent('price'), $this->serialize($price));

        if ($this->hasDeserializer()) {
            $deserialized = $this->deserialize($this->getContent('price'), get_class($price));
            $this->assertEquals(3, $this->getField($deserialized, 'price'));
        }
    }

    public function testOrder()
    {
        $order = new Order(new Price(12.34));
        $this->assertEquals($this->getContent('order'), $this->serialize($order));

        if ($this->hasDeserializer()) {
            $this->assertEquals($order, $this->deserialize($this->getContent('order'), get_class($order)));
        }
    }

    public function testCurrencyAwarePrice()
    {
        $price = new CurrencyAwarePrice(2.34);
        $this->assertEquals($this->getContent('currency_aware_price'), $this->serialize($price));

        if ($this->hasDeserializer()) {
            $this->assertEquals($price, $this->deserialize($this->getContent('currency_aware_price'), get_class($price)));
        }
    }

    public function testOrderWithCurrencyAwarePrice()
    {
        $order = new CurrencyAwareOrder(new CurrencyAwarePrice(1.23));
        $this->assertEquals($this->getContent('order_with_currency_aware_price'), $this->serialize($order));

        if ($this->hasDeserializer()) {
            $this->assertEquals($order, $this->deserialize($this->getContent('order_with_currency_aware_price'), get_class($order)));
        }
    }

    /**
     * @group handlerCallback
     */
    public function testArticle()
    {
        $article = new Article();
        $article->element = 'custom';
        $article->value = 'serialized';

        $result = $this->serialize($article);
        $this->assertEquals($this->getContent('article'), $result);

        if ($this->hasDeserializer()) {
            $this->assertEquals($article, $this->deserialize($result, 'JMS\SerializerBundle\Tests\Fixtures\Article'));
        }
    }

    public function testInline()
    {
        $inline = new InlineParent();

        $result = $this->serialize($inline);
        $this->assertEquals($this->getContent('inline'), $result);

        // no deserialization support
    }

    /**
     * @group log
     */
    public function testLog()
    {
        $this->assertEquals($this->getContent('log'), $this->serialize($log = new Log()));

        if ($this->hasDeserializer()) {
            $deserialized = $this->deserialize($this->getContent('log'), get_class($log));
            $this->assertEquals($log, $deserialized);
        }
    }

    public function testCircularReference()
    {
        $object = new CircularReferenceParent();
        $this->assertEquals($this->getContent('circular_reference'), $this->serialize($object));

        if ($this->hasDeserializer()) {
            $deserialized = $this->deserialize($this->getContent('circular_reference'), get_class($object));

            $col = $this->getField($deserialized, 'collection');
            $this->assertEquals(2, count($col));
            $this->assertEquals('child1', $col[0]->getName());
            $this->assertEquals('child2', $col[1]->getName());
            $this->assertSame($deserialized, $col[0]->getParent());
            $this->assertSame($deserialized, $col[1]->getParent());

            $col = $this->getField($deserialized, 'anotherCollection');
            $this->assertEquals(2, count($col));
            $this->assertEquals('child1', $col[0]->getName());
            $this->assertEquals('child2', $col[1]->getName());
            $this->assertSame($deserialized, $col[0]->getParent());
            $this->assertSame($deserialized, $col[1]->getParent());
        }
    }

    public function testLifecycleCallbacks()
    {
        $object = new ObjectWithLifecycleCallbacks();
        $this->assertEquals($this->getContent('lifecycle_callbacks'), $this->serialize($object));
        $this->assertAttributeSame(null, 'name', $object);

        if ($this->hasDeserializer()) {
            $deserialized = $this->deserialize($this->getContent('lifecycle_callbacks'), get_class($object));
            $this->assertEquals($object, $deserialized);
        }
    }

    public function testFormErrors()
    {
        $errors = array(
            new FormError('This is the form error'),
            new FormError('Another error')
        );

        $this->assertEquals($this->getContent('form_errors'), $this->serialize($errors));
    }

    public function testNestedFormErrors()
    {
        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $formConfigBuilder = new \Symfony\Component\Form\FormConfigBuilder('foo', null, $dispatcher);
        $formConfigBuilder->setCompound(true);
        $formConfigBuilder->setDataMapper($this->getMock('Symfony\Component\Form\DataMapperInterface'));
        $fooConfig = $formConfigBuilder->getFormConfig();

        $form = new Form($fooConfig);
        $form->addError(new FormError('This is the form error'));

        $formConfigBuilder = new \Symfony\Component\Form\FormConfigBuilder('bar', null, $dispatcher);
        $barConfig = $formConfigBuilder->getFormConfig();
        $child = new Form($barConfig);
        $child->addError(new FormError('Error of the child form'));
        $form->add($child);

        $this->assertEquals($this->getContent('nested_form_errors'), $this->serialize($form));
    }

    public function testConstraintViolation()
    {
        $violation = new ConstraintViolation('Message of violation', array(), null, 'foo', null);

        $this->assertEquals($this->getContent('constraint_violation'), $this->serialize($violation));
    }

    public function testConstraintViolationList()
    {
        $violations = new ConstraintViolationList();
        $violations->add(new ConstraintViolation('Message of violation', array(), null, 'foo', null));
        $violations->add(new ConstraintViolation('Message of another violation', array(), null, 'bar', null));

        $this->assertEquals($this->getContent('constraint_violation_list'), $this->serialize($violations));
    }

    public function testDoctrineProxy()
    {
        if (!class_exists('Doctrine\ORM\Version')) {
            $this->markTestSkipped('Doctrine is not available.');
        }

        $object = new SimpleObjectProxy('foo', 'bar');

        $this->assertEquals($this->getContent('orm_proxy'), $this->serialize($object));
    }

    public function testInitializedDoctrineProxy()
    {
        if (!class_exists('Doctrine\ORM\Version')) {
            $this->markTestSkipped('Doctrine is not available.');
        }

        $object = new SimpleObjectProxy('foo', 'bar');
        $object->__load();

        $this->assertEquals($this->getContent('orm_proxy'), $this->serialize($object));
    }

    public function testCustomAccessor()
    {
        $post = new IndexedCommentsBlogPost();

        $this->assertEquals($this->getContent('custom_accessor'), $this->serialize($post));
    }

    public function testMixedAccessTypes()
    {
        $object = new GetSetObject();

        $this->assertEquals($this->getContent('mixed_access_types'), $this->serialize($object));

        if ($this->hasDeserializer()) {
            $object = $this->deserialize($this->getContent('mixed_access_types'), 'JMS\SerializerBundle\Tests\Fixtures\GetSetObject');
            $this->assertAttributeEquals(1, 'id', $object);
            $this->assertAttributeEquals('Johannes', 'name', $object);
            $this->assertAttributeEquals(42, 'readOnlyProperty', $object);
        }
    }

    public function testAccessorOrder()
    {
        $this->assertEquals($this->getContent('accessor_order_child'), $this->serialize(new AccessorOrderChild()));
        $this->assertEquals($this->getContent('accessor_order_parent'), $this->serialize(new AccessorOrderParent()));
    }

    public function testGroups()
    {
        $groupsObject = new GroupsObject();

        $this->assertEquals($this->getContent('groups_all'), $this->serializer->serialize($groupsObject, $this->getFormat()));

        $this->serializer->setGroups(array("foo"));
        $this->assertEquals($this->getContent('groups_foo'), $this->serializer->serialize($groupsObject, $this->getFormat()));

        $this->serializer->setGroups(array("foo", "bar"));
        $this->assertEquals($this->getContent('groups_foobar'), $this->serializer->serialize($groupsObject, $this->getFormat()));

        $this->serializer->setGroups(null);
        $this->assertEquals($this->getContent('groups_all'), $this->serializer->serialize($groupsObject, $this->getFormat()));

        $this->serializer->setGroups(array());
        $this->assertEquals($this->getContent('groups_all'), $this->serializer->serialize($groupsObject, $this->getFormat()));

        $this->serializer->setGroups(array('Default'));
        $this->assertEquals($this->getContent('groups_default'), $this->serializer->serialize($groupsObject, $this->getFormat()));
    }

    public function testVirtualProperty()
    {
        $this->assertEquals($this->getContent('virtual_properties'), $this->serialize(new ObjectWithVirtualProperties()));
    }

    public function testVirtualVersions()
    {
        $this->serializer->setVersion(2);
        $this->assertEquals($this->getContent('virtual_properties_low'), $this->serializer->serialize(new ObjectWithVersionedVirtualProperties(), $this->getFormat()));

        $this->serializer->setVersion(7);
        $this->assertEquals($this->getContent('virtual_properties_all'), $this->serializer->serialize(new ObjectWithVersionedVirtualProperties(), $this->getFormat()));

        $this->serializer->setVersion(9);
        $this->assertEquals($this->getContent('virtual_properties_high'), $this->serializer->serialize(new ObjectWithVersionedVirtualProperties(), $this->getFormat()));
    }

    public function testCustomHandler()
    {
        if ( ! $this->hasDeserializer()) {
            return;
        }

        $handler = function() {
            return new CustomDeserializationObject('customly_unserialized_value');
        };

        $this->handlerRegistry->registerHandler(GraphNavigator::DIRECTION_DESERIALIZATION, 'CustomDeserializationObject', $this->getFormat(), $handler);

        $serialized = $this->serializer->serialize(new CustomDeserializationObject('sometext'), $this->getFormat());
        $object = $this->serializer->deserialize($serialized, 'CustomDeserializationObject', $this->getFormat());
        $this->assertEquals('customly_unserialized_value', $object->someProperty);
    }

    public function testInput()
    {
        $this->assertEquals($this->getContent('input'), $this->serializer->serialize(new Input(), $this->getFormat()));
    }

    public function testObjectWithEmptyHash()
    {
        $this->assertEquals($this->getContent('hash_empty'), $this->serializer->serialize(new ObjectWithEmptyHash(), $this->getFormat()));
    }

    abstract protected function getContent($key);
    abstract protected function getFormat();

    protected function hasDeserializer()
    {
        return true;
    }

    protected function serialize($data)
    {
        return $this->serializer->serialize($data, $this->getFormat());
    }

    protected function deserialize($content, $type)
    {
        return $this->serializer->deserialize($content, $type, $this->getFormat());
    }

    protected function setUp()
    {
        $this->factory = new MetadataFactory(new AnnotationDriver(new AnnotationReader()));

        $this->handlerRegistry = new HandlerRegistry();
        $this->handlerRegistry->registerSubscribingHandler(new ConstraintViolationHandler());
        $this->handlerRegistry->registerSubscribingHandler(new DateTimeHandler());
        $this->handlerRegistry->registerSubscribingHandler(new FormErrorHandler(new IdentityTranslator(new MessageSelector())));
        $this->handlerRegistry->registerSubscribingHandler(new ArrayCollectionHandler());
        $this->handlerRegistry->registerHandler(GraphNavigator::DIRECTION_SERIALIZATION, 'AuthorList', $this->getFormat(),
            function(VisitorInterface $visitor, $object, array $type) {
                return $visitor->visitArray(iterator_to_array($object), $type);
            }
        );
        $this->handlerRegistry->registerHandler(GraphNavigator::DIRECTION_DESERIALIZATION, 'AuthorList', $this->getFormat(),
            function(VisitorInterface $visitor, $data) {
                $type = array(
                    'name' => 'array',
                    'params' => array(
                        array('name' => 'integer', 'params' => array()),
                        array('name' => 'JMS\SerializerBundle\Tests\Fixtures\Author', 'params' => array()),
                    ),
                );

                $elements = $visitor->getNavigator()->accept($data, $type, $visitor);
                $list = new AuthorList();
                foreach ($elements as $author) {
                    $list->add($author);
                }

                return $list;
            }
        );

        $this->dispatcher = new EventDispatcher();
        $this->dispatcher->addSubscriber(new DoctrineProxySubscriber());

        $namingStrategy = new SerializedNameAnnotationStrategy(new CamelCaseNamingStrategy());
        $objectConstructor = new UnserializeObjectConstructor();
        $this->serializationVisitors = array(
            'json' => new JsonSerializationVisitor($namingStrategy),
            'xml'  => new XmlSerializationVisitor($namingStrategy),
            'yml'  => new YamlSerializationVisitor($namingStrategy),
        );
        $this->deserializationVisitors = array(
            'json' => new JsonDeserializationVisitor($namingStrategy),
            'xml'  => new XmlDeserializationVisitor($namingStrategy),
        );

        $this->serializer = new Serializer($this->factory, $this->handlerRegistry, $objectConstructor, $this->dispatcher, null, $this->serializationVisitors, $this->deserializationVisitors);
    }

    private function getField($obj, $name)
    {
        $ref = new \ReflectionProperty($obj, $name);
        $ref->setAccessible(true);

        return $ref->getValue($obj);
    }

    private function setField($obj, $name, $value)
    {
        $ref = new \ReflectionProperty($obj, $name);
        $ref->setAccessible(true);
        $ref->setValue($obj, $value);
    }
}
