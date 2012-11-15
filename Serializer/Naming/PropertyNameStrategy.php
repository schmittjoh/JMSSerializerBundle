<?php
namespace JMS\SerializerBundle\Serializer\Naming;

use JMS\SerializerBundle\Serializer\Naming\PropertyNamingStrategyInterface;

class PropertyNameStrategy implements PropertyNamingStrategyInterface
{
    public function translateName(PropertyMetadata $property)
    {
        return $property->name;
    }
}
