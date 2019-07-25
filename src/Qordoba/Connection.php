<?php
/**
 * @category Qordoba PHP SDK
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba;

use Exception;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Qordoba\Exception\AuthException;
use Qordoba\Exception\ConnException;
use Qordoba\Exception\ServerException;
use Qordoba\Interfaces\ConnectionInterface;
use Qordoba\Interfaces\DocumentInterface;
use RuntimeException;
use StdClass;

/**
 * Class Connection
 * Initiate connection with the API server
 *
 * @package Qordoba
 */
class Connection implements ConnectionInterface
{
    /**
     *
     * Qordoba API base URL
     *
     * @var string
     */
    const DEFAULT_API_URL = 'https://app.qordoba.com/api';
    /**
     *
     * Qordoba Application API user name (email)
     *
     * @var string
     */
    private $username;
    /**
     *
     * Qordoba Application API user password
     *
     * @var string
     */
    private $password;
    /**
     *
     * Qordoba Application REST API url
     *
     * @var string
     */
    private $apiUrl;
    /**
     *
     * Qordoba Application API token
     *
     * @var string
     */
    private $apiKey;
    /**
     *
     * Qordoba Application Workspace additional data
     *
     * @var array
     */
    private $metadata;
    /**
     *
     * Number of request sent to Qordoba Application via REST API per session
     *
     * @var int
     */
    private $requestCount;
    /**
     *
     * Requests to send to Qordoba Application via REST API
     *
     * @var array
     */
    private $requests;

    /**
     * Connection constructor. Initiate default values
     *
     * @param null|string $apiUrl
     * @param null|string $username
     * @param null|string $password
     */
    public function __construct($apiUrl = null, $username = null, $password = null)
    {
        if ($apiUrl) {
            $this->setApiUrl($apiUrl);
        } else {
            $this->setApiUrl(self::DEFAULT_API_URL);
        }
        $this->requestCount = 0;
        $this->setUsername($username);
        $this->setPassword($password);
    }

    /**
     * Get connection additional meta data
     *
     * @return array
     */
    public function getConnectionData()
    {
        return $this->metadata;
    }

    /**
     *
     * Get Qordoba Application API server requests count
     *
     * @return int
     */
    public function getRequestCount()
    {
        return $this->requestCount;
    }

    /**
     *
     * Get Qordoba Application API server requests list including payload
     *
     * @return array
     */
    public function getRequests()
    {
        return $this->requests;
    }

    /**
     *
     * Uploads a file to Qordoba Application via REST API
     *
     * @param string $fileName
     * @param string $filePath
     * @param int|string $projectId
     * @param int|string $fileId
     * @return int
     * @throws AuthException
     * @throws ConnException
     * @throws ServerException
     * @throws GuzzleException
     */
    public function requestFileUploadUpdate($fileName, $filePath, $projectId, $fileId)
    {
        $authToken = $this->requestAuthToken();
        $requestUrl = sprintf(
            '%s/projects/%s/files/%s/update/upload?content_type_code=JSON',
            $this->getApiUrl(),
            $projectId,
            $fileId
        );

        $requestOptions = [
            'multipart' => [
                [
                    'name' => 'user_key',
                    'contents' => $authToken
                ],
                [
                    'name' => 'file_names',
                    'contents' => '[]'
                ],
                [
                    'name' => 'file',
                    'contents' => file_get_contents($filePath),
                    'filename' => $fileName,
                    'headers' => [
                        'Content-Type' => 'application/octet-stream'
                    ]
                ]
            ],
            'headers' => [
                'X-AUTH-TOKEN' => $authToken
            ]
        ];

        $response = $this->processRequest(ConnectionInterface::REQUEST_METHOD_POST, $requestUrl, $requestOptions);
        $result = json_decode($response->getBody()->getContents());

        if (!$result->id) {
            throw new ConnException('File upload failed');
        }

        return $result->id;
    }

