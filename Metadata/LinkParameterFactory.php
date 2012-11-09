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

namespace JMS\SerializerBundle\Metadata;

use Symfony\Component\Form\Util\PropertyPath;

/**
 * This class extracts the parameters that are needed for the route from the data. Heavily inspired
 * by Adrien Brault's work in the HateoasBundle (https://github.com/TheFootballSocialClub/FSCHateoasBundle)
 *
 * @author Baldur Rensch <baldur.rensch@hautelook.com>
 */
class LinkParameterFactory implements LinkParameterFactoryInterface
{
	/**
	 * @inheritdoc
	 */
	public function generateParameters(array $parameters, $data)
	{
		$newParams = array();
        foreach ($parameters as $parameter => $value) {
        	if ('=' === substr($value, 0, 1)) {
            	$value = substr($value, 1);
            } else {
            	$propertyPath = new PropertyPath($value);
            	$value = $propertyPath->getValue($data);
            }

            $newParams[$parameter] = $value;
        }

        return $newParams;
	}
}