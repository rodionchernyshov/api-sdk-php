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
use Qordoba\Interfaces\DocumentInterface;
use RuntimeException;

/**
 * Class Document
 *
 * @package Qordoba
 */
class Document implements DocumentInterface
{

    /**
     *
     * Document sections (only JSON file type support)
     *
     * @var array
     */
    public $sections = [];
    /**
     *
     * Qordoba Application active connection object
     *
     * @var null|Connection
     */
    private $connection;
    /**
     *
     * Qordoba Application Workspace representation object
     *
     * @var null|Project
     */
    private $project;
    /**
     * @var array[TranslateSection]
     */
    private $translationStrings;
    /**
     * @var TranslateContent
     */
    private $translationContent;
    /**
     *
     * Document type (HTML or JSON) will be sent/downloaded to/from Qordoba Application via REST API
     *
     * @var string
     */
    private $type = DocumentInterface::TYPE_JSON;
    /**
     *
     * Document version tag will be sent/downloaded to/from Qordoba Application via REST API
     *
     * @var string
     */
    private $tag;
    /**
     *
     * Document name will be sent/downloaded to/from Qordoba Application via REST API
     *
     * @var string
     */
    private $name;
    /**
     *
     * Document identificator will be sent/downloaded to/from Qordoba Application via REST API
     *
     * @var null
     */
    private $id;
    /**
     *
     * Available languages on Qordoba Application Workspace settings
     *
     * @var null
     */
    private $languages;

    /**
     * Document constructor.
     *
     * @param $apiUrl
     * @param $username
     * @param $password
     * @param $projectId
     * @param $organizationId
     */
    public function __construct($apiUrl, $username, $password, $projectId, $organizationId)
    {
        $this->tag = DocumentInterface::DEFAULT_TAG_NAME;
        $this->name = '';
        $this->translationStrings = [];
        if ($apiUrl) {
            $this->connection = new Connection($apiUrl, $username, $password);
        } else {
            $this->connection = new Connection(Connection::DEFAULT_API_URL, $username, $password);
        }
        $this->project = new Project($projectId, $organizationId, $this->connection);
    }

    /**
     *
     * Gets active connection to Qorodba Application
     *
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     *
     * Add new item to a document (JSON file type only)
     *
     * @param string $key
     * @return mixed
     * @throws DocumentException
     */
    public function addSection($key)
    {
        if (DocumentInterface::TYPE_JSON !== $this->getType()) {
            throw new DocumentException(
                sprintf(
                    'Strings can be added only to appropriate project. Please set type to \'%s\'.',
                    DocumentInterface::TYPE_JSON
                ),
                DocumentException::TRANSLATION_WRONG_TYPE
            );
        }
        $this->sections[$key] = new TranslateSection($key);
        return $this->sections[$key];
    }

    /**
     *
     * Gets active file type (supported file types JSON HTML)
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     *
     * Set active file type (supported file types JSON HTML)
     *
     * @param $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     *
     * Get translation for an element in the document (support JSON file type only)
     *
     * @param string $key
     * @return bool|mixed
     * @throws DocumentException
     */
    public function getTranslationString($key)
    {
        if (DocumentInterface::TYPE_JSON !== $this->getType()) {
            throw new DocumentException(
                sprintf(
                    'Strings can be added only to appropriate project. Please set type to \'%s\'.',
                    DocumentInterface::TYPE_JSON
                ),
                DocumentException::TRANSLATION_WRONG_TYPE
            );
        }

        if (!isset($this->translationStrings[$key])) {
            return false;
        }

        return $this->translationStrings[$key];
    }

    /**
     *
     * Get translation of the document (support JSON file type only)
     *
     * @return array
     * @throws DocumentException
     */
    public function getTranslationStrings()
    {
        if (DocumentInterface::TYPE_JSON !== $this->getType()) {
            throw new DocumentException(
                sprintf(
                    'Strings can be added only to appropriate project. Please set type to \'%s\'.',
                    DocumentInterface::TYPE_JSON
                ),
                DocumentException::TRANSLATION_WRONG_TYPE
            );
        }
        return $this->translationStrings;
    }

