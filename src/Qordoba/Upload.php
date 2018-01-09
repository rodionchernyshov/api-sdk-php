<?php
/**
 * @category Qordoba PHP SDK
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba;

use Qordoba\Exception\UploadException;
use Respect\Validation\Validator as v;

/**
 * Class Upload
 *
 * @package Qordoba
 */
class Upload
{

	/**
	 * @var string
	 */
	private $fileName;
	/**
	 * @var string
	 */
	private $projectId;
	/**
	 * @var string
	 */
	private $uploadId;
	/**
	 * @var string
	 */
	private $organizationId;
	/**
	 * @var \Qordoba\Connection
	 */
	private $connection;

	/**
	 * Upload constructor.
	 *
	 * @param \Qordoba\Connection $connection
	 * @param $projectId
	 * @param $organizationId
	 */
	public function __construct(Connection $connection, $projectId, $organizationId)
	{
		$this->connection = $connection;
		$this->projectId = $projectId;
		$this->organizationId = $organizationId;
	}

	/**
	 * @param $fileName
	 * @param $tag
	 * @deprecated
	 */
	public function searchTranslationFile($fileName, $tag)
	{
		$this->connection->fetchFilenameSearch($fileName, $tag);
	}

	/**
	 * @param $fileName
	 * @param $content
	 * @param bool $update
	 * @param int $fileId
	 * @param null $tag
	 * @return mixed
	 * @throws \RuntimeException
	 * @throws \Exception
	 * @throws \Qordoba\Exception\AuthException
	 * @throws \Qordoba\Exception\ConnException
	 * @throws \Qordoba\Exception\ServerException
	 * @throws \Qordoba\Exception\UploadException
	 */
	public function sendFile($fileName, $content, $update = false, $fileId = 0, $tag = null)
	{
		$this->setFileName($fileName);

		$tmpFile = tempnam(sys_get_temp_dir(), $fileName);
		file_put_contents($tmpFile, $content);

		if ($update) {
			$uploadId = $this->connection->requestFileUploadUpdate(
				$this->getFileName(),
				$tmpFile,
				$this->projectId,
				$fileId
			);
			$this->uploadId = $uploadId;
			return $this->uploadId;
		}

		$uploadId = $this->connection->requestFileUpload($this->getFileName(), $tmpFile, $this->projectId,
			$this->organizationId);
		$this->uploadId = $uploadId;
		return $this->uploadId;
	}

	/**
	 * @return mixed
	 */
	public function getFileName()
	{
		return $this->fileName;
	}

	/**
	 * @param $fileName
	 * @throws \Qordoba\Exception\UploadException
	 */
	public function setFileName($fileName)
	{
		if (!v::alnum('-.')->validate($fileName)) {
			throw new UploadException('Upload file name not valid.', UploadException::WRONG_FILENAME);
		}

		$this->fileName = $fileName;
	}

	/**
	 * @param string $tagName
	 * @return mixed
	 * @throws \RuntimeException
	 * @throws \Exception
	 * @throws \Qordoba\Exception\AuthException
	 * @throws \Qordoba\Exception\ConnException
	 * @throws \Qordoba\Exception\ServerException
	 */
	public function appendToProject($tagName = 'New')
	{
		return $this->connection->requestAppendToProject($this->fileName, $this->uploadId, $tagName, $this->projectId);
	}
}