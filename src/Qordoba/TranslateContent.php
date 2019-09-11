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
use Qordoba\Interfaces\TranslateContentInterface;

/**
 * Class TranslateContent
 *
 * @package Qordoba
 */
class TranslateContent implements JsonSerializable, TranslateContentInterface
{

    /**
     *
     * HTML document content
     *
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
     *
     * Add HTML content to document that will be sent to Qordoba Application via REST API
     *
     * @param string $value
     * @return bool
     * @throws DocumentException
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
     *
     * Update HTML content to document that will be sent to Qordoba Application via REST API
     *
     * @param string $value
     * @return bool
     * @throws DocumentException
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
     * Clear HTML content of a document
     */
    public function removeContent()
    {
        $this->content = '';
    }

    /**
     *
     * Get HTML content to document that will be sent to Qordoba Application via REST API
     *
     * @return bool|string
     */
    public function getContent()
    {
        return ('' === $this->content) ? false : $this->content;
    }

    /**
     *
     * @return mixed|string
     */
    public function jsonSerialize()
    {
        return $this->content;
    }
}
