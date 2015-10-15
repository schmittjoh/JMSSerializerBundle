<?php

namespace JMS\SerializerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class DoctrinePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasParameter('jms_serializer.infer_types_from_doctrine_metadata') 
            && $container->getParameter('jms_serializer.infer_types_from_doctrine_metadata') === false) {
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

        if (empty($registries)) {
            return;
        }

        $serviceTemplates = array(
            'jms_serializer.metadata_driver' => 'jms_serializer.metadata.%s_type_driver',
            'jms_serializer.object_constructor' => 'jms_serializer.%s_object_constructor',
        );

        $registry = array_pop($registries);
        $previousId = array();
        foreach ($serviceTemplates as $service => $serviceTemplate) {
            $previousId[$service] = sprintf($serviceTemplate, $registry);
            $container->setAlias($service, $previousId[$service]);
        }

        foreach ($registries as $registry) {
            foreach ($serviceTemplates as $service => $serviceTemplate) {
                $id = sprintf($serviceTemplate, $registry);
                $container
                    ->getDefinition($id)
                    ->replaceArgument(0, new Reference($previousId[$service]))
                ;
                $previousId[$service] = $id;
            }
        }
    }
}
