<?php
/**
 * @category Qordoba PHP SDK
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba;

use Exception;
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
     * @var string
     */
    private $fileName;
    /**
     * @var int
     */
    private $projectId;
    /**
     * @var string
     */
    private $uploadId;
    /**
     * @var int
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
     * @param $documentName
     * @param $documentContent
     * @param bool $isNeedUpdate
     * @param null|int|string $documentId
     * @return mixed
     * @throws \RuntimeException
     * @throws \Exception
     * @throws \Qordoba\Exception\AuthException
     * @throws \Qordoba\Exception\ConnException
     * @throws \Qordoba\Exception\ServerException
     * @throws \Qordoba\Exception\UploadException
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
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }
    
    /**
     * @param string $fileName
     * @throws \Qordoba\Exception\UploadException
     */
    public function setFileName($fileName)
    {
        if (!Validator::alnum('-._')->validate($fileName)) {
            throw new UploadException('Upload file name not valid.', UploadException::WRONG_FILENAME);
        }
        
        $this->fileName = trim($fileName);
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
    public function appendToProject($tagName = DocumentInterface::DEFAULT_TAG_NAME)
    {
        return $this->connection->requestAppendToProject($this->fileName, $this->uploadId, $tagName, $this->projectId);
    }

    /**
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
