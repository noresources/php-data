<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization;

/**
 * Shared (de)serialization parameter names and values
 */
class SerializationParameter
{

	/**
	 * Indicates input data is a collection of object/array
	 *
	 * Parameter value is ignored
	 *
	 * @var string
	 */
	const COLLECTION = 'collection';

	/**
	 * Prepare input data for serialization.
	 *
	 * <ul>
	 * <li>&lt; 0: No recursion limit</li>
	 * <li>0: Do not preprocess</li>
	 * <li>&gt; 0: Preprocess input data. If data is a container, recurse until reacing the given
	 * recursion limit value</li>
	 * </ul>
	 *
	 * @var integer
	 */
	const PRE_TRANSFORM_RECURSION_LIMIT = 'preprocess-depth';

	/**
	 * Serializationpresentation style parameter name.
	 *
	 * Parameter expect a string value representing a pre-defined sytle name.
	 */
	const PRESENTATION_STYLE = 'style';

	/**
	 * Pretty print presentation style.
	 *
	 * @var string
	 */
	const PRESENTATION_STYLE_PRETTY = 'pretty';

	/**
	 * Default presentation style.
	 *
	 * @var string
	 */
	const PRESENTATION_STYLE_DEFAULT = 'default';

	/**
	 * Condensed presentation style.
	 *
	 * @var string
	 */
	const PRESENTATION_STYLE_CONDENSED = 'condensed';
}