    /**
     *
     * Get Qordoba Application REST API access token
     *
     * @return string
     * @throws RuntimeException
     * @throws AuthException
     * @throws ConnException
     * @throws ServerException
     * @throws Exception
     * @throws GuzzleException
     */
    public function requestAuthToken()
    {
        $apiKey = $this->getApiKey();
        $username = $this->getUsername();
        $password = $this->getPassword();
        $apiUrl = $this->getApiUrl();

        if ($apiKey) {
            return $apiKey;
        }

        if (!$username) {
            throw new AuthException('Username not provided', AuthException::USERNAME_NOT_PROVIDED);
        }

        if (!$password) {
            throw new AuthException('Password not provided', AuthException::USERNAME_NOT_PROVIDED);
        }

        if (!$apiUrl) {
            throw new ConnException('API URL not provided', ConnException::URL_NOT_PROVIDED);
        }

        $requestHeaders = ['Content-Type' => 'application/json'];

        $requestObj = new stdClass();
        $requestObj->username = $username;
        $requestObj->password = $password;

        $requestOptions = [
            'headers' => $requestHeaders,
            'body' => json_encode($requestObj)
        ];

        $response = $this->processRequest('PUT', $this->getApiUrl() . '/login', $requestOptions);

        if (200 !== $response->getStatusCode()) {
            throw new ConnException('Non-200 response from API.', ConnException::BAD_RESPONSE);
        }

        $responseRawBody = $response->getBody()->getContents();
        if (!isJson($responseRawBody)) {
            throw new ConnException('Non-JSON response from API.', ConnException::BAD_RESPONSE);
        }

        $responseBody = json_decode($responseRawBody);
        if (!isset($responseBody->token)) {
            throw new ConnException('API token not found in response.', ConnException::BAD_RESPONSE);
        }

        $this->setConnectionData($responseBody);
        $this->setApiKey($responseBody->token);

        return $responseBody->token;
    }

