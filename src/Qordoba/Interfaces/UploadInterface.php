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
use Qordoba\Exception\ServerException;
use Qordoba\Exception\UploadException;
use RuntimeException;

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
     * @throws RuntimeException
     * @throws Exception
     * @throws AuthException
     * @throws ConnException
     * @throws ServerException
     * @throws UploadException
     */
    public function sendFile($documentName, $documentContent, $isNeedUpdate = false, $documentId = null);

    /**
     * @return string
     */
    public function getFileName();

    /**
     * @param string $fileName
     * @throws UploadException
     */
    public function setFileName($fileName);

    /**
     * @param string $tagName
     * @return mixed
     * @throws RuntimeException
     * @throws Exception
     * @throws AuthException
     * @throws ConnException
     * @throws ServerException
     */
    public function appendToProject($tagName = DocumentInterface::DEFAULT_TAG_NAME);
}
