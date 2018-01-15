<?php
/**
 * @category Qordoba PHP SDK
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Interfaces;

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
     * @return null|\Qordoba\Upload
     */
    public function getUpload();
    
    /**
     * @param string $documentName
     * @param string $documentContent
     * @param null|string $documentTag
     * @param string $type
     * @return mixed
     * @throws \RuntimeException
     * @throws \Exception
     * @throws \Qordoba\Exception\AuthException
     * @throws \Qordoba\Exception\ConnException
     * @throws \Qordoba\Exception\DocumentException
     * @throws \Qordoba\Exception\ServerException
     * @throws \Qordoba\Exception\UploadException
     */
    public function upload($documentName, $documentContent, $documentTag = null, $type = DocumentInterface::TYPE_JSON);
    
    /**
     * @return \stdClass
     * @throws \RuntimeException
     * @throws \Exception
     * @throws \Qordoba\Exception\AuthException
     * @throws \Qordoba\Exception\ConnException
     * @throws \Qordoba\Exception\ServerException
     */
    public function fetchMetadata();
    
    /**
     * @return \stdClass
     * @throws \RuntimeException
     * @throws \Exception
     * @throws \Qordoba\Exception\AuthException
     * @throws \Qordoba\Exception\ConnException
     * @throws \Qordoba\Exception\ServerException
     */
    public function getMetadata();
    
    
    /**
     * @param string $documentName
     * @param string $documentContent
     * @param null|string $documentTag
     * @param null $fileId
     * @param string $type
     * @return mixed
     * @throws \RuntimeException
     * @throws \Exception
     * @throws \Qordoba\Exception\AuthException
     * @throws \Qordoba\Exception\ConnException
     * @throws \Qordoba\Exception\DocumentException
     * @throws \Qordoba\Exception\ServerException
     * @throws \Qordoba\Exception\UploadException
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
     * @throws \RuntimeException
     * @throws \Exception
     * @throws \Qordoba\Exception\AuthException
     * @throws \Qordoba\Exception\ConnException
     * @throws \Qordoba\Exception\ProjectException
     * @throws \Qordoba\Exception\ServerException
     */
    public function fetch($documentName, $documentLanguageCode = null, $documentTag = null, $documentType = 'json');
    
    /**
     * @param $documentName
     * @param null $documentLanguageCode
     * @param null $documentTag
     * @param string $status
     * @param string $type
     * @return array
     * @throws \RuntimeException
     * @throws \Exception
     * @throws \Qordoba\Exception\AuthException
     * @throws \Qordoba\Exception\ConnException
     * @throws \Qordoba\Exception\ProjectException
     * @throws \Qordoba\Exception\ServerException
     */
    public function check(
        $documentName,
        $documentLanguageCode = null,
        $documentTag = null,
        $status = 'completed',
        $type = 'json'
    );
}
