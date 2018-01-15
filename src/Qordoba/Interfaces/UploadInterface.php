<?php
/**
 * @category Qordoba PHP SDK
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Interfaces;

/**
 * Interface UploadInterface
 *
 * @package Qordoba\Interfaces
 */
interface UploadInterface
{
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
    public function sendFile($documentName, $documentContent, $isNeedUpdate = false, $documentId = null);
    
    /**
     * @return string
     */
    public function getFileName();
    
    /**
     * @param string $fileName
     * @throws \Qordoba\Exception\UploadException
     */
    public function setFileName($fileName);
    
    /**
     * @param string $tagName
     * @return mixed
     * @throws \RuntimeException
     * @throws \Exception
     * @throws \Qordoba\Exception\AuthException
     * @throws \Qordoba\Exception\ConnException
     * @throws \Qordoba\Exception\ServerException
     */
    public function appendToProject($tagName = DocumentInterface::DEFAULT_TAG_NAME);
}
