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
use JMS\SerializerBundle\Tests\Fixtures\SimpleObject;

class JsonSerializationTest extends BaseSerializationTest
{
    protected function getContent($key)
    {
        static $outputs = array();

        if (!$outputs) {
            $outputs['string'] = '"foo"';
            $outputs['boolean_true'] = 'true';
            $outputs['boolean_false'] = 'false';
            $outputs['integer'] = '1';
            $outputs['float'] = '4.533';
            $outputs['float_trailing_zero'] = '1';
            $outputs['simple_object'] = '{"foo":"foo","moo":"bar","camel_case":"boo"}';
            $outputs['circular_reference'] = '{"collection":[{"name":"child1"},{"name":"child2"}],"another_collection":[{"name":"child1"},{"name":"child2"}]}';
            $outputs['array_strings'] = '["foo","bar"]';
            $outputs['array_booleans'] = '[true,false]';
            $outputs['array_integers'] = '[1,3,4]';
            $outputs['array_floats'] = '[1.34,3,6.42]';
            $outputs['array_objects'] = '[{"foo":"foo","moo":"bar","camel_case":"boo"},{"foo":"baz","moo":"boo","camel_case":"boo"}]';
            $outputs['array_mixed'] = '["foo",1,true,{"foo":"foo","moo":"bar","camel_case":"boo"},[1,3,true]]';
            $outputs['blog_post'] = '{"title":"This is a nice title.","created_at":"2011-07-30T00:00:00+0000","is_published":false,"comments":[{"author":{"full_name":"Foo Bar"},"text":"foo"}],"author":{"full_name":"Foo Bar"}}';
            $outputs['log'] = '{"author_list":[{"full_name":"Johannes Schmitt"},{"full_name":"John Doe"}],"comments":[{"author":{"full_name":"Foo Bar"},"text":"foo"},{"author":{"full_name":"Foo Bar"},"text":"bar"},{"author":{"full_name":"Foo Bar"},"text":"baz"}]}';
            $outputs['lifecycle_callbacks'] = '{"name":"Foo Bar"}';
            $outputs['form_errors'] = '["This is the form error","Another error"]';
            $outputs['nested_form_errors'] = '{"errors":["This is the form error"],"children":{"bar":{"errors":["Error of the child form"]}}}';
            $outputs['constraint_violation'] = '{"property_path":"foo","message":"Message of violation"}';
            $outputs['constraint_violation_list'] = '[{"property_path":"foo","message":"Message of violation"},{"property_path":"bar","message":"Message of another violation"}]';
        }

        if (!isset($outputs[$key])) {
            throw new RuntimeException(sprintf('The key "%s" is not supported.', $key));
        }

        return $outputs[$key];
    }

    protected function getFormat()
    {
        return 'json';
    }
}