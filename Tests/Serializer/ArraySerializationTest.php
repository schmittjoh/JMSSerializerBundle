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

use JMS\SerializerBundle\Exception\RuntimeException;

class ArraySerializationTest extends BaseSerializationTest
{
    protected function getContent($key)
    {
        static $outputs = array();

        if (!$outputs) {
            $outputs['readonly'] = array("id" => 123, "full_name" => "Ruud Kamphuis");
            $outputs['string'] = 'foo';
            $outputs['boolean_true'] = true;
            $outputs['boolean_false'] = false;
            $outputs['integer'] = 1;
            $outputs['float'] = 4.533;
            $outputs['float_trailing_zero'] = 1;
            $outputs['simple_object'] = array('foo' => 'foo', 'moo' => 'bar', 'camelCase' => 'boo');
            $outputs['circular_reference'] = array(
                "collection" => array(array("name" => "child1"),array("name" => "child2")),
                "anotherCollection" => array(array("name" => "child1"),array("name" => "child2"))
            );
            $outputs['array_strings'] = array('foo', 'bar');
            $outputs['array_booleans'] = array(true, false);
            $outputs['array_integers'] = array(1,3,4);
            $outputs['array_floats'] = array(1.34,3,6.42);
            $outputs['array_objects'] = array(array("foo" => "foo", "moo" => "bar", "camelCase" => "boo"), array("foo" => "baz", "moo" => "boo", "camelCase" => "boo"));
            $outputs['array_mixed'] = array("foo", 1, true, array("foo" => "foo", "moo" => "bar", "camelCase" => "boo"), array(1,3,true));
            $outputs['blog_post'] = array("title" => "This is a nice title.","createdAt" => "2011-07-30T00:00:00+0000","is_published" => false,"comments" => array(array("author" => array("full_name" => "Foo Bar"),"text" => "foo")),"author" => array("full_name" => "Foo Bar"));
            $outputs['price'] = array("price" => 3);
            $outputs['currency_aware_price'] = array("currency" => "EUR","amount" => 2.34);
            $outputs['order'] = array("cost" => array("price" => 12.34));
            $outputs['order_with_currency_aware_price'] = array("cost" => array("currency" => "EUR","amount" => 1.23));
            $outputs['log'] = array("author_list" => array(array("full_name" => "Johannes Schmitt"),array("full_name" => "John Doe")),"comments" => array(array("author" => array("full_name" => "Foo Bar"),"text" => "foo"),array("author" => array("full_name" => "Foo Bar"),"text" => "bar"),array("author" => array("full_name" => "Foo Bar"),"text" => "baz")));
            $outputs['lifecycle_callbacks'] = array("name" => "Foo Bar");
            $outputs['form_errors'] = array("This is the form error","Another error");
            $outputs['nested_form_errors'] = array("errors" => array("This is the form error"),"children" => array("bar" => array("errors" => array("Error of the child form"))));
            $outputs['constraint_violation'] = array("property_path" => "foo","message" => "Message of violation");
            $outputs['constraint_violation_list'] = array(array("property_path" => "foo","message" => "Message of violation"),array("property_path" => "bar","message" => "Message of another violation"));
            $outputs['article'] = array("custom" => "serialized");
            $outputs['orm_proxy'] = array("foo" => "foo","moo" => "bar","camelCase" => "proxy-boo");
            $outputs['custom_accessor'] = array("comments" => array("Foo" => array("comments" => array(array("author" => array("full_name" => "Foo"),"text" => "foo"),array("author" => array("full_name" => "Foo"),"text" => "bar")),"count" => 2)));
            $outputs['mixed_access_types'] = array("id" => 1,"name" => "Johannes","readOnlyProperty" => 42);
            $outputs['accessor_order_child'] = array("c" => "c","d" => "d","a" => "a","b" => "b");
            $outputs['accessor_order_parent'] = array("a" => "a","b" => "b");
            $outputs['inline'] = array("c" => "c","a" => "a","b" => "b","d" => "d");
            $outputs['groups_all'] = array("foo" => "foo","foobar" => "foobar","bar" => "bar","none" => "none");
            $outputs['groups_foo'] = array("foo" => "foo","foobar" => "foobar");
            $outputs['groups_foobar'] = array("foo" => "foo","foobar" => "foobar","bar" => "bar");
            $outputs['virtual_properties'] = array("existField" => "value","virtualValue" => "value","test" => "other-name");
            $outputs['virtual_properties_low'] = array("low" => 1);
            $outputs['virtual_properties_high'] = array("high" => 8);
            $outputs['virtual_properties_all'] = array("low" => 1,"high" => 8);
        }

        if (!isset($outputs[$key])) {
            throw new RuntimeException(sprintf('The key "%s" is not supported.', $key));
        }

        return $outputs[$key];
    }

    protected function getFormat()
    {
        return 'array';
    }

    protected function hasDeserializer()
    {
        return false;
    }

}
