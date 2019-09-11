<?php
/**
 * @category Qordoba PHP SDK
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Interfaces;

use Exception;
use Qordoba\Connection;
use Qordoba\Exception\AuthException;
use Qordoba\Exception\ConnException;
use Qordoba\Exception\DocumentException;
use Qordoba\Exception\ProjectException;
use Qordoba\Exception\ServerException;
use Qordoba\Exception\UploadException;
use Qordoba\Project;
use RuntimeException;

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
     * @const string
     */
    const STATE_COMPLETED = 'completed';
    /**
     * @const string
     */
    const STATE_ENABLED = 'enabled';

    /**
     * @return Connection
     */
    public function getConnection();

    /**
     * @param $key
     * @return mixed
     * @throws DocumentException
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
     * @throws DocumentException
     */
    public function getTranslationString($key);

    /**
     * @return array
     * @throws DocumentException
     */
    public function getTranslationStrings();

    /**
     * @return array
     * @throws Exception
     */
    public function getMetadata();

    /**
     * @throws RuntimeException
     * @throws Exception
     * @throws AuthException
     * @throws ConnException
     * @throws ServerException
     */
    public function fetchMetadata();

    /**
     * @return array
     * @throws RuntimeException
     * @throws Exception
     * @throws AuthException
     * @throws ConnException
     * @throws ServerException
     */
    public function getProjectLanguages();

    /**
     * @return int|
     * @throws RuntimeException
     * @throws Exception
     * @throws AuthException
     * @throws ConnException
     * @throws DocumentException
     * @throws ServerException
     * @throws UploadException
     */
    public function createTranslation();

    /**
     * @return mixed
     * @throws DocumentException
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
     * @throws RuntimeException
     * @throws Exception
     * @throws AuthException
     * @throws ConnException
     * @throws DocumentException
     * @throws ProjectException
     * @throws ServerException
     * @throws UploadException
     */
    public function updateTranslation();

    /**
     * @return Project
     */
    public function getProject();

    /**
     * @param null|string $languageCode
     * @return array
     * @throws RuntimeException
     * @throws Exception
     * @throws AuthException
     * @throws ConnException
     * @throws ProjectException
     * @throws ServerException
     */
    public function checkTranslation($languageCode = null);

    /**
     * @param null|string $languageCode
     * @return array
     * @throws RuntimeException
     * @throws Exception
     * @throws AuthException
     * @throws ConnException
     * @throws ProjectException
     * @throws ServerException
     */
    public function fetchTranslation($languageCode = null);

    /**
     * @return array
     * @throws Exception
     * @throws AuthException
     * @throws ConnException
     * @throws ServerException
     */
    public function getProjectLanguageCodes();

    /**
     * @param $value
     * @return bool
     * @throws DocumentException
     */
    public function addTranslationContent($value);

    /**
     * @param $key
     * @param $value
     * @return bool
     * @throws DocumentException
     */
    public function addTranslationString($key, $value);

    /**
     * @param $value
     * @return bool
     * @throws DocumentException
     */
    public function updateTranslationContent($value);

    /**
     * @param $key
     * @param $value
     * @return bool
     * @throws DocumentException
     */
    public function updateTranslationString($key, $value);

    /**
     * @return bool
     * @throws DocumentException
     */
    public function removeTranslationContent();

    /**
     * @param $searchChunk
     * @return bool
     * @throws DocumentException
     */
    public function removeTranslationString($searchChunk);
}
