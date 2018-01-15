<?php
/**
 * @category Qordoba PHP SDK
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba;

use Qordoba\Exception\DocumentException;
use Qordoba\Interfaces\TranslateContentInterface;

/**
 * Class TranslateContent
 *
 * @package Qordoba
 */
class TranslateContent implements \JsonSerializable, TranslateContentInterface
{
    
    /**
     * @var string
     */
    public $content;
    
    /**
     * TranslateContent constructor.
     */
    public function __construct()
    {
        $this->content = '';
    }
    
    /**
     * @param string $value
     * @return bool
     * @throws \Qordoba\Exception\DocumentException
     */
    public function addContent($value)
    {
        if ('' !== $this->content) {
            throw new DocumentException(
                'Content already exists. Please use method to edit it.',
                DocumentException::TRANSLATION_STRING_EXISTS
            );
        }
        
        $this->content = trim($value);
        return true;
    }
    
    /**
     * @param string $value
     * @return bool
     * @throws \Qordoba\Exception\DocumentException
     */
    public function updateContent($value)
    {
        if ('' === $value) {
            throw new DocumentException(
                'Content not exists. Please use method to edit it.',
                DocumentException::TRANSLATION_STRING_NOT_EXISTS
            );
        }
        $this->content = $value;
        return true;
    }
    
    /**
     *
     */
    public function removeContent()
    {
        $this->content = '';
    }
    
    /**
     * @return bool|string
     */
    public function getContent()
    {
        return ('' === $this->content) ? false : $this->content;
    }
    
    /**
     * @return mixed|string
     */
    public function jsonSerialize()
    {
        return $this->content;
    }
}
