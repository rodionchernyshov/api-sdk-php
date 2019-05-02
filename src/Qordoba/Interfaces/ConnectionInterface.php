<?php
/**
 * @category Qordoba PHP SDK
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Interfaces;

use GuzzleHttp\Exception\ServerException;
use Qordoba\Exception\AuthException;
use Qordoba\Exception\ConnException;

/**
 * Class ConnectionInterface
 *
 * @package Qordoba\Interfaces
 */
interface ConnectionInterface
{
    /**
     * @const string
     */
    const REQUEST_METHOD_POST = 'POST';
    /**
     * @const string
     */
    const REQUEST_METHOD_PUT = 'PUT';
    /**
     * @const string
     */
    const REQUEST_METHOD_GET = 'GET';
    
    /**
     * @return array
     */
    public function getConnectionData();
    
    /**
     * @return int
     */
    public function getRequestCount();
    
    /**
     * @return array
     */
    public function getRequests();
    
    /**
     * @param string $fileName
     * @param string $filePath
     * @param int|string $projectId
     * @param int|string $fileId
     * @return int
     * @throws \RuntimeException
     * @throws AuthException
     * @throws ConnException
     * @throws ServerException
     * @throws \Exception
     */
    public function requestFileUploadUpdate($fileName, $filePath, $projectId, $fileId);
    
    /**
     * @return string
     * @throws \RuntimeException
     * @throws AuthException
     * @throws ConnException
     * @throws ServerException
     * @throws \Exception
     */
    public function requestAuthToken();
    
    /**
     * @return string
     */
    public function getUsername();
    
    /**
     * @param string $username
     */
    public function setUsername($username);
    
    /**
     * @return string
     */
    public function getPassword();
    
    /**
     * @param string $password
     */
    public function setPassword($password);
    
    /**
     * @return string
     */
    public function getApiUrl();
    
    /**
     * @param string $apiUrl
     */
    public function setApiUrl($apiUrl);
    
    /**
     * @param array $data
     */
    public function setConnectionData($data);
    
    /**
     * @param string $apiKey
     */
    public function setApiKey($apiKey);
    
    /**
     * @param string $fileName
     * @param string $filePath
     * @param string|int $projectId
     * @param string|int $organizationId
     * @return int|string
     * @throws \RuntimeException
     * @throws AuthException
     * @throws ConnException
     * @throws ServerException
     * @throws \Exception
     */
    public function requestFileUpload($fileName, $filePath, $projectId, $organizationId);
    
    /**
     * @param string $fileName
     * @param string|int $uploadId
     * @param string $tagName
     * @param string|int $projectId
     * @return array
     * @throws \RuntimeException
     * @throws AuthException
     * @throws ConnException
     * @throws ServerException
     * @throws \Exception
     */
    public function requestAppendToProject($fileName, $uploadId, $tagName, $projectId);
    
    /**
     * @return array
     * @throws \RuntimeException
     * @throws AuthException
     * @throws ConnException
     * @throws ServerException
     * @throws \Exception
     */
    public function fetchLanguages();
    
    /**
     * @param int|string $projectId
     * @return mixed
     * @throws \RuntimeException
     * @throws AuthException
     * @throws ConnException
     * @throws ServerException
     * @throws \Exception
     */
    public function fetchProject($projectId);
    
    /**
     * @param string|int $projectId
     * @param string|int $langId
     * @param null|string $searchName
     * @param string $searchStatus
     * @param int $offset
     * @param int $limit
     * @return array
     * @throws \RuntimeException
     * @throws AuthException
     * @throws ConnException
     * @throws ServerException
     * @throws \Exception
     */
    public function fetchProjectSearch(
        $projectId,
        $langId,
        $searchName = null,
        $searchStatus = 'completed',
        $offset = 0,
        $limit = 50
    );
    
    /**
     * @param string|int $projectId
     * @param string|int $langId
     * @param string|int $pageId
     * @return mixed|string
     * @throws \RuntimeException
     * @throws AuthException
     * @throws ConnException
     * @throws ServerException
     * @throws \Exception
     */
    public function fetchTranslationFile($projectId, $langId, $pageId);
}
