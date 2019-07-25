<?php
/**
 * @category Qordoba PHP SDK
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba;

use JsonSerializable;
use Qordoba\Exception\DocumentException;
use Qordoba\Interfaces\TranslateSectionInterface;

/**
 * Class TranslateSection
 *
 * @package Qordoba
 */
class TranslateSection implements JsonSerializable, TranslateSectionInterface
{
    /**
     *
     * JSON document element key
     *
     * @var string
     */
    public $key;
    /**
     *
     * JSON document element values
     *
     * @var array
     */
    public $strings;

    /**
     * TranslateSection constructor.
     *
     * @param $key
     */
    public function __construct($key)
    {
        $this->key = $key;
        $this->strings = [];
    }

    /**
     *
     * Creates new section in the JSON document
     *
     * @param string $key
     * @param string|array $value
     * @return bool
     * @throws DocumentException
     */
    public function addTranslationString($key, $value)
    {
        if (isset($this->strings[$key])) {
            throw new DocumentException(
                'String already exists. Please use method to edit it.',
                DocumentException::TRANSLATION_STRING_EXISTS
            );
        }
        $this->strings[$key] = new TranslateString($key, $value, $this);
        return true;
    }

    /**
     *
     * Update an existing section in the JSON document
     *
     * @param string $key
     * @param string|array $value
     * @return bool
     * @throws DocumentException
     */
    public function updateTranslationString($key, $value)
    {
        if (!isset($this->strings[$key])) {
            throw new DocumentException(
                'String not exists. Please use method to edit it.',
                DocumentException::TRANSLATION_STRING_NOT_EXISTS
            );
        }

        $this->strings[$key] = new TranslateString($key, $value, $this);
        return true;
    }

    /**
     *
     * Remove an existing section in the JSON document
     *
     * @param string|int $searchChunk
     * @return bool
     */
    public function removeTranslationString($searchChunk)
    {
        if (isset($this->strings[$searchChunk])) {
            return $this->removeTranslationStringByKey($searchChunk);
        }
        return $this->removeTranslationStringByValue($searchChunk);
    }

    /**
     *
     * Remove an existing section in the JSON document by section key
     *
     * @param $searchChunk
     * @return bool
     */
    private function removeTranslationStringByKey($searchChunk)
    {
        $isRemoved = false;
        if (isset($this->strings[$searchChunk])) {
            unset($this->strings[$searchChunk]);
            $isRemoved = true;
        }
        return $isRemoved;
    }

    /**
     *
     * Remove an existing section in the JSON document by section value
     *
     * @param string $searchChunk
     * @return bool
     */
    private function removeTranslationStringByValue($searchChunk)
    {
        $result = false;
        foreach ($this->strings as $key => $val) {
            if ($searchChunk === $val) {
                unset($this->strings[$key]);
                $result = true;
                break;
            }
        }
        return $result;
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return $this->strings;
    }
}
