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
use Qordoba\Exception\DocumentException;
use Qordoba\Exception\ProjectException;
use Qordoba\Exception\ServerException;
use Qordoba\Exception\UploadException;
use Qordoba\Interfaces\ConnectionInterface;
use Qordoba\Interfaces\DocumentInterface;
use Qordoba\Interfaces\ProjectInterface;
use RuntimeException;
use stdClass;

/**
 * Class Project
 *
 * @package Qordoba
 */
class Project implements ProjectInterface
{
    /**
     *
     * Qordoba Application Workspace ID
     *
     * @var int|string
     */
    private $projectId;
    /**
     *
     * Qordoba Application Organization ID
     *
     * @var int|string
     */
    private $organizationId;
    /**
     *
     * Qordoba Application active connection object
     *
     * @var Connection
     */
    private $connection;
    /**
     *
     *  Qordoba Application Workspace additional information
     *
     * @var
     */
    private $metadata;

    /**
     * @var null|Upload
     */
    private $upload;

    /**
     * Project constructor.
     *
     * @param $projectId
     * @param $organizationId
     * @param ConnectionInterface $connection
     */
    public function __construct($projectId, $organizationId, ConnectionInterface $connection)
    {
        $this->setProjectId($projectId);
        $this->setOrganizationId($organizationId);
        $this->connection = $connection;
        $this->upload = new Upload($this->connection, $this->getProjectId(), $this->getOrganizationId());
    }

    /**
     *
     * Get Workspace ID on Qordoba Application
     *
     * @return int
     */
    public function getProjectId()
    {
        return $this->projectId;
    }

    /**
     *
     * Set Workspace ID on Qordoba Application
     *
     * @param int|string $projectId
     */
    public function setProjectId($projectId)
    {
        $this->projectId = (int)$projectId;
    }

    /**
     *
     * Get Organization ID on Qordoba Application
     *
     * @return int
     */
    public function getOrganizationId()
    {
        return $this->organizationId;
    }

    /**
     *
     * Set Organization ID on Qordoba Application
     *
     * @param int|string $organizationId
     */
    public function setOrganizationId($organizationId)
    {
        $this->organizationId = (int)$organizationId;
    }

    /**
     * @return null|Upload
     */
    public function getUpload()
    {
        return $this->upload;
    }

    /**
     *
     * Uploads document (JSON or HTML) on Qordoba Application via REST API
     *
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
    public function upload($documentName, $documentContent, $documentTag = null, $type = DocumentInterface::TYPE_JSON)
    {
        $this->fetchMetadata();
        $this->checkProjectType($type);
        $this->upload->sendFile(sprintf('%s.%s', $documentName, $type), $documentContent);
        return $this->upload->appendToProject($documentTag);
    }

    /**
     *
     * Get remote Qordoba Application Workspace additional information
     *
     * @return stdClass
     * @throws RuntimeException
     * @throws Exception
     * @throws AuthException
     * @throws ConnException
     * @throws ServerException
     */
    public function fetchMetadata()
    {
        $this->metadata = $this->connection->fetchProject($this->getProjectId());
        $metaLanguages = [];
        $targetLanguages = $this->metadata->project->target_languages;
        if (is_array($targetLanguages)) {
            foreach ($targetLanguages as $key => $lang) {
                $metaLanguages[$lang->id] = $lang;
            }
        }
        $this->metadata->project->target_languages = $metaLanguages;
        return $this->getMetadata();
    }

    /**
     *
     * Get remote/local Qordoba Application Workspace additional information
     *
     * @return stdClass
     * @throws RuntimeException
     * @throws Exception
     * @throws AuthException
     * @throws ConnException
     * @throws ServerException
     */
    public function getMetadata()
    {
        if (!$this->metadata) {
            $this->fetchMetadata();
        }
        return $this->metadata;
    }

    /**
     *
     * Check if current workspace supports HTML or JSON documents types.
     * Document (HTML or JSON) type(s) can be added to a Workspaces in the on Settings page
     *
     * @param string $projectType
     * @throws RuntimeException
     * @throws Exception
     * @throws AuthException
     * @throws ConnException
     * @throws DocumentException
     * @throws ServerException
     */
    private function checkProjectType($projectType)
    {
        $meta = $this->getMetadata();
        $isTypeExist = false;
        $contentTypeCodes = $meta->project->content_type_codes;
        if (is_array($contentTypeCodes)) {
            foreach ($contentTypeCodes as $key => $type) {
                if (isset($type->extensions[0]) && ($type->extensions[0] === $projectType)) {
                    $isTypeExist = true;
                    break;
                }
            }
        }
        if (!$isTypeExist) {
            throw new DocumentException('Sorry, this type of documents is not supported by the project.');
        }
    }

