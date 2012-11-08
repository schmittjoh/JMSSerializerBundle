<?php

/*
 * Copyright 2011 Johannes M. Schmitt <schmittjoh@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace JMS\SerializerBundle\Serializer;

use JMS\SerializerBundle\Metadata\ClassMetadata;
use JMS\SerializerBundle\Metadata\PropertyMetadata;
use JMS\SerializerBundle\Metadata\LinkParameterFactoryInterface;
use JMS\SerializerBundle\Annotation\Link;
use JMS\SerializerBundle\Serializer\Naming\PropertyNamingStrategyInterface;
use JMS\SerializerBundle\Serializer\Construction\UnserializeObjectConstructor;
use JMS\SerializerBundle\Exception\InvalidArgumentException;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Form\Util\PropertyPath;

/**
 * @author Baldur Rensch <baldur.rensch@hautelook.com>
 */
class JsonHalSerializationVisitor extends JsonSerializationVisitor
{
    /**
     * Router for regular (non-templated links)
     * @var Router
     */
    protected $router;

    /**
     * @var LinkParameterFactoryInterface
     */
    protected $linkParameterFactory;

    public function __construct(PropertyNamingStrategyInterface $cacheNamingStrategy = null,
        UnserializeObjectConstructor $objectConstructor = null,
        RouterInterface $router = null,
        LinkParameterFactoryInterface $linkParameterFactory = null
        )
    {
        parent::__construct($cacheNamingStrategy);

        $this->router               = $router;
        $this->linkParameterFactory = $linkParameterFactory;
    }

    /**
     * Visit a link and add it to the serialization result. If the route parameter is set, then the router is used.
     * Otherwise, the href is just used verbose.
     * @param  Link   $link
     * @param  Object $data
     */
    public function visitLink(Link $link, $data)
    {
        if (!empty($link->route)) {
            $routeParams = $this->linkParameterFactory->generateParameters($link->parameters, $data);
            $l = $this->router->generate($link->route, $routeParams, true);
        } else if (!empty($link->href)) {
            $l = $link->href;
        } else {
            throw new InvalidArgumentException("A link needs either an href or a route");
        }

        $this->data['_links'][$link->rel] []= $l;
    }

    public function visitProperty(PropertyMetadata $metadata, $data)
    {
        $v = (null === $metadata->getter ? $metadata->reflection->getValue($data)
                : $data->{$metadata->getter}());

        $v = $this->navigator->accept($v, $metadata->type, $this);
        if (null === $v && !$this->shouldSerializeNull()) {
            return;
        }

        $k = $this->namingStrategy->translateName($metadata);

        if (is_array($v)) {
            if (!empty($v[0]['_links']['rel'][0])) {
                $k = (string) $v[0]['_links']['rel'][0];
            }

            $this->data['_embedded'][$k] = $v;
        } else {
            $this->data[$k] = $v;
        }
    }
}