<?php
/**
 * @category Qordoba PHP SDK
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Interfaces;

/**
 * Interface TranslateSectionInterface
 *
 * @package Qordoba\Interfaces
 */
interface TranslateSectionInterface
{
    /**
     * @param string $key
     * @param string|array $value
     * @return bool
     * @throws \Qordoba\Exception\DocumentException
     */
    public function addTranslationString($key, $value);
    
    /**
     * @param string $key
     * @param string|array $value
     * @return bool
     * @throws \Qordoba\Exception\DocumentException
     */
    public function updateTranslationString($key, $value);
    
    /**
     * @param string|int $searchChunk
     * @return bool
     */
    public function removeTranslationString($searchChunk);
}
