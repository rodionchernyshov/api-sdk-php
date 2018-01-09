<?php
/**
 * @category Qordoba PHP SDK
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */


/**
 * @param string $string
 *
 * @return bool
 */
function isJson($string) {
  json_decode($string);
    return (json_last_error() === JSON_ERROR_NONE);
}