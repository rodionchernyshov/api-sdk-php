<?php
/**
 * @category Qordoba PHP SDK
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Interfaces;

/**
 * Interface DocumentInterface
 *
 * @package Qordoba\Interfaces
 */
interface DocumentInterface
{
    /**
     * @const string
     */
    const TYPE_JSON = 'json';
    /**
     * @const string
     */
    const TYPE_HTML = 'html';
    /**
     * @const string
     */
    const DEFAULT_TAG_NAME = 'New';
    
    /**
     * @return \Qordoba\Connection
     */
    public function getConnection();
    
    /**
     * @param $key
     * @return mixed
     * @throws \Qordoba\Exception\DocumentException
     */
    public function addSection($key);
    
    /**
     * @return string
     */
    public function getType();
    
    /**
     * @param $type
     */
    public function setType($type);
    
    /**
     * @param string $key
     * @return bool|mixed
     * @throws \Qordoba\Exception\DocumentException
     */
    public function getTranslationString($key);
    
    /**
     * @return array
     * @throws \Qordoba\Exception\DocumentException
     */
    public function getTranslationStrings();
    
    /**
     * @return array
     * @throws \Exception
     */
    public function getMetadata();
    
    /**
     * @throws \RuntimeException
     * @throws \Exception
     * @throws \Qordoba\Exception\AuthException
     * @throws \Qordoba\Exception\ConnException
     * @throws \Qordoba\Exception\ServerException
     */
    public function fetchMetadata();
    
    /**
     * @return array
     * @throws \RuntimeException
     * @throws \Exception
     * @throws \Qordoba\Exception\AuthException
     * @throws \Qordoba\Exception\ConnException
     * @throws \Qordoba\Exception\ServerException
     */
    public function getProjectLanguages();
    
    /**
     * @return int|
     * @throws \RuntimeException
     * @throws \Exception
     * @throws \Qordoba\Exception\AuthException
     * @throws \Qordoba\Exception\ConnException
     * @throws \Qordoba\Exception\DocumentException
     * @throws \Qordoba\Exception\ServerException
     * @throws \Qordoba\Exception\UploadException
     */
    public function createTranslation();
    
    /**
     * @return mixed
     * @throws \Qordoba\Exception\DocumentException
     */
    public function getTranslationContent();
    
    /**
     * @return string
     */
    public function getName();
    
    /**
     * @param string $name
     */
    public function setName($name);
    
    /**
     * @return string
     */
    public function getTag();
    
    /**
     * @param $tag
     */
    public function setTag($tag);
    
    /**
     * @return int
     */
    public function getId();
    
    /**
     * @param int|string $id
     */
    public function setId($id);
    
    /**
     * @return int
     * @throws \RuntimeException
     * @throws \Exception
     * @throws \Qordoba\Exception\AuthException
     * @throws \Qordoba\Exception\ConnException
     * @throws \Qordoba\Exception\DocumentException
     * @throws \Qordoba\Exception\ProjectException
     * @throws \Qordoba\Exception\ServerException
     * @throws \Qordoba\Exception\UploadException
     */
    public function updateTranslation();
    
    /**
     * @return \Qordoba\Project
     */
    public function getProject();
    
    /**
     * @param null|string $languageCode
     * @return array
     * @throws \RuntimeException
     * @throws \Exception
     * @throws \Qordoba\Exception\AuthException
     * @throws \Qordoba\Exception\ConnException
     * @throws \Qordoba\Exception\ProjectException
     * @throws \Qordoba\Exception\ServerException
     */
    public function checkTranslation($languageCode = null);
    
    /**
     * @param null|string $languageCode
     * @return array
     * @throws \RuntimeException
     * @throws \Exception
     * @throws \Qordoba\Exception\AuthException
     * @throws \Qordoba\Exception\ConnException
     * @throws \Qordoba\Exception\ProjectException
     * @throws \Qordoba\Exception\ServerException
     */
    public function fetchTranslation($languageCode = null);
    
    /**
     * @return array
     * @throws \Exception
     * @throws \Qordoba\Exception\AuthException
     * @throws \Qordoba\Exception\ConnException
     * @throws \Qordoba\Exception\ServerException
     */
    public function getProjectLanguageCodes();
    
    /**
     * @param $value
     * @return bool
     * @throws \Qordoba\Exception\DocumentException
     */
    public function addTranslationContent($value);
    
    /**
     * @param $key
     * @param $value
     * @return bool
     * @throws \Qordoba\Exception\DocumentException
     */
    public function addTranslationString($key, $value);
    
    /**
     * @param $value
     * @return bool
     * @throws \Qordoba\Exception\DocumentException
     */
    public function updateTranslationContent($value);
    
    /**
     * @param $key
     * @param $value
     * @return bool
     * @throws \Qordoba\Exception\DocumentException
     */
    public function updateTranslationString($key, $value);
    
    /**
     * @return bool
     * @throws \Qordoba\Exception\DocumentException
     */
    public function removeTranslationContent();
    
    /**
     * @param $searchChunk
     * @return bool
     * @throws \Qordoba\Exception\DocumentException
     */
    public function removeTranslationString($searchChunk);
}