    /**
     *
     * Get Qorodba Application Workspaces metadata
     *
     * @return array
     * @throws Exception
     */
    public function getMetadata()
    {
        $this->fetchMetadata();
        return ['languages' => $this->getProjectLanguages()];
    }

    /**
     *
     * Get remote Qorodba Application Workspaces metadata
     *
     * @throws RuntimeException
     * @throws Exception
     * @throws AuthException
     * @throws ConnException
     * @throws ServerException
     */
    public function fetchMetadata()
    {
        if (!$this->languages) {
            $this->languages = $this->connection->fetchLanguages();
        }
    }

    /**
     *
     * Get remote Qorodba Application Workspaces Target languages
     *
     * @return array
     * @throws RuntimeException
     * @throws Exception
     * @throws AuthException
     * @throws ConnException
     * @throws ServerException
     */
    public function getProjectLanguages()
    {
        return $this->project->getMetadata()->project->target_languages;
    }

    /**
     *
     * Send document to Qorodba Application via REST API
     *
     * @return int
     * @throws RuntimeException
     * @throws Exception
     * @throws AuthException
     * @throws ConnException
     * @throws DocumentException
     * @throws ServerException
     * @throws UploadException
     */
    public function createTranslation()
    {
        $contents = null;
        $type = $this->getType();
        if ($type === DocumentInterface::TYPE_JSON) {
            $contents = json_encode($this->sections);
        } elseif ($type === DocumentInterface::TYPE_HTML) {
            $contents = $this->getTranslationContent();
        }

        if ('' === trim($contents)) {
            throw new DocumentException('Contents for upload can\'t be empty');
        }

        $this->id = $this->project->upload($this->getName(), $contents, $this->getTag(), $this->getType());
        return $this->id;
    }

    /**
     *
     * Get completed document content from Qorodba Application via REST API
     *
     * @return mixed
     * @throws DocumentException
     */
    public function getTranslationContent()
    {
        if (DocumentInterface::TYPE_HTML !== $this->getType()) {
            throw new DocumentException(
                sprintf(
                    'HTML content can be added only to appropriate project. Please set type to \'%s\'.',
                    DocumentInterface::TYPE_HTML
                ),
                DocumentException::TRANSLATION_WRONG_TYPE
            );
        }
        return $this->translationContent->getContent();
    }

    /**
     *
     * Get document name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     * Set document name will be sent/download to/from Qordoba Application
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = trim($name);
    }

    /**
     *
     * Get document version/tag
     *
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     *
     * Set document version/tag will be sent/download to/from Qordoba Application
     *
     * @param $tag
     */
    public function setTag($tag)
    {
        $this->tag = (string)$tag;
    }

    /**
     *
     * Update document content on Qorodba Application via REST API
     *
     * @return int
     * @throws RuntimeException
     * @throws Exception
     * @throws AuthException
     * @throws ConnException
     * @throws DocumentException
     * @throws ProjectException
     * @throws ServerException
     * @throws UploadException
     */
    public function updateTranslation()
    {
        $id = $this->getId();
        $contents = null;
        $type = $this->getType();
        if (!$id) {
            $locales = $this->getProject()->check($this->getName(), null, null, 'none');
            $locale = null;
            foreach ($locales as $key => $val) {
                if (isset($val->pages) && is_array($val->pages)) {
                    foreach ($val->pages as $pageIndex => $page) {
                        if (isset($val->pages[$pageIndex])) {
                            $locale = $val->pages[$pageIndex];
                            break;
                        }
                    }
                }
            }
            if (!$locale) {
                throw new DocumentException('You must create file before updating.');
            }
            $this->setId($locale->page_id);
        }

        if ($type === DocumentInterface::TYPE_JSON) {
            $contents = json_encode($this->sections);
        } elseif ($type === DocumentInterface::TYPE_HTML) {
            $contents = $this->getTranslationContent();
        }

        if ('' === trim($contents)) {
            throw new DocumentException('Contents for upload is empty');
        }

        if ($this->project->update($this->getName(), $contents, $this->getTag(), $this->getId(), $this->getType())) {
            $id = $this->getId();
        }
        return $id;
    }

