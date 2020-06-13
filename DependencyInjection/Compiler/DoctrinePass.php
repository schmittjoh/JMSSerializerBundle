<?php

namespace JMS\SerializerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DoctrinePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasParameter('jms_serializer.infer_types_from_doctrine_metadata')
            && $container->getParameter('jms_serializer.infer_types_from_doctrine_metadata') === false
        ) {
            return;
        }

        $registries = array(
            'doctrine.orm.entity_manager' => 'doctrine',
            'doctrine_phpcr.odm.document_manager' => 'doctrine_phpcr',
        );

        foreach ($registries as $managerId => $registry) {
            if (!$container->has($managerId)) {
                unset($registries[$managerId]);
            }
        }

        foreach ($registries as $registry) {
            $container->getDefinition(sprintf('jms_serializer.metadata.%s_type_driver', $registry))
                ->setDecoratedService('jms_serializer.metadata_driver')
                ->replaceArgument(0, new Reference(sprintf('jms_serializer.metadata.%s_type_driver.inner', $registry)))
                ->setPublic(false)
                ;

            $container->getDefinition(sprintf('jms_serializer.%s_object_constructor', $registry))
                ->setDecoratedService('jms_serializer.object_constructor')
                ->replaceArgument(1, new Reference(sprintf('jms_serializer.%s_object_constructor.inner', $registry)))
                ->setPublic(false)
                ;
        }
    }
}
