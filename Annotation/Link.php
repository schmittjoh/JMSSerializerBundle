<?php

namespace JMS\SerializerBundle\Annotation;

/**
 * @Annotation
 * @Target({"CLASS", "PROPERTY"})
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Baldur Rensch <baldur.rensch@hautelook.com>
 */
final class Link
{
    /**
     * @var string
     */
    public $rel;

    /**
     * @var string
     */
    public $route;

    /**
     * @var string
     */
    public $href;

    /**
     * @var boolean
     */
    public $templated;

    /**
     * @var array
     */
    public $parameters;

    /**
     * @var string
     */
    public $title;
}