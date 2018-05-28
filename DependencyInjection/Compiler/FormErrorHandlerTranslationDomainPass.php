<?php

namespace JMS\SerializerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FormErrorHandlerTranslationDomainPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter('validator.translation_domain')) {
            return;
        }

        $container->findDefinition('jms_serializer.form_error_handler')
            ->addArgument('%validator.translation_domain%');
    }
}
