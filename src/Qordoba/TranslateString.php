<?php
/**
 * @category Qordoba PHP SDK
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba;

/**
 * Class TranslateString
 *
 * @package Qordoba
 */
class TranslateString implements \JsonSerializable
{

	/**
	 * @var
	 */
	private $key;
	/**
	 * @var
	 */
	private $value;
	/**
	 * @var
	 */
	private $section;

	/**
	 * TranslateString constructor.
	 *
	 * @param $key
	 * @param $value
	 * @param $section
	 */
	public function __construct($key, $value, $section)
	{
		$this->key = $key;
		$this->value = $value;
		$this->section = $section;
	}

	/**
	 *
	 */
	public function unlink()
	{
		$this->section->removeTranslationString($this->key);
	}

	/**
	 * @return mixed
	 */
	public function jsonSerialize()
	{
		return $this->value;
	}
}