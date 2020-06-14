<?php

namespace JMS\SerializerBundle;

use JMS\DiExtraBundle\DependencyInjection\Compiler\LazyServiceMapPass;
use JMS\SerializerBundle\DependencyInjection\Compiler\CustomHandlersPass;
use JMS\SerializerBundle\DependencyInjection\Compiler\DoctrinePass;
use JMS\SerializerBundle\DependencyInjection\Compiler\ExpressionFunctionProviderPass;
use JMS\SerializerBundle\DependencyInjection\Compiler\FormErrorHandlerTranslationDomainPass;
use JMS\SerializerBundle\DependencyInjection\Compiler\RegisterEventListenersAndSubscribersPass;
use JMS\SerializerBundle\DependencyInjection\Compiler\ServiceMapPass;
use JMS\SerializerBundle\DependencyInjection\Compiler\TwigExtensionPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class JMSSerializerBundle extends Bundle
{
    public function build(ContainerBuilder $builder)
    {
        $builder->addCompilerPass($this->getServiceMapPass('jms_serializer.serialization_visitor', 'format',
            function (ContainerBuilder $container, $def) {
                $container->getDefinition('jms_serializer.serializer')->replaceArgument(2, $def);
            }
        ));
        $builder->addCompilerPass($this->getServiceMapPass('jms_serializer.deserialization_visitor', 'format',
            function (ContainerBuilder $container, $def) {
                $container->getDefinition('jms_serializer.serializer')->replaceArgument(3, $def);
            }
        ));

        $builder->addCompilerPass(new FormErrorHandlerTranslationDomainPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
        $builder->addCompilerPass(new TwigExtensionPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
        $builder->addCompilerPass(new ExpressionFunctionProviderPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
        $builder->addCompilerPass(new RegisterEventListenersAndSubscribersPass(), PassConfig::TYPE_BEFORE_REMOVING);
        $builder->addCompilerPass(new CustomHandlersPass(), PassConfig::TYPE_BEFORE_REMOVING);
        $builder->addCompilerPass(new DoctrinePass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
    }

    private function getServiceMapPass($tagName, $keyAttributeName, $callable)
    {
        if (class_exists('JMS\DiExtraBundle\DependencyInjection\Compiler\LazyServiceMapPass')) {
            return new LazyServiceMapPass($tagName, $keyAttributeName, $callable);
        }

        return new ServiceMapPass($tagName, $keyAttributeName, $callable);
    }
}
