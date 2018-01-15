<?php
/**
 * @category Qordoba PHP SDK
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Exception;

/**
 * Class AuthException
 *
 * @package Qordoba\Exception
 */
class AuthException extends BaseException
{
    /**
     * @const int
     */
    const USERNAME_NOT_PROVIDED = 1;
    /**
     * @const int
     */
    const PASSWORD_NOT_PROVIDED = 2;
}
