<?php
/**
 * @category Qordoba PHP SDK
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Interfaces;

use Exception;
use Qordoba\Exception\AuthException;
use Qordoba\Exception\ConnException;
use Qordoba\Exception\DocumentException;
use Qordoba\Exception\ProjectException;
use Qordoba\Exception\ServerException;
use Qordoba\Exception\UploadException;
use Qordoba\Upload;
use RuntimeException;
use stdClass;

/**
 * Interface ProjectInterface
 *
 * @package Qordoba\Interfaces
 */
interface ProjectInterface
{
    /**
     * @return int
     */
    public function getProjectId();

    /**
     * @param int|string $projectId
     */
    public function setProjectId($projectId);

    /**
     * @return int
     */
    public function getOrganizationId();

    /**
     * @param int|string $organizationId
     */
    public function setOrganizationId($organizationId);

    /**
     * @return null|Upload
     */
    public function getUpload();

    /**
     * @param string $documentName
     * @param string $documentContent
     * @param null|string $documentTag
     * @param string $type
     * @return mixed
     * @throws RuntimeException
     * @throws Exception
     * @throws AuthException
     * @throws ConnException
     * @throws DocumentException
     * @throws ServerException
     * @throws UploadException
     */
    public function upload($documentName, $documentContent, $documentTag = null, $type = DocumentInterface::TYPE_JSON);

    /**
     * @return stdClass
     * @throws RuntimeException
     * @throws Exception
     * @throws AuthException
     * @throws ConnException
     * @throws ServerException
     */
    public function fetchMetadata();

    /**
     * @return stdClass
     * @throws RuntimeException
     * @throws Exception
     * @throws AuthException
     * @throws ConnException
     * @throws ServerException
     */
    public function getMetadata();


    /**
     * @param string $documentName
     * @param string $documentContent
     * @param null|string $documentTag
     * @param null $fileId
     * @param string $type
     * @return mixed
     * @throws RuntimeException
     * @throws Exception
     * @throws AuthException
     * @throws ConnException
     * @throws DocumentException
     * @throws ServerException
     * @throws UploadException
     */
    public function update(
        $documentName,
        $documentContent,
        $documentTag = null,
        $fileId = null,
        $type = DocumentInterface::TYPE_JSON
    );

    /**
     * @param string $documentName
     * @param string|null $documentLanguageCode
     * @param string|null $documentTag
     * @param string $documentType
     * @return array
     * @throws RuntimeException
     * @throws Exception
     * @throws AuthException
     * @throws ConnException
     * @throws ProjectException
     * @throws ServerException
     */
    public function fetch($documentName, $documentLanguageCode = null, $documentTag = null, $documentType = DocumentInterface::TYPE_JSON);

    /**
     * @param $documentName
     * @param null $documentLanguageCode
     * @param null $documentTag
     * @param string $status
     * @param string $type
     * @return array
     * @throws RuntimeException
     * @throws Exception
     * @throws AuthException
     * @throws ConnException
     * @throws ProjectException
     * @throws ServerException
     */
    public function check(
        $documentName,
        $documentLanguageCode = null,
        $documentTag = null,
        $status = DocumentInterface::STATE_COMPLETED,
        $type = DocumentInterface::TYPE_JSON
    );
}