    /**
     *
     * Get document ID
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * Set document ID
     *
     * @param int|string $id
     */
    public function setId($id)
    {
        $this->id = (int)$id;
    }

    /**
     *
     * Get Qordoba Application Workspace object with additional data
     *
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     *
     * Check if remote translation exist in the Qordoba Application Workspace
     *
     * @param null|string $languageCode
     * @return array
     * @throws RuntimeException
     * @throws Exception
     * @throws AuthException
     * @throws ConnException
     * @throws ProjectException
     * @throws ServerException
     */
    public function checkTranslation($languageCode = null)
    {
        return $this->project->check($this->getName(), $languageCode, $this->getTag(), $this->getType());
    }

    /**
     *
     * Download document if it's exists and competed in the Qordoba Application Workspace
     *
     * @param null|string $languageCode
     * @return array
     * @throws RuntimeException
     * @throws Exception
     * @throws AuthException
     * @throws ConnException
     * @throws ProjectException
     * @throws ServerException
     */
    public function fetchTranslation($languageCode = null)
    {
        return $this->project->fetch($this->getName(), $languageCode, $this->getTag(), $this->getType());
    }

    /**
     *
     * Download document if it's exists and saved in the Qordoba Application Workspace
     *
     * @param null|string $languageCode
     * @return array
     * @throws RuntimeException
     * @throws Exception
     * @throws AuthException
     * @throws ConnException
     * @throws ProjectException
     * @throws ServerException
     */
    public function fetchSavedTranslation($languageCode = null)
    {
        return $this->project->fetchSaved($this->getName(), $languageCode, $this->getTag(), $this->getType());
    }

    /**
     *
     * Download list of Qordoba supported Language Codes
     *
     * @return array
     * @throws Exception
     * @throws AuthException
     * @throws ConnException
     * @throws ServerException
     */
    public function getProjectLanguageCodes()
    {
        $languages = [];
        $targetLanguages = $this->project->getMetadata()->project->target_languages;
        if (is_array($targetLanguages)) {
            foreach ($targetLanguages as $key => $lang) {
                if (isset($lang->id, $lang->code) && ('' !== $lang->id) && ('' !== $lang->code)) {
                    $languages = ['id' => $lang->id, 'code' => $lang->code];
                    break;
                }
            }
        }
        return $languages;
    }

    /**
     *
     * Set HTML content for a document (support HTML only)
     *
     * @param $value
     * @return bool
     * @throws DocumentException
     */
    public function addTranslationContent($value)
    {
        if ($this->getType() !== DocumentInterface::TYPE_HTML) {
            throw new DocumentException(
                sprintf(
                    'Strings can be added only to appropriate project. Please set type to \'%s\'.',
                    DocumentInterface::TYPE_HTML
                ),
                DocumentException::TRANSLATION_WRONG_TYPE
            );
        }
        $this->translationContent = new TranslateContent();
        $this->translationContent->addContent($value);
        return true;
    }

    /**
     *
     * Add new section to JSON document
     *
     * @param $key
     * @param $value
     * @return bool
     * @throws DocumentException
     */
    public function addTranslationString($key, $value)
    {
        if ($this->getType() !== DocumentInterface::TYPE_JSON) {
            throw new DocumentException(
                sprintf(
                    'Strings can be added only to appropriate project. Please set type to \'%s\'.',
                    DocumentInterface::TYPE_JSON
                ),
                DocumentException::TRANSLATION_WRONG_TYPE
            );
        }

        if (isset($this->sections[$key])) {
            throw new DocumentException(
                'String already exists. Please use method to edit it.',
                DocumentException::TRANSLATION_STRING_EXISTS
            );
        }
        $this->sections[$key] = new TranslateString($key, $value, $this);
        return true;
    }

