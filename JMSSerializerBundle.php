<?php

namespace JMS\SerializerBundle;

use JMS\SerializerBundle\DependencyInjection\Compiler\CustomHandlersPass;
use JMS\SerializerBundle\DependencyInjection\Compiler\DoctrinePass;
use JMS\SerializerBundle\DependencyInjection\Compiler\AssignVisitorsPass;
use JMS\SerializerBundle\DependencyInjection\Compiler\ExpressionFunctionProviderPass;
use JMS\SerializerBundle\DependencyInjection\Compiler\FormErrorHandlerTranslationDomainPass;
use JMS\SerializerBundle\DependencyInjection\Compiler\RegisterEventListenersAndSubscribersPass;
use JMS\SerializerBundle\DependencyInjection\Compiler\TwigExtensionPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class JMSSerializerBundle extends Bundle
{
    public function build(ContainerBuilder $builder)
    {
        $builder->addCompilerPass(new AssignVisitorsPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
        $builder->addCompilerPass(new FormErrorHandlerTranslationDomainPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
        $builder->addCompilerPass(new TwigExtensionPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
        $builder->addCompilerPass(new ExpressionFunctionProviderPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
        $builder->addCompilerPass(new RegisterEventListenersAndSubscribersPass(), PassConfig::TYPE_BEFORE_REMOVING);
        $builder->addCompilerPass(new CustomHandlersPass(), PassConfig::TYPE_BEFORE_REMOVING);
        $builder->addCompilerPass(new DoctrinePass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
    }
}
