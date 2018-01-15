<?php
/**
 * @category Qordoba PHP SDK
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Interfaces;

/**
 * Interface TranslateContentInterface
 *
 * @package Qordoba\Interfaces
 */
interface TranslateContentInterface
{
    /**
     * @param string $value
     * @return bool
     * @throws \Qordoba\Exception\DocumentException
     */
    public function addContent($value);
    
    /**
     * @param string $value
     * @return bool
     * @throws \Qordoba\Exception\DocumentException
     */
    public function updateContent($value);
    
    /**
     *
     */
    public function removeContent();
    
    /**
     * @return bool|string
     */
    public function getContent();
    
}
