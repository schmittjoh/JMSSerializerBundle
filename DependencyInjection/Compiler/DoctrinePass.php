<?php

namespace JMS\SerializerBundle\DependencyInjection\Compiler;

use JMS\SerializerBundle\DependencyInjection\ScopedContainer;
use Symfony\Component\DependencyInjection\Reference;

class DoctrinePass extends PerInstancePass
{
    protected function processInstance(ScopedContainer $container): void
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
            $driver = sprintf('jms_serializer.metadata.%s_type_driver', $registry);
            $container->getDefinition($driver)
                ->setDecoratedService($container->getDefinitionRealId('jms_serializer.metadata_driver'))
                ->replaceArgument(0, new Reference(sprintf($container->getDefinitionRealId($driver).'.inner', $registry)))
                ->setPublic(false)
                ;

            $constructor = sprintf('jms_serializer.%s_object_constructor', $registry);
            $container->getDefinition($constructor)
                ->setDecoratedService($container->getDefinitionRealId('jms_serializer.object_constructor'))
                ->replaceArgument(1, new Reference($container->getDefinitionRealId($constructor). '.inner'))
                ->setPublic(false)
                ;
        }
    }
}
