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

        if (empty($registries)) {
            return;
        }

        $serviceTemplates = array(
            'jms_serializer.metadata_driver' => array('template' => 'jms_serializer.metadata.%s_type_driver', 'position' => 0),
            'jms_serializer.object_constructor' => array('template' => 'jms_serializer.%s_object_constructor', 'position' => 1)
        );

        $registry = array_pop($registries);
        $previousId = array();
        foreach ($serviceTemplates as $serviceName => $service) {
            $previousId[$serviceName] = sprintf($service['template'], $registry);
            $container->setAlias($serviceName, new Alias($previousId[$serviceName], true));
        }

        foreach ($registries as $registry) {
            foreach ($serviceTemplates as $serviceName => $service) {
                $id = sprintf($service['template'], $registry);
                $container
                    ->findDefinition($id)
                    ->replaceArgument($service['position'], new Reference($previousId[$serviceName]));
                $previousId[$serviceName] = $id;
                $container->setAlias($serviceName, new Alias($previousId[$serviceName], true));
            }
        }
    }
}
