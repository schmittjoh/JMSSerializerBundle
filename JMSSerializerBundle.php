<?php

namespace JMS\SerializerBundle;

use JMS\DiExtraBundle\DependencyInjection\Compiler\LazyServiceMapPass;
use JMS\SerializerBundle\DependencyInjection\Compiler\CustomHandlersLocatorPass;
use JMS\SerializerBundle\DependencyInjection\Compiler\CustomHandlersPass;
use JMS\SerializerBundle\DependencyInjection\Compiler\DoctrinePass;
use JMS\SerializerBundle\DependencyInjection\Compiler\ExpressionFunctionProviderPass;
use JMS\SerializerBundle\DependencyInjection\Compiler\FormErrorHandlerTranslationDomainPass;
use JMS\SerializerBundle\DependencyInjection\Compiler\RegisterEventListenersAndSubscribersPass;
use JMS\SerializerBundle\DependencyInjection\Compiler\ServiceMapPass;
use JMS\SerializerBundle\DependencyInjection\Compiler\TwigExtensionPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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

        // Should run before Symfony\Bundle\TwigBundle\DependencyInjection\Compiler\TwigEnvironmentPass
        $builder->addCompilerPass(new TwigExtensionPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 10);

        $builder->addCompilerPass(new FormErrorHandlerTranslationDomainPass());
        $builder->addCompilerPass(new ExpressionFunctionProviderPass());
        $builder->addCompilerPass(new DoctrinePass());

        $builder->addCompilerPass(new RegisterEventListenersAndSubscribersPass(), PassConfig::TYPE_OPTIMIZE);
        $builder->addCompilerPass(new CustomHandlersPass(), PassConfig::TYPE_OPTIMIZE);
    }

    private function getServiceMapPass($tagName, $keyAttributeName, $callable)
    {
        if (class_exists('JMS\DiExtraBundle\DependencyInjection\Compiler\LazyServiceMapPass')) {
            return new LazyServiceMapPass($tagName, $keyAttributeName, $callable);
        }

        return new ServiceMapPass($tagName, $keyAttributeName, $callable);
    }
}