    /**
     *
     * Returns Qordoba REST API access token
     *
     * @return string
     */
    private function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     *
     * Returns Qordoba REST API user name
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     *
     * Sets Qordoba REST API user name
     *
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = trim($username);
    }

    /**
     *
     * Returns Qordoba REST API user password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     *
     * Sets Qordoba REST API user password
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = trim($password);
    }

    /**
     *
     * Returns Qordoba REST API url
     *
     * @return string
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    /**
     *
     * Sets Qordoba REST API url
     *
     * @param string $apiUrl
     */
    public function setApiUrl($apiUrl)
    {
        $this->apiUrl = rtrim(trim($apiUrl), '/');
    }

    /**
     *
     * Send request to Qordoba Application using HTTP protocol
     *
     * @param string $method
     * @param string $apiUrl
     * @param array $options
     * @return mixed|ResponseInterface
     * @throws ServerException
     * @throws Exception
     * @throws GuzzleException
     */
    private function processRequest($method, $apiUrl, $options)
    {
        try {
            $httpClient = new HttpClient([RequestOptions::DELAY => 1]);
            $response = $httpClient->request($method, $apiUrl, $options);
        } catch (Exception $e) {
            $message = $e->getMessage();
            if (preg_match('#\"errMessage\":\"([^\"]{1,})\"#', $message, $match)) {
                throw new ServerException($match[1]);
            }
            throw $e;
        }

        $this->requestCount++;
        $this->requests[] = $response;

        return $response;
    }

    /**
     *
     * Sets Qordoba REST API connection metadata
     *
     * @param array $data
     */
    public function setConnectionData($data)
    {
        $this->metadata = $data;
    }

    /**
     *
     * Sets Qordoba REST API access token
     *
     * @param string $apiKey
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = trim($apiKey);
    }

    /**
     *
     * Sends file to Qordoba Application via REST API.
     * Returns uploaded file ID to use in future requests
     *
     * @param string $fileName
     * @param string $filePath
     * @param string|int $projectId
     * @param string|int $organizationId
     * @return int|string
     * @throws RuntimeException
     * @throws AuthException
     * @throws ConnException
     * @throws ServerException
     * @throws Exception
     * @throws GuzzleException
     */
    public function requestFileUpload($fileName, $filePath, $projectId, $organizationId)
    {
        $authToken = $this->requestAuthToken();

        $requestUrl = sprintf(
            '%s/organizations/%s/upload/uploadFile_anyType?project_id=%s&content_type_code=JSON',
            $this->getApiUrl(),
            $organizationId,
            $projectId
        );

        $requestOptions = [
            'multipart' => [
                [
                    'name' => 'user_key',
                    'contents' => $authToken
                ],
                [
                    'name' => 'file_names',
                    'contents' => '[]'
                ],
                [
                    'name' => 'file',
                    'contents' => file_get_contents($filePath),
                    'filename' => $fileName,
                    'headers' => [
                        'Content-Type' => 'application/octet-stream'
                    ]
                ]
            ],
            'headers' => [
                'X-AUTH-TOKEN' => $authToken
            ]
        ];

        $response = $this->processRequest(ConnectionInterface::REQUEST_METHOD_POST, $requestUrl, $requestOptions);
        $result = json_decode($response->getBody()->getContents());

        if ('success' !== $result->result) {
            throw new ConnException('File upload failed');
        }

        return $result->upload_id;
    }

    /**
     *
     * Adds uploaded file to Workspace on Qorodba Application via REST API
     *
     * @param string $fileName
     * @param string|int $uploadId
     * @param string $tagName
     * @param string|int $projectId
     * @return array
     * @throws RuntimeException
     * @throws AuthException
     * @throws ConnException
     * @throws ServerException
     * @throws Exception
     * @throws GuzzleException
     */
    public function requestAppendToProject($fileName, $uploadId, $tagName, $projectId)
    {
        $authToken = $this->requestAuthToken();
        $requestUrl = sprintf('%s/projects/%s/append_files', $this->getApiUrl(), $projectId);
        $requestOptions = [
            'headers' => [
                'X-AUTH-TOKEN' => $authToken
            ],
            'json' => [
                [
                    'source_columns' => [],
                    'file_name' => $fileName,
                    'version_tag' => $tagName,
                    'id' => $uploadId
                ]
            ]
        ];
        $response = $this->processRequest(ConnectionInterface::REQUEST_METHOD_POST, $requestUrl, $requestOptions);
        $result = json_decode($response->getBody()->getContents());
        return array_shift($result->files_ids);
    }

    /**
     *
     * Update existing  Workspace on Qorodba Application via REST API
     *
     * @param string $fileName
     * @param string|int $uploadId
     * @param string|int $fileId
     * @param string|int $projectId
     * @return array
     * @throws AuthException
     * @throws ConnException
     * @throws ServerException
     * @throws GuzzleException
     */
    public function requestUpdateProject($fileName, $uploadId, $fileId, $projectId)
    {
        $authToken = $this->requestAuthToken();
        $requestUrl = sprintf('%s/projects/%s/files/%s/update/apply', $this->getApiUrl(), $projectId, $fileId);
        $requestOptions = [
            'headers' => [
                'X-AUTH-TOKEN' => $authToken
            ],
            'json' => [
                'keep_in_project' => false,
                'new_file_id' => $uploadId
            ]
        ];
        $response = $this->processRequest(ConnectionInterface::REQUEST_METHOD_PUT, $requestUrl, $requestOptions);
        $result = json_decode($response->getBody()->getContents());
        return array_shift($result->files_ids);
    }

    /**
     *
     * Get Workspace languages list on Qorodba Application via REST API
     *
     * @return array
     * @throws RuntimeException
     * @throws AuthException
     * @throws ConnException
     * @throws ServerException
     * @throws Exception
     * @throws GuzzleException
     */
    public function fetchLanguages()
    {
        $authToken = $this->requestAuthToken();

        $requestUrl = $this->getApiUrl() . '/languages';
        $requestOptions = [
            'headers' => [
                'X-AUTH-TOKEN' => $authToken
            ]
        ];

        $response = $this->processRequest(ConnectionInterface::REQUEST_METHOD_GET, $requestUrl, $requestOptions);
        return json_decode($response->getBody()->getContents());
    }

    /**
     *
     * Gets Workspace information on Qorodba Application via REST API
     *
     * @param int|string $projectId
     * @return mixed
     * @throws RuntimeException
     * @throws AuthException
     * @throws ConnException
     * @throws ServerException
     * @throws Exception
     * @throws GuzzleException
     */
    public function fetchProject($projectId)
    {
        $authToken = $this->requestAuthToken();
        $requestUrl = sprintf('%s/projects/%s', $this->getApiUrl(), $projectId);
        $requestOptions = [
            'headers' => [
                'X-AUTH-TOKEN' => $authToken
            ]
        ];
        $response = $this->processRequest(ConnectionInterface::REQUEST_METHOD_GET, $requestUrl, $requestOptions);
        return json_decode($response->getBody()->getContents());
    }

    /**
     *
     * Search for files in the existing Workspace on Qorodba Application via REST API
     *
     * @param string|int $projectId
     * @param string|int $langId
     * @param null|string $searchName
     * @param string $searchStatus
     * @param int $offset
     * @param int $limit
     * @return array
     * @throws RuntimeException
     * @throws AuthException
     * @throws ConnException
     * @throws ServerException
     * @throws Exception
     * @throws GuzzleException
     */
    public function fetchProjectSearch(
        $projectId,
        $langId,
        $searchName = null,
        $searchStatus = DocumentInterface::STATE_COMPLETED,
        $offset = 0,
        $limit = 50
    )
    {
        $authToken = $this->requestAuthToken();
        $requestUrl = sprintf(
            '%s/projects/%s/languages/%s/page_settings/search?limit=%s&offset=%s',
            $this->getApiUrl(),
            $projectId,
            $langId,
            $limit,
            $offset
        );

        $searchStructure = new StdClass();
        if ($searchStatus !== 'none') {
            $searchStructure->status = [$searchStatus];
        }
        if ($searchName !== null) {
            $searchStructure->title = $searchName;
        }

        $requestOptions = [
            'headers' => [
                'X-AUTH-TOKEN' => $authToken
            ],
            'json' => $searchStructure
        ];

        $response = $this->processRequest(ConnectionInterface::REQUEST_METHOD_POST, $requestUrl, $requestOptions);
        return json_decode($response->getBody()->getContents());
    }

    /**
     *
     * Get translation for an existing completed file on Workspace on Qorodba Application via REST API
     *
     * @param string|int $projectId
     * @param string|int $langId
     * @param string|int $pageId
     * @return mixed|string
     * @throws RuntimeException
     * @throws AuthException
     * @throws ConnException
     * @throws ServerException
     * @throws Exception
     * @throws GuzzleException
     */
    public function fetchTranslationFile($projectId, $langId, $pageId)
    {
        $response = null;
        $authToken = $this->requestAuthToken();
        $requestUrl = sprintf(
            '%s/projects/%s/languages/%s/pages/%s/segments/milestones/-100/export?original_format=false&compress_columns=false',
            $this->getApiUrl(),
            $projectId,
            $langId,
            $pageId
        );

        $requestOptions = [
            'headers' => [
                'X-AUTH-TOKEN' => $authToken
            ]
        ];

        $response = $this->processRequest(ConnectionInterface::REQUEST_METHOD_GET, $requestUrl, $requestOptions);
        $result = json_decode($response->getBody()->getContents());
        $requestUrl = sprintf('%s/file/download', $this->getApiUrl());
        $requestOptions = [
            'headers' => [
                'X-AUTH-TOKEN' => $authToken
            ],
            'query' => [
                'filename' => $result->filename,
                'token' => $result->token
            ]
        ];

        $response = $this->processRequest(ConnectionInterface::REQUEST_METHOD_GET, $requestUrl, $requestOptions);
        $result = $response->getBody()->getContents();

        $jsonResponse = json_decode($result);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $result;
        }
        return $jsonResponse;
    }
}
