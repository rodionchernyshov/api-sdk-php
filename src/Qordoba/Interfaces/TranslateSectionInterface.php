<?php
/**
 * @category Qordoba PHP SDK
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Interfaces;

use Qordoba\Exception\DocumentException;

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
     * @throws DocumentException
     */
    public function addTranslationString($key, $value);

    /**
     * @param string $key
     * @param string|array $value
     * @return bool
     * @throws DocumentException
     */
    public function updateTranslationString($key, $value);

    /**
     * @param string|int $searchChunk
     * @return bool
     */
    public function removeTranslationString($searchChunk);
}
