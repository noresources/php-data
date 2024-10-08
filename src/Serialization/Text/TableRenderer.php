<?php

/**
 * Copyright © 2024 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization\Text;

use NoreSources\Container\Container;
use NoreSources\Data\Analyzer;
use NoreSources\Data\CollectionClass;
use NoreSources\Data\Primitifier;
use NoreSources\Helper\FunctionInvoker;
use NoreSources\Type\TypeConversion;
use NoreSources\Type\TypeDescription;

class TableRenderer
{

	const PADDING = ' ';

	/**
	 * Ellipsis character(s)
	 *
	 * Used at end of copped texts.
	 *
	 * @var string
	 */
	const ELLIPSIS = '';

	const HEADING_COLUMN = 0x01;

	const HEADING_ROW = 0x02;

	const HEADING_BOTH = 0x03;

	public $heading = self::HEADING_COLUMN;

	public $eol = "\n";

	public static function guessHeadingMode($data)
	{
		$heading = 0;
		$analyzer = Analyzer::getInstance();
		$rowClass = $columnClass = 0;
		$classes = $analyzer->getDimensionCollectionClasss($data);
		if (\count($classes))
			$rowClass = \array_shift($classes);
		if (\count($classes))
			$columnClass = \array_shift($classes);

		if (($rowClass & CollectionClass::MAP) != 0)
			$heading |= self::HEADING_ROW;
		if (($columnClass & CollectionClass::MAP) != 0)
			$heading |= self::HEADING_COLUMN;

		return $heading;
	}

	/**
	 * Set cell horizontal margins
	 *
	 * @param integer $size
	 *        	Cell horizontal margin.
	 */
	public function setMargin($size)
	{
		$this->margin = \str_repeat(self::PADDING, $size);
	}

	public function setMaxRowLength($length)
	{
		if (\is_numeric($length))
		{
			$length = \intval($length);
			if ($length < 1)
				$length = null;
			$this->maxRowLength = $length;
			return;
		}

		$this->maxRowLength = null;
	}

	/**
	 * Set a custom stringification function
	 *
	 * @param callable $callable
	 *        	User-defined stringification function
	 * @throws \InvalidArgumentException
	 */
	public function setStringifier($callable)
	{
		if (!\is_callable($callable))
			throw new \InvalidArgumentException(
				TypeDescription::getName($callable) . ' is not callable');
		$this->stringifier = $callable;
	}

	public function render($rows)
	{
		$stream = FunctionInvoker::fopen('php://memory', 'w');
		$this->write($stream, $rows);
		FunctionInvoker::fseek($stream, 0);
		$text = FunctionInvoker::stream_get_contents($stream);
		FunctionInvoker::fclose($stream);
		return $text;
	}

	public function write($stream, $rows)
	{
		$withColumnHeading = ($this->heading & self::HEADING_COLUMN) ==
			self::HEADING_COLUMN;
		$withRowHeading = ($this->heading & self::HEADING_ROW) ==
			self::HEADING_ROW;
		list ($columns, $rowHeading) = $this->preprocess($rows);

		$resized = false;
		if ($this->maxRowLength)
			$resized = $this->resizeColumns($columns, $rowHeading);

		if ($withRowHeading)
			$columns = \array_merge(
				[
					self::ROW_HEADING_COLUMN_NAME => $rowHeading
				], $columns);

		$columnSizes = \array_map(
			function ($c) {
				return $c[self::COLUMN_SIZE];
			}, $columns);

		FunctionInvoker::fwrite($stream,
			$this->renderBorderLine(self::VERTICAL_POSITION_TOP,
				$columnSizes, $resized) . $this->eol);
		if ($withColumnHeading)
		{
			$names = Container::map($columns,
				function ($n, $_) {
					return $n;
				});
			if (isset($names[self::ROW_HEADING_COLUMN_NAME]))
				$names[self::ROW_HEADING_COLUMN_NAME] = '';

			FunctionInvoker::fwrite($stream,
				$this->renderRow($names, $columns, $resized) . $this->eol);
			FunctionInvoker::fwrite($stream,
				$this->renderBorderLine(self::VERTICAL_POSITION_INTER,
					$columnSizes) . $this->eol);
		}

		foreach ($rows as $r => $row)
		{
			if ($withRowHeading)
				$row[self::ROW_HEADING_COLUMN_NAME] = [
					$r
				];

			$lines = $this->makeLinesValues($row);

			foreach ($lines as $line)
			{
				FunctionInvoker::fwrite($stream,
					$this->renderRow($line, $columns, $resized) .
					$this->eol);
			}
		}

		FunctionInvoker::fwrite($stream,
			$this->renderBorderLine(self::VERTICAL_POSITION_BOTTOM,
				$columnSizes) . $this->eol);
	}

	/**
	 *
	 * @param array $row
	 *        	Data row
	 * @param array $columns
	 *        	Column info
	 * @return string
	 *
	 */
	public function renderRow($row, $columns, $resized)
	{
		$ellipsis = static::ELLIPSIS;
		$el = \mb_strlen($ellipsis);

		$line = static::BORDER_VERTICAL;
		$offset = 0;
		foreach ($columns as $c => $column)
		{
			if ($offset++)
				$line .= static::BORDER_VERTICAL;
			$line .= $this->margin;
			$value = Container::keyValue($row, $c, '');
			$length = \mb_strlen($value);
			if ($resized)
			{
				$max = $column[self::COLUMN_SIZE];
				if ($length > $max)
				{
					$length = $max;
					if ($el && $max > $el)
					{
						$value = \mb_substr($value, 0, $max - $el) .
							$ellipsis;
					}
					else
					{
						$value = \mb_substr($value, 0, $max);
					}
				}
			}

			$padding = \str_repeat(self::PADDING,
				$column[self::COLUMN_SIZE] - $length);
			if ($column[self::COLUMN_NUMERIC])
				$line .= $padding . $value;
			else
				$line .= $value . $padding;
			$line .= $this->margin;
		}

		$line .= static::BORDER_VERTICAL;
		return $line;
	}

	/**
	 * Render horizontal border line
	 *
	 * @param integer $verticalPosition
	 *        	Border position
	 * @param array $columnSizes
	 *        	Column sizezs
	 * @return mixed|string
	 */
	public function renderBorderLine($verticalPosition, $columnSizes)
	{
		$marginSize = \mb_strlen($this->margin) * 2;
		$line = '';
		switch ($verticalPosition)
		{
			case self::VERTICAL_POSITION_TOP:
				$line .= static::BORDER_TOP_LEFT;
			break;
			case self::VERTICAL_POSITION_INTER:
				$line .= static::BORDER_INTER_LEFT;
			break;
			case self::VERTICAL_POSITION_BOTTOM:
				$line .= static::BORDER_BOTTOM_LEFT;
			break;
		}

		$separator = '';
		switch ($verticalPosition)
		{
			case self::VERTICAL_POSITION_TOP:
				$separator = static::BORDER_TOP_INTER;
			break;
			case self::VERTICAL_POSITION_INTER:
				$separator = static::BORDER_INTER;
			break;
			case self::VERTICAL_POSITION_BOTTOM:
				$separator = static::BORDER_BOTTOM_INTER;
			break;
		}

		$line .= Container::implodeValues($columnSizes,
			[
				Container::IMPLODE_BETWEEN => $separator
			],
			function ($size) use ($marginSize) {
				return \str_repeat(static::BORDER_HORIZONTAL,
					$size + $marginSize);
			});

		switch ($verticalPosition)
		{
			case self::VERTICAL_POSITION_TOP:
				$line .= static::BORDER_TOP_RIGHT;
			break;
			case self::VERTICAL_POSITION_INTER:
				$line .= static::BORDER_INTER_RIGHT;
			break;
			case self::VERTICAL_POSITION_BOTTOM:
				$line .= static::BORDER_BOTTOM_RIGHT;
			break;
		}

		return $line;
	}

	public function resizeColumns(&$columns, &$rowHeading)
	{
		$withRowHeading = ($this->heading & self::HEADING_ROW) ==
			self::HEADING_ROW;
		$margin = \mb_strlen($this->margin);
		$uncompressible = 1;
		$uncompressible += \count($columns) * ((2 * $margin) + 1);

		$length = 0;
		foreach ($columns as $column)
			$length += $column[self::COLUMN_SIZE];
		if ($withRowHeading)
		{
			$length += $rowHeading[self::COLUMN_SIZE];
			$uncompressible += ((2 * $margin) + 1);
		}
		$maxLength = \max(0, ($this->maxRowLength - $uncompressible));

		if ($maxLength >= $length)
			return false;

		$scale = $maxLength / $length;

		$length = 0;
		foreach ($columns as &$column)
		{
			$column[self::COLUMN_SIZE] = \max(1,
				\floor($column[self::COLUMN_SIZE] * $scale));
			$length += $column[self::COLUMN_SIZE];
		}
		if ($withRowHeading)
		{
			$rowHeading[self::COLUMN_SIZE] = \max(1,
				\floor($rowHeading[self::COLUMN_SIZE] * $scale));
			$length += $rowHeading[self::COLUMN_SIZE];
		}

		if ($length >= $maxLength)
			return true;
		$sorted = $columns;
		\uasort($sorted,
			function ($a, $b) {
				return $a[self::COLUMN_SIZE] - $b[self::COLUMN_SIZE];
			});
		foreach ($sorted as $k => $o)
		{
			if ($length >= $maxLength)
				break;
			$length++;
			$columns[$k][self::COLUMN_SIZE]++;
		}

		return true;
	}

	private function makeLinesValues($parts)
	{
		$max = 0;
		foreach ($parts as $value)
			$max = \max(\count($value), $max);

		$lines = [];
		for ($i = 0; $i < $max; $i++)
		{
			$line = [];
			foreach ($parts as $name => $_)
			{
				if (\count($parts[$name]))
					$line[$name] = \array_shift($parts[$name]);
				else
					$line[$name] = '';
			}
			$lines[] = $line;
		}
		return $lines;
	}

	/**
	 *
	 * @param array $table
	 *        	Input data
	 * @return array Column infos
	 */
	private function preprocess(&$table)
	{
		$withColumnHeading = ($this->heading & self::HEADING_COLUMN) ==
			self::HEADING_COLUMN;
		$rowHeadingSize = 0;
		$rowHeadingIsNumberic = true;
		$columns = [];

		$primitifier = new Primitifier();

		foreach ($table as $r => &$row)
		{
			if (!Container::isArray($row))
				$row = $primitifier($row);

			$rowHeadingSize = \max($rowHeadingSize,
				\mb_strlen(\strval($r)));
			$rowHeadingIsNumberic = $rowHeadingIsNumberic &&
				\is_numeric($r);
			foreach ($row as $c => &$value)
			{
				if (!\is_string($value))
				{
					if (\is_callable($this->stringifier))
						$value = \call_user_func($this->stringifier,
							$value);
					else
						$value = TypeConversion::toString($value);
					$table[$r][$c] = $value;
				}
				$isNumeric = \is_numeric($value);
				$length = 0;
				$lines = [];
				if ($isNumeric)
				{
					$length = \mb_strlen($value);
					$lines = [
						$value
					];
				}
				else
				{
					$lines = \preg_split('/((\r\n)|\n)/', $value);
					$length = 0;
					foreach ($lines as $line)
						$length = \max($length, \mb_strlen($line));
				}

				if (Container::keyExists($columns, $c))
				{
					$columns[$c][self::COLUMN_NUMERIC] = $columns[$c][self::COLUMN_NUMERIC] &&
						$isNumeric;
					$columns[$c][self::COLUMN_SIZE] = \max($length,
						$columns[$c][self::COLUMN_SIZE]);
				}
				else
				{
					if ($withColumnHeading)
						$length = \max($length, \mb_strlen($c));

					$columns[$c] = [
						self::COLUMN_SIZE => $length,
						self::COLUMN_NUMERIC => $isNumeric
					];
				}

				$value = $lines;
			}
		}

		return [
			$columns,
			[
				self::COLUMN_NUMERIC => $rowHeadingIsNumberic,
				self::COLUMN_SIZE => $rowHeadingSize
			]
		];
	}

	const COLUMN_SIZE = 'size';

	const COLUMN_NUMERIC = 'is-numeric';

	const VERTICAL_POSITION_TOP = 0x01;

	const VERTICAL_POSITION_INTER = 0x02;

	const VERTICAL_POSITION_BOTTOM = 0x04;

	const ROW_HEADING_COLUMN_NAME = '__[[row-heading]]__';

	/**
	 * Cell left & right margin
	 *
	 * @var string
	 */
	private $margin = self::PADDING;

	private $maxRowLength = null;

	/**
	 *
	 * @var callable
	 */
	private $stringifier;
}
