<?php

namespace JMS\SerializerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class RemoveExpressionLanguagePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!class_exists($container->getParameter('jms_serializer.expression_language.class'))) {
            $container->removeDefinition('jms_serializer.expression_evaluator');
        }
    }
}
