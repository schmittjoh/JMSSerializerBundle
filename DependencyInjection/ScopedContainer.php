<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class ScopedContainer
{
    private $container;
    private $instance;

    public function __construct(ContainerBuilder $container, string $instance)
    {
        $this->container = $container;
        $this->instance = $instance;
        $container->setAlias('jms_serializer.instances.' . $instance, new Alias($this->getDefinitionRealId('jms_serializer.serializer'), true));
    }

    public function getInstanceName(): string
    {
        return $this->instance;
    }

    public function removeDefinition($id)
    {
        $this->container->removeDefinition($this->getDefinitionRealId($id));
    }

    public function register(string $id, ?string $class = null)
    {
        return $this->setDefinition($id, new Definition($class));
    }

    public function findDefinition(string $id): Definition
    {
        return $this->container->findDefinition($this->getDefinitionRealId($id));
    }

    public function findTaggedServiceIds($tag): array
    {
        $serviceIds = [];
        foreach ($this->container->findTaggedServiceIds($tag) as $id => $tags) {
            $def = $this->container->findDefinition($id);

            if ($def->hasTag('jms_serializer.instance')) {
                if ($def->getTag('jms_serializer.instance')[0]['name'] !== $this->instance) {
                    continue;
                }
            }

            foreach ($tags as $attributes) {
                if (empty($attributes['instance']) || $attributes['instance'] === $this->instance) {
                    $serviceIds[$id][] = $attributes;
                }
            }
        }

        return $serviceIds;
    }

    private function getDefinitionRealId(string $id): string
    {
        return DIUtils::getRealId($this->instance, $id);
    }

    public function getDefinition($id): Definition
    {
        return $this->container->getDefinition($this->getDefinitionRealId($id));
    }

    public function removeAlias(string $alias)
    {
        $this->container->removeAlias($this->getDefinitionRealId($alias));
    }

    public function setAlias(string $alias, $id)
    {
        if (is_string($id)) {
            $id = new Alias($id);
        }

        $alias = $this->getDefinitionRealId($alias);

        $id = new Alias($this->getDefinitionRealId((string) $id), $id->isPublic());

        return $this->container->setAlias($alias, $id);
    }

    public function __call($name, $args)
    {
        return call_user_func_array([$this->container, $name], $args);
    }
}
