<?php
/**
 * @category Qordoba PHP SDK
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Exception;

/**
 * Class DocumentException
 *
 * @package Qordoba\Exception
 */
class DocumentException extends BaseException
{
	/**
	 * @const int
	 */
	const TRANSLATION_STRING_EXISTS = 1;
	/**
	 * @const int
	 */
	const TRANSLATION_STRING_NOT_EXISTS = 2;
	/**
	 * @const int
	 */
	const TRANSLATION_WRONG_TYPE = 3;
}