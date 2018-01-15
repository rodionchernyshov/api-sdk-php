<?php
/**
 * @category Qordoba PHP SDK
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Interfaces;

/**
 * Class TranslateStringInterface
 *
 * @package Qordoba\Interfaces
 */
interface TranslateStringInterface
{
    /**
     * @return int|string
     */
    public function getKey();
    
    /**
     * @return array
     */
    public function getValue();
    
    /**
     * @return string
     */
    public function getSection();
}
