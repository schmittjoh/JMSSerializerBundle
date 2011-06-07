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

use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Encoder\NormalizationAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Serializer as BaseSerializer;

/**
 * Serializer implementation.
 *
 * This serializer distinguishes three different types of normalizers, one
 * normalizer for native php types, one default normalizer for objects, and an
 * arbitrary amount of specialized normalizers for specific object classes.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Serializer extends BaseSerializer
{
    private $nativePhpTypeNormalizer;
    private $defaultObjectNormalizer;

    public function __construct(array $normalizers = array(), array $encoders = array(), NormalizerInterface $nativePhpNormalizer = null, NormalizerInterface $defaultObjectNormalizer = null)
    {
        parent::__construct($normalizers, $encoders);

        if ($nativePhpNormalizer instanceof SerializerAwareInterface) {
            $nativePhpNormalizer->setSerializer($this);
        }
        $this->nativePhpTypeNormalizer = $nativePhpNormalizer;

        if ($defaultObjectNormalizer instanceof SerializerAwareInterface) {
            $defaultObjectNormalizer->setSerializer($this);
        }
        $this->defaultObjectNormalizer = $defaultObjectNormalizer;
    }

    /**
     * {@inheritDoc}
     */
    public final function normalize($data, $format = null)
    {
        if ($this->normalizers && is_object($data)) {
            foreach ($this->normalizers as $normalizer) {
                // needs to run first so that users can override the behavior for built-in
                // interface like \Traversable, see #10
                if ($normalizer->supportsNormalization($data, $format)) {
                    return $normalizer->normalize($data, $format);
                }
            }
        }

        if ($this->nativePhpTypeNormalizer->supportsNormalization($data, $format)) {
            return $this->nativePhpTypeNormalizer->normalize($data, $format);
        }

        return $this->defaultObjectNormalizer->normalize($data, $format);
    }

    /**
     * {@inheritDoc}
     */
    public final function denormalize($data, $type, $format = null)
    {
        if ($this->nativePhpTypeNormalizer->supportsDenormalization($data, $type, $format)) {
            return $this->nativePhpTypeNormalizer->denormalize($data, $type, $format);
        }

        if ($this->normalizers) {
            foreach ($this->normalizers as $normalizer) {
                if ($normalizer->supportsDenormalization($data, $type, $format)) {
                    return $normalizer->denormalize($data, $type, $format);
                }
            }
        }

        return $this->defaultObjectNormalizer->denormalize($data, $type, $format);
    }

    /**
     * {@inheritDoc}
     */
    public final function serialize($data, $format)
    {
        $data = $this->normalize($data, $format);

        return $this->encode($data, $format);
    }

    /**
     * {@inheritDoc}
     */
    public final function deserialize($data, $type, $format)
    {
        $data = $this->decode($data, $format);

        return $this->denormalize($data, $type, $format);
    }

    /**
     * {@inheritDoc}
     */
    public final function encode($data, $format)
    {
        return $this->getEncoder($format)->encode($data, $format);
    }

    /**
     * {@inheritDoc}
     */
    public final function decode($data, $format)
    {
        return $this->getEncoder($format)->decode($data, $format);
    }

    protected function getEncoder($format)
    {
        if (!isset($this->encoderMap[$format])) {
            throw new RuntimeException(sprintf('No encoder found for format "%s".', $format));
        }

        return $this->encoderMap[$format];
    }
}
