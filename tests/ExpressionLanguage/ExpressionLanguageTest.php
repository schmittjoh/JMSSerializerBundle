<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\Tests\ExpressionLanguage;

use JMS\SerializerBundle\ExpressionLanguage\BasicSerializerFunctionsProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class ExpressionLanguageTest extends TestCase
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
