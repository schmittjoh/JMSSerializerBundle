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

/**
 * @author Baldur Rensch <baldur.rensch@hautelook.com>
 */
interface LinkParameterFactoryInterface {
	/**
	 * Retrieves parameters from the passed in data object according to the path specified in the parameters array
	 * @param  array  $parameters
	 * @param  Object $data
	 * @return array
	 */
	public function generateParameters(array $parameters, $data);
}