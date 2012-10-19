<?php

use JMS\SerializerBundle\Metadata\ClassMetadata;
use JMS\SerializerBundle\Metadata\LinkMetadata;

$metadata = new ClassMetadata('JMS\SerializerBundle\Tests\Fixtures\Link');

$l = new LinkMetadata(
    'r1',
    false,
    array(
         'p1' => array('type' => 'property', 'value' => 'prop1'),
         'p2' => array('type' => 'method', 'value' => 'method1'),
         'p3' => array('type' => 'static', 'value' => 'static1')
    ),
    'http://rels.kartoncek.si/rel1',
    null,
    null,
    false,
    null
);
$metadata->addLink($l);

$l = new LinkMetadata(
    'r2',
    true,
    array(
         'p1' => array('type' => 'property', 'value' => 'prop1'),
         'p2' => array('type' => 'method', 'value' => 'method1'),
         'p3' => array('type' => 'static', 'value' => '{placeholder}'),
    ),
    'http://rels.kartoncek.si/rel2',
    '__links',
    '_link',
    true,
    'pebkac'
);
$metadata->addLink($l);

return $metadata;