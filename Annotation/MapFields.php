<?php

namespace JMS\SerializerBundle\Annotation;

use JMS\SerializerBundle\Exception\RuntimeException;

/**
 * @Annotation
 * @Target("PROPERTY")
 *
 * @author Zach Badgett <zach.badgett@gmail.com>
 */
final class MapFields
{
    public $fields;

    public function __construct(array $values)
    {
        if (!isset($values['value']) || !is_array($values['value'])) {
            throw new RuntimeException('$fields must be an array.');
        }

        $this->fields = $values['value'];
    }
}