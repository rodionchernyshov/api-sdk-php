<?php
/**
 * @category Qordoba PHP SDK
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba;

use Qordoba\Interfaces\TranslateStringInterface;

/**
 * Class TranslateString
 *
 * @package Qordoba
 */
class TranslateString implements \JsonSerializable, TranslateStringInterface
{
    
    /**
     * @var string|int
     */
    private $key;
    /**
     * @var array
     */
    private $value;
    /**
     * @var string
     */
    private $section;
    
    /**
     * TranslateString constructor.
     *
     * @param string|int $key
     * @param array|string $value
     * @param string $section
     */
    public function __construct($key, $value, $section)
    {
        $this->key = (string)$key;
        $this->value = $value;
        $this->section = $section;
    }
    
    /**
     * @return int|string
     */
    public function getKey()
    {
        return $this->key;
    }
    
    /**
     * @return array
     */
    public function getValue()
    {
        return $this->value;
    }
    
    /**
     * @return string
     */
    public function getSection()
    {
        return $this->section;
    }
    
    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->value;
    }
}
