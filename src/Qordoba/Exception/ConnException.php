<?php
/**
 * @category Qordoba PHP SDK
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Exception;

/**
 * Class ConnException
 *
 * @package Qordoba\Exception
 */
class ConnException extends BaseException
{
	/**
	 * @const int
	 */
	const URL_NOT_PROVIDED = 1;
	/**
	 * @const int
	 */
	const BAD_RESPONSE = 2;
}