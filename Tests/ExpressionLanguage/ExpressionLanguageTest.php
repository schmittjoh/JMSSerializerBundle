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

namespace JMS\SerializerBundle\Tests\ExpressionLanguage;

use JMS\SerializerBundle\ExpressionLanguage\BasicSerializerFunctionsProvider;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class ExpressionLanguageTest extends \PHPUnit_Framework_TestCase
{
    public function testFunctionProviderCompilation()
    {
        $provider = new BasicSerializerFunctionsProvider();

        $exp = new ExpressionLanguage();
        $exp->registerProvider($provider);

        $this->assertEquals('$this->get("foo")', $exp->compile("service('foo')"));
        $this->assertEquals('$this->getParameter("foo")', $exp->compile("parameter('foo')"));
        $this->assertEquals('call_user_func_array(array($this->get(\'security.authorization_checker\'), \'isGranted\'), array("foo", ))', $exp->compile("is_granted('foo')"));
    }

    public function testFunctionProviderEvaluation()
    {
        $provider = new BasicSerializerFunctionsProvider();

        $exp = new ExpressionLanguage();
        $exp->registerProvider($provider);

        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')->getMock();
        $container
            ->expects($this->once())
            ->method('get')->with('foo', 1)
            ->will($this->returnValue('bar'));

        $this->assertEquals('bar', $exp->evaluate("service('foo')", ['container' => $container]));

        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')->getMock();
        $container
            ->expects($this->once())
            ->method('getParameter')->with('foo')
            ->will($this->returnValue('bar'));

        $this->assertEquals('bar', $exp->evaluate("parameter('foo')", ['container' => $container]));

        $authChecker = $this->getMockBuilder('JMS\SerializerBundle\Tests\ExpressionLanguage\AuthCheckerMock')->getMock();
        $authChecker
            ->expects($this->once())
            ->method('isGranted')->with('foo')
            ->will($this->returnValue('bar'));


        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')->getMock();
        $container
            ->expects($this->once())
            ->method('get')->with('security.authorization_checker')
            ->will($this->returnValue($authChecker));

        $this->assertEquals('bar', $exp->evaluate("is_granted('foo')", ['container' => $container]));
    }
}

class AuthCheckerMock
{
    public function isGranted()
    {

    }
}