    /**
     *
     * Update document content (support only of HTML file type)
     *
     * @param $value
     * @return bool
     * @throws DocumentException
     */
    public function updateTranslationContent($value)
    {
        if ($this->getType() !== DocumentInterface::TYPE_HTML) {
            throw new DocumentException(
                'HTML content can be added only to appropriate project. Please set type to \'html\'.',
                DocumentException::TRANSLATION_WRONG_TYPE
            );
        }

        if (!$this->translationContent) {
            throw new DocumentException(
                'Cannot update not existing content.',
                DocumentException::TRANSLATION_WRONG_TYPE
            );
        }
        $this->translationContent->updateContent($value);
        return true;
    }


    /**
     *
     * Update JSON document section (support only of JSON file type)
     *
     * @param $key
     * @param $value
     * @return bool
     * @throws DocumentException
     */
    public function updateTranslationString($key, $value)
    {
        if ($this->getType() !== DocumentInterface::TYPE_JSON) {
            throw new DocumentException(
                'Strings can be added only to appropriate project. Please set type to \'json\'.',
                DocumentException::TRANSLATION_WRONG_TYPE
            );
        }

        if (!isset($this->sections[$key]) || $this->sections[$key] instanceof TranslateSection) {
            throw new DocumentException(
                'String not exists. Please use method to edit it.',
                DocumentException::TRANSLATION_STRING_NOT_EXISTS
            );
        }

        $this->sections[$key] = new TranslateString($key, $value, $this);
        return true;
    }


    /**
     *
     * Delete document content (support only of HTML file type)
     *
     * @return bool
     * @throws DocumentException
     */
    public function removeTranslationContent()
    {
        if ($this->getType() !== DocumentInterface::TYPE_HTML) {
            throw new DocumentException(
                'HTML content can be added only to appropriate project. Please set type to \'html\'.',
                DocumentException::TRANSLATION_WRONG_TYPE
            );
        }

        if (!$this->translationContent) {
            throw new DocumentException(
                'Cannot update not existing content.',
                DocumentException::TRANSLATION_WRONG_TYPE
            );
        }
        $this->translationContent = null;
        return true;
    }

    /**
     *
     * Search & remove section from JSON file
     *
     * @param $searchChunk
     * @return bool
     * @throws DocumentException
     */
    public function removeTranslationString($searchChunk)
    {
        if ($this->getType() !== DocumentInterface::TYPE_JSON) {
            throw new DocumentException(
                'Strings can be added only to appropriate project. Please set type to \'json\'.',
                DocumentException::TRANSLATION_WRONG_TYPE
            );
        }

        if (isset($this->sections[$searchChunk])) {
            return $this->removeTranslationStringByKey($searchChunk);
        }
        return $this->removeTranslationStringByValue($searchChunk);
    }

    /**
     *
     * Search & remove section from JSON file by section title
     *
     * @param $searchChunk
     * @return bool
     * @throws DocumentException
     */
    private function removeTranslationStringByKey($searchChunk)
    {
        $isRemoved = false;
        if ($this->getType() !== DocumentInterface::TYPE_JSON) {
            throw new DocumentException(
                'Strings can be added only to appropriate project. Please set type to \'json\'.',
                DocumentException::TRANSLATION_WRONG_TYPE
            );
        }
        if (isset($this->sections[$searchChunk]) && ($this->sections[$searchChunk] instanceof TranslateString)) {
            unset($this->sections[$searchChunk]);
            $isRemoved = true;
        }
        return $isRemoved;
    }

    /**
     *
     * Search & remove section from JSON file by section content
     *
     * @param $searchChunk
     * @return bool
     * @throws DocumentException
     */
    private function removeTranslationStringByValue($searchChunk)
    {
        $isRemoved = false;
        if ($this->getType() !== DocumentInterface::TYPE_JSON) {
            throw new DocumentException(
                "Strings can be added only to appropriate project. Please set type to 'json'.",
                DocumentException::TRANSLATION_WRONG_TYPE
            );
        }
        foreach ($this->sections as $key => $value) {
            if (($searchChunk === $value) && ($this->sections[$key] instanceof TranslateString)) {
                unset($this->sections[$key]);
                $isRemoved = true;
            }
        }
        return $isRemoved;
    }
}
