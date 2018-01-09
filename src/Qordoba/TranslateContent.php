<?php
/**
 * @category Qordoba PHP SDK
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba;

use Qordoba\Exception\DocumentException;

/**
 * Class TranslateContent
 *
 * @package Qordoba
 */
class TranslateContent implements \JsonSerializable
{

	/**
	 * @var string
	 */
	public $_content = '';

	/**
	 * TranslateContent constructor.
	 */
	public function __construct()
	{

	}

	/**
	 * @param $value
	 * @return bool
	 * @throws \Qordoba\Exception\DocumentException
	 */
	public function addContent($value)
	{
		if (!empty($this->_content)) {
			throw new DocumentException(
				'Content already exists. Please use method to edit it.',
				DocumentException::TRANSLATION_STRING_EXISTS
			);
		}

		$this->_content = $value;
		return true;
	}

	/**
	 * @param $value
	 * @return bool
	 * @throws \Qordoba\Exception\DocumentException
	 */
	public function updateContent($value)
	{
		if (empty($value)) {
			throw new DocumentException(
				'Content not exists. Please use method to edit it.',
				DocumentException::TRANSLATION_STRING_NOT_EXISTS
			);
		}
		$this->_content = $value;
		return true;
	}

	/**
	 *
	 */
	public function removeContent()
	{
		$this->_content = '';
	}

	/**
	 * @return bool|string
	 */
	public function getContent()
	{
		if (!empty($this->_content)) {
			return $this->_content;
		}

		return false;
	}

	/**
	 * @return mixed|string
	 */
	public function jsonSerialize()
	{
		return $this->_content;
	}
}