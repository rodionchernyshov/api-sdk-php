<?php
/**
 * @category Qordoba PHP SDK
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\RequestOptions;
use Qordoba\Exception\AuthException;
use Qordoba\Exception\ConnException;
use Qordoba\Exception\ServerException;

/**
 * Class Connection
 *
 * @package Qordoba
 */
class Connection
{

	/**
	 * @var string
	 */
	private $username;
	/**
	 * @var string
	 */
	private $password;
	/**
	 * @var string
	 */
	private $apiUrl;
	/**
	 * @var string
	 */
	private $apiKey;
	/**
	 * @var array
	 */
	private $metadata;
	/**
	 * @var int
	 */
	private $_requestCount = 0;
	/**
	 * @var
	 */
	private $_requests;

	/**
	 * Connection constructor.
	 *
	 * @param null $apiUrl
	 * @param null $username
	 * @param null $password
	 */
	public function __construct($apiUrl = null, $username = null, $password = null)
	{
		$this->setApiUrl($apiUrl);
		$this->setUsername($username);
		$this->setPassword($password);
	}

	/**
	 * @return array
	 */
	public function getConnectionData()
	{
		return $this->metadata;
	}

	/**
	 * @return int
	 */
	public function getRequestCount()
	{
		return $this->_requestCount;
	}

	/**
	 * @return mixed
	 */
	public function getRequests()
	{
		return $this->_requests;
	}

	/**
	 * @param $fileName
	 * @param $filePath
	 * @param $projectId
	 * @param $fileId
	 * @return mixed
	 * @throws \RuntimeException
	 * @throws AuthException
	 * @throws ConnException
	 * @throws ServerException
	 * @throws \Exception
	 */
	public function requestFileUploadUpdate($fileName, $filePath, $projectId, $fileId)
	{
		$authToken = $this->requestAuthToken();

		$apiUrl = $this->getApiUrl()
			. '/projects/' . $projectId
			. '/files/' . $fileId
			. '/update/upload'
			. '?content_type_code=JSON';

		$options = [
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

		$response = $this->processRequest('POST', $apiUrl, $options);
		$result = json_decode($response->getBody()->getContents());

		if (!$result->id) {
			throw new ConnException('File upload failed');
		}

		return $result->id;
	}

	/**
	 * @return string
	 * @throws \RuntimeException
	 * @throws AuthException
	 * @throws ConnException
	 * @throws ServerException
	 * @throws \Exception
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

		$headers = ['Content-Type' => 'application/json'];

		$requestObj = new \stdClass();
		$requestObj->username = $username;
		$requestObj->password = $password;

		$options = [
			'headers' => $headers,
			'body' => json_encode($requestObj)
		];

		$response = $this->processRequest('PUT', $this->getApiUrl() . '/login', $options);

		if ($response->getStatusCode() !== 200) {
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
	 * @return string
	 */
	private function getApiKey()
	{
		return $this->apiKey;
	}

	/**
	 * @return string
	 */
	public function getUsername()
	{
		return $this->username;
	}

	/**
	 * @param $username
	 */
	public function setUsername($username)
	{
		$this->username = $username;
	}

	/**
	 * @return string
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * @param $password
	 */
	public function setPassword($password)
	{
		$this->password = $password;
	}

	/**
	 * @return string
	 */
	public function getApiUrl()
	{
		return rtrim($this->apiUrl, '/');
	}

	/**
	 * @param $apiUrl
	 */
	public function setApiUrl($apiUrl)
	{
		$this->apiUrl = $apiUrl;
	}

	/**
	 * @param $method
	 * @param $apiUrl
	 * @param $options
	 * @return mixed|\Psr\Http\Message\ResponseInterface
	 * @throws ServerException
	 * @throws \Exception
	 */
	private function processRequest($method, $apiUrl, $options)
	{
		try {
			$httpClient = new GuzzleClient([
				RequestOptions::DELAY => 1
			]);
			$response = $httpClient->request($method, $apiUrl, $options);
		} catch (\Exception $e) {
			$message = $e->getMessage();
			if (preg_match('#\"errMessage\":\"([^\"]{1,})\"#', $message, $match)) {
				throw new ServerException($match[1]);
			}
			throw $e;
		}

		$this->_requestCount++;
		$this->_requests[] = $response;

		return $response;
	}

	/**
	 * @param $data
	 */
	public function setConnectionData($data)
	{
		$this->metadata = $data;
	}

	/**
	 * @param $apiKey
	 */
	public function setApiKey($apiKey)
	{
		$this->apiKey = $apiKey;
	}

	/**
	 * @param $fileName
	 * @param $filePath
	 * @param $projectId
	 * @param $organizationId
	 * @return mixed
	 * @throws AuthException
	 * @throws ConnException
	 * @throws ServerException
	 * @throws \Exception
	 */
	public function requestFileUpload($fileName, $filePath, $projectId, $organizationId)
	{
		$authToken = $this->requestAuthToken();

		$apiUrl = $this->getApiUrl()
			. '/organizations/' . $organizationId
			. '/upload/uploadFile_anyType?project_id=' . $projectId
			. '&content_type_code=JSON';

		$options = [
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

		$response = $this->processRequest('POST', $apiUrl, $options);
		$result = json_decode($response->getBody()->getContents());

		if ($result->result != 'success') {
			throw new ConnException('File upload failed');
		}

		return $result->upload_id;
	}

	/**
	 * @param $fileName
	 * @param $uploadId
	 * @param $tagName
	 * @param $projectId
	 * @return mixed
	 * @throws \RuntimeException
	 * @throws AuthException
	 * @throws ConnException
	 * @throws ServerException
	 * @throws \Exception
	 */
	public function requestAppendToProject($fileName, $uploadId, $tagName, $projectId)
	{
		$authToken = $this->requestAuthToken();

		$apiUrl = $this->getApiUrl()
			. '/projects/' . $projectId
			. '/append_files';

		$options = [
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

		$response = $this->processRequest('POST', $apiUrl, $options);
		$result = json_decode($response->getBody()->getContents());

		return array_shift($result->files_ids);
	}

	/**
	 * @return mixed
	 * @throws AuthException
	 * @throws ConnException
	 * @throws ServerException
	 * @throws \Exception
	 */
	public function fetchLanguages()
	{
		$authToken = $this->requestAuthToken();

		$apiUrl = $this->getApiUrl()
			. '/languages';

		$options = [
			'headers' => [
				'X-AUTH-TOKEN' => $authToken
			]
		];

		$response = $this->processRequest('GET', $apiUrl, $options);
		$result = json_decode($response->getBody()->getContents());

		return $result;
	}

	/**
	 * @param $projectId
	 * @return mixed
	 * @throws AuthException
	 * @throws ConnException
	 * @throws ServerException
	 * @throws \Exception
	 */
	public function fetchProject($projectId)
	{
		$authToken = $this->requestAuthToken();

		$apiUrl = $this->getApiUrl()
			. '/projects/' . $projectId;

		$options = [
			'headers' => [
				'X-AUTH-TOKEN' => $authToken
			]
		];

		$response = $this->processRequest('GET', $apiUrl, $options);
		$result = json_decode($response->getBody()->getContents());

		return $result;
	}

	/**
	 * @param $projectId
	 * @param $langId
	 * @param null $searchName
	 * @param string $searchStatus
	 * @param int $offset
	 * @param int $limit
	 * @return mixed
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
	) {
		$authToken = $this->requestAuthToken();

		$apiUrl = $this->getApiUrl()
			. '/projects/' . $projectId
			. '/languages/' . $langId
			. '/page_settings/search?'
			. 'limit=' . $limit
			. '&offset=' . $offset;

		$searchStruct = new \StdClass();
		if ($searchStatus != 'none') {
			$searchStruct->status = [$searchStatus];
		}
		if ($searchName != null) {
			$searchStruct->title = $searchName;
		}

		$options = [
			'headers' => [
				'X-AUTH-TOKEN' => $authToken
			],
			'json' => $searchStruct
		];

		$response = $this->processRequest('POST', $apiUrl, $options);
		$result = json_decode($response->getBody()->getContents());

		return $result;
	}

	/**
	 * @param $projectId
	 * @param $langId
	 * @param $pageId
	 * @return mixed|string
	 * @throws AuthException
	 * @throws ConnException
	 * @throws ServerException
	 * @throws \Exception
	 */
	public function fetchTranslationFile($projectId, $langId, $pageId)
	{
		$authToken = $this->requestAuthToken();

		$apiUrl = $this->getApiUrl()
			. '/projects/' . $projectId
			. '/languages/' . $langId
			. '/pages/' . $pageId
			. '/segments/milestones/-100/export';

		$options = [
			'headers' => [
				'X-AUTH-TOKEN' => $authToken
			]
		];

		$response = $this->processRequest('GET', $apiUrl, $options);
		$result = json_decode($response->getBody()->getContents());


		$apiUrl = $this->getApiUrl()
			. '/file/download';

		$options = [
			'headers' => [
				'X-AUTH-TOKEN' => $authToken
			],
			'query' => [
				'filename' => $result->filename,
				'token' => $result->token
			]
		];

		$response = $this->processRequest('GET', $apiUrl, $options);
		$result = $response->getBody()->getContents();

		$json = json_decode($result);
		if (json_last_error() != JSON_ERROR_NONE) {
			return $result;
		}
		return $json;
	}
}
