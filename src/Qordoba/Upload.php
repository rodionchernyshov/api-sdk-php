<?php
/**
 * @category Qordoba PHP SDK
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Qordoba\Exception\AuthException;
use Qordoba\Exception\ConnException;
use Qordoba\Exception\ServerException;
use Qordoba\Exception\UploadException;
use Qordoba\Interfaces\DocumentInterface;
use Qordoba\Interfaces\UploadInterface;
use Respect\Validation\Validator;
use RuntimeException;

/**
 * Class Upload
 *
 * @package Qordoba
 */
class Upload implements UploadInterface
{
    /**
     *
     * Name of a file to upload on Qordoba Application
     *
     * @var string
     */
    private $fileName;
    /**
     *
     * Workspace ID on Qordoba Application
     *
     * @var int
     */
    private $projectId;
    /**
     * @var string
     */
    private $uploadId;
    /**
     *
     * Organization ID on Qordoba Application
     *
     * @var int
     */
    private $organizationId;
    /**
     *
     * Active connection to Qorodba Application API
     *
     * @var Connection
     */
    private $connection;

    /**
     *
     * Upload constructor.
     *
     * @param Connection $connection
     * @param int|string $projectId
     * @param int|string $organizationId
     */
    public function __construct(Connection $connection, $projectId, $organizationId)
    {
        $this->connection = $connection;
        $this->projectId = (int)$projectId;
        $this->organizationId = (int)$organizationId;
    }

    /**
     *
     * Sends file to Qorodba Application via REST API
     *
     * @param string $documentName
     * @param string $documentContent
     * @param bool $isNeedUpdate
     * @param null|int|string $documentId
     * @return mixed
     * @throws RuntimeException
     * @throws Exception
     * @throws AuthException
     * @throws ConnException
     * @throws ServerException
     * @throws UploadException
     * @throws GuzzleException
     */
    public function sendFile($documentName, $documentContent, $isNeedUpdate = false, $documentId = null)
    {
        $this->setFileName($documentName);
        $tmpFile = tempnam(sys_get_temp_dir(), $documentName);
        if ($tmpFile) {
            file_put_contents($tmpFile, $documentContent);
            if ($isNeedUpdate && $documentId) {
                $this->uploadId = $this->connection->requestFileUploadUpdate(
                    $this->getFileName(),
                    $tmpFile,
                    $this->projectId,
                    $documentId
                );
            } else {
                $this->uploadId = $this->connection->requestFileUpload(
                    $this->getFileName(),
                    $tmpFile,
                    $this->projectId,
                    $this->organizationId
                );
            }
        }
        return $this->uploadId;
    }

    /**
     *
     * Get file name that will be send to Qordoba Application via REST API
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     *
     * Set file name that will be send to Qordoba Application via REST API
     *
     * @param string $fileName
     * @throws UploadException
     */
    public function setFileName($fileName)
    {
        if (!Validator::alnum('-._')->validate($fileName)) {
            throw new UploadException('Upload file name not valid.', UploadException::WRONG_FILENAME);
        }
        $this->fileName = trim($fileName);
    }

    /**
     *
     * Add file to existing workspace on Qordoba via REST API
     *
     * @param string $tagName
     * @return mixed
     * @throws RuntimeException
     * @throws Exception
     * @throws AuthException
     * @throws ConnException
     * @throws ServerException
     */
    public function appendToProject($tagName = DocumentInterface::DEFAULT_TAG_NAME)
    {
        return $this->connection->requestAppendToProject($this->fileName, $this->uploadId, $tagName, $this->projectId);
    }

    /**
     *
     * Update existing workspace on Qordoba via REST API
     *
     * @param $documentId
     * @param $uploadFileId
     * @return mixed
     * @throws AuthException
     * @throws ConnException
     * @throws ServerException
     */
    public function updateProject($documentId, $uploadFileId)
    {
        return $this->connection->requestUpdateProject($uploadFileId, $this->uploadId, $documentId, $this->projectId);
    }
}
