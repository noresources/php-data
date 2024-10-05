<?php

/**
 * Copyright © 2024 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization\Text;

class Utf8TableRenderer extends TableRenderer
{

	const BORDER_HORIZONTAL = '─';

	const BORDER_VERTICAL = '│';

	const PADDING = ' ';

	const BORDER_TOP_LEFT = '┌';

	const BORDER_TOP_INTER = '┬';

	const BORDER_TOP_RIGHT = '┐';

	const BORDER_INTER_LEFT = '├';

	const BORDER_INTER = '┼';

	const BORDER_INTER_RIGHT = '┤';

	const BORDER_BOTTOM_LEFT = '└';

	const BORDER_BOTTOM_INTER = '┴';

	const BORDER_BOTTOM_RIGHT = '┘';
}
