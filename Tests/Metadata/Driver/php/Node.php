<?php

use JMS\SerializerBundle\Metadata\ClassMetadata;
use JMS\SerializerBundle\Metadata\PropertyMetadata;

$metadata = new ClassMetadata('JMS\SerializerBundle\Tests\Fixtures\Node');

$pMetadata = new PropertyMetadata('JMS\SerializerBundle\Tests\Fixtures\Node', 'children');
$pMetadata->maxDepth = 3;
$metadata->addPropertyMetadata($pMetadata);

return $metadata;