    /**
     *
     * Send request to Qordoba Application to updated an existing completed document via REST API
     *
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
    )
    {
        $this->fetchMetadata();
        $this->checkProjectType($type);
        $uploadId = $this->upload->sendFile(sprintf('%s.%s', $documentName, $type), $documentContent, true, $fileId);
        return $this->upload->updateProject($fileId, $uploadId);
    }

    /**
     *
     * Send request to Qordoba Application to get an existing document via REST API
     *
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
    public function fetch($documentName, $documentLanguageCode = null, $documentTag = null, $documentType = DocumentInterface::TYPE_JSON)
    {
        if (!$documentName || ('' === $documentName)) {
            throw new ProjectException('Document name is not defined.');
        }

        $pages = $this->check($documentName, $documentLanguageCode, null, DocumentInterface::STATE_COMPLETED, $documentType);
        $results = [];
        foreach ($pages as $language => $page) {
            if ((int)$page->meta->paging->total_results === 0) {
                continue;
            }
            if (($documentTag !== null) && isset($page->pages) && is_array($page->pages)) {
                foreach ($page->pages as $key => $doc) {
                    if (isset($doc->version_tag) && ($doc->version_tag === $documentTag)) {
                        $results[$language] = $doc;
                        break;
                    }
                }
            } else {
                $results[$language] = array_shift($page->pages);
            }
        }

        $languagesByCode = [];
        $targetLanguages = $this->getMetadata()->project->target_languages;

        if (is_array($targetLanguages)) {
            foreach ($targetLanguages as $key => $language) {
                if ($documentLanguageCode) {
                    if ($documentLanguageCode === $language->code) {
                        $languagesByCode[$language->code] = ['id' => $language->id, 'code' => $language->code];
                        $result[$language->code] = $this->connection->fetchProjectSearch(
                            $this->getProjectId(),
                            $language->id,
                            sprintf('%s.%s', $documentName, $documentType)
                        );
                    }
                } else {
                    $languagesByCode[$language->code] = ['id' => $language->id, 'code' => $language->code];
                    $result[$language->code] = $this->connection->fetchProjectSearch(
                        $this->getProjectId(),
                        $language->id,
                        sprintf('%s.%s', $documentName, $documentType)
                    );
                }
            }
        }

        foreach ($results as $language => $version) {
            if (isset($languagesByCode[$language])) {
                $results[$languagesByCode[$language]['code']] = $this->connection->fetchTranslationFile(
                    $this->getProjectId(),
                    $languagesByCode[$language]['id'],
                    $version->page_id
                );
            }
        }
        return $results;
    }

    /**
     *
     * Send request to check state of a document on Qordoba Application via REST API
     *
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
    )
    {
        if (!$documentName || '' === $documentName) {
            throw new ProjectException('Document name is not defined.');
        }

        $this->fetchMetadata();

        $result = [];
        $languagesByCode = [];

        $targetLanguages = $this->getMetadata()->project->target_languages;
        if (is_array($targetLanguages)) {
            foreach ($targetLanguages as $key => $lang) {
                if ($documentLanguageCode) {
                    if ($documentLanguageCode === $lang->code) {
                        $languagesByCode[$lang->code] = ['id' => $lang->id, 'code' => $lang->code];
                        $result[$lang->code] = $this->connection->fetchProjectSearch(
                            $this->getProjectId(),
                            $lang->id,
                            sprintf('%s.%s', $documentName, $type),
                            $status
                        );
                        break;
                    }
                } else {
                    $languagesByCode[$lang->code] = ['id' => $lang->id, 'code' => $lang->code];
                    $result[$lang->code] = $this->connection->fetchProjectSearch(
                        $this->getProjectId(),
                        $lang->id,
                        sprintf('%s.%s', $documentName, $type),
                        $status
                    );
                }
            }
        }
        if ($documentLanguageCode !== null && !isset($result[$documentLanguageCode])) {
            throw new ProjectException('Checked language ID not found in the project');
        }

        if (($documentLanguageCode !== null && $languagesByCode[$documentLanguageCode] !== null)
            && isset($result[$documentLanguageCode])) {
            return [$documentLanguageCode => $result[$documentLanguageCode]];
        }
        return $result;
    }

    /**
     *
     * Send request to get saved document on Qordoba Application via REST API
     *
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
    public function fetchSaved($documentName, $documentLanguageCode = null, $documentTag = null, $documentType = 'json')
    {
        if (!$documentName || ('' === $documentName)) {
            throw new ProjectException('Document name is not defined.');
        }

        $pages = $this->check($documentName, $documentLanguageCode, null, DocumentInterface::STATE_ENABLED, $documentType);
        $results = [];
        foreach ($pages as $language => $page) {
            if ((int)$page->meta->paging->total_results === 0) {
                continue;
            }
            if (($documentTag !== null) && isset($page->pages) && is_array($page->pages)) {
                foreach ($page->pages as $key => $doc) {
                    if (isset($doc->version_tag) && ($doc->version_tag === $documentTag)) {
                        $results[$language] = $doc;
                        break;
                    }
                }
            } else {
                $results[$language] = array_shift($page->pages);
            }
        }

        $languagesByCode = [];
        $targetLanguages = $this->getMetadata()->project->target_languages;

        if (is_array($targetLanguages)) {
            foreach ($targetLanguages as $key => $language) {
                if ($documentLanguageCode) {
                    if ($documentLanguageCode === $language->code) {
                        $languagesByCode[$language->code] = ['id' => $language->id, 'code' => $language->code];
                        $result[$language->code] = $this->connection->fetchProjectSearch(
                            $this->getProjectId(),
                            $language->id,
                            sprintf('%s.%s', $documentName, $documentType),
                            DocumentInterface::STATE_ENABLED
                        );
                    }
                } else {
                    $languagesByCode[$language->code] = ['id' => $language->id, 'code' => $language->code];
                    $result[$language->code] = $this->connection->fetchProjectSearch(
                        $this->getProjectId(),
                        $language->id,
                        sprintf('%s.%s', $documentName, $documentType),
                        DocumentInterface::STATE_ENABLED
                    );
                }
            }
        }

        foreach ($results as $language => $version) {
            if (isset($languagesByCode[$language])) {
                $results[$languagesByCode[$language]['code']] = $this->connection->fetchTranslationFile(
                    $this->getProjectId(),
                    $languagesByCode[$language]['id'],
                    $version->page_id
                );
            }
        }
        return $results;
    }
}
