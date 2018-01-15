<?php
/**
 * @category Qordoba PHP SDK
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba;

use Qordoba\Exception\DocumentException;
use Qordoba\Interfaces\DocumentInterface;

/**
 * Class Document
 *
 * @package Qordoba
 */
class Document implements DocumentInterface
{
    
    /**
     * @var array
     */
    public $sections = [];
    /**
     * @var null|\Qordoba\Connection
     */
    private $connection;
    /**
     * @var null|\Qordoba\Project
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
     * @var string
     */
    private $type = DocumentInterface::TYPE_JSON;
    /**
     * @var string
     */
    private $tag;
    /**
     * @var string
     */
    private $name;
    /**
     * @var null
     */
    private $id;
    /**
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
        $this->connection = new Connection($apiUrl, $username, $password);
        $this->project = new Project($projectId, $organizationId, $this->connection);
    }
    
    /**
     * @return \Qordoba\Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }
    
    /**
     * @param $key
     * @return mixed
     * @throws \Qordoba\Exception\DocumentException
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
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * @param $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
    
    /**
     * @param string $key
     * @return bool|mixed
     * @throws \Qordoba\Exception\DocumentException
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
     * @return array
     * @throws \Qordoba\Exception\DocumentException
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
     * @return array
     * @throws \Exception
     */
    public function getMetadata()
    {
        $this->fetchMetadata();
        return ['languages' => $this->getProjectLanguages()];
    }
    
    /**
     * @throws \RuntimeException
     * @throws \Exception
     * @throws \Qordoba\Exception\AuthException
     * @throws \Qordoba\Exception\ConnException
     * @throws \Qordoba\Exception\ServerException
     */
    public function fetchMetadata()
    {
        if (!$this->languages) {
            $this->languages = $this->connection->fetchLanguages();
        }
    }
    
    /**
     * @return array
     * @throws \RuntimeException
     * @throws \Exception
     * @throws \Qordoba\Exception\AuthException
     * @throws \Qordoba\Exception\ConnException
     * @throws \Qordoba\Exception\ServerException
     */
    public function getProjectLanguages()
    {
        return $this->project->getMetadata()->project->target_languages;
    }
    
    /**
     * @return int|
     * @throws \RuntimeException
     * @throws \Exception
     * @throws \Qordoba\Exception\AuthException
     * @throws \Qordoba\Exception\ConnException
     * @throws \Qordoba\Exception\DocumentException
     * @throws \Qordoba\Exception\ServerException
     * @throws \Qordoba\Exception\UploadException
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
     * @return mixed
     * @throws \Qordoba\Exception\DocumentException
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = trim($name);
    }
    
    /**
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }
    
    /**
     * @param $tag
     */
    public function setTag($tag)
    {
        $this->tag = (string)$tag;
    }
    
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @param int|string $id
     */
    public function setId($id)
    {
        $this->id = (int)$id;
    }
    
    /**
     * @return int
     * @throws \RuntimeException
     * @throws \Exception
     * @throws \Qordoba\Exception\AuthException
     * @throws \Qordoba\Exception\ConnException
     * @throws \Qordoba\Exception\DocumentException
     * @throws \Qordoba\Exception\ProjectException
     * @throws \Qordoba\Exception\ServerException
     * @throws \Qordoba\Exception\UploadException
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
     * @return \Qordoba\Project
     */
    public function getProject()
    {
        return $this->project;
    }
    
    /**
     * @param null|string $languageCode
     * @return array
     * @throws \RuntimeException
     * @throws \Exception
     * @throws \Qordoba\Exception\AuthException
     * @throws \Qordoba\Exception\ConnException
     * @throws \Qordoba\Exception\ProjectException
     * @throws \Qordoba\Exception\ServerException
     */
    public function checkTranslation($languageCode = null)
    {
        return $this->project->check($this->getName(), $languageCode, $this->getTag(), $this->getType());
    }
    
    /**
     * @param null|string $languageCode
     * @return array
     * @throws \RuntimeException
     * @throws \Exception
     * @throws \Qordoba\Exception\AuthException
     * @throws \Qordoba\Exception\ConnException
     * @throws \Qordoba\Exception\ProjectException
     * @throws \Qordoba\Exception\ServerException
     */
    public function fetchTranslation($languageCode = null)
    {
        return $this->project->fetch($this->getName(), $languageCode, $this->getTag(), $this->getType());
    }
    
    /**
     * @return array
     * @throws \Exception
     * @throws \Qordoba\Exception\AuthException
     * @throws \Qordoba\Exception\ConnException
     * @throws \Qordoba\Exception\ServerException
     */
    public function getProjectLanguageCodes()
    {
        $languages = [];
        $targetLanguages = $this->project->getMetadata()->project->target_languages;
        if (is_array($targetLanguages)) {
            foreach ($targetLanguages as $key => $lang) {
                if (isset($lang->id, $lang->code)&& ('' !== $lang->id) && ('' !== $lang->code)) {
                    $languages = ['id' => $lang->id, 'code' => $lang->code];
                    break;
                }
            }
        }
        return $languages;
    }
    
    /**
     * @param $value
     * @return bool
     * @throws \Qordoba\Exception\DocumentException
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
     * @param $key
     * @param $value
     * @return bool
     * @throws \Qordoba\Exception\DocumentException
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
     * @param $value
     * @return bool
     * @throws \Qordoba\Exception\DocumentException
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
     * @param $key
     * @param $value
     * @return bool
     * @throws \Qordoba\Exception\DocumentException
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
     * @return bool
     * @throws \Qordoba\Exception\DocumentException
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
     * @param $searchChunk
     * @return bool
     * @throws \Qordoba\Exception\DocumentException
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
     * @param $searchChunk
     * @return bool
     * @throws \Qordoba\Exception\DocumentException
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
     * @param $searchChunk
     * @return bool
     * @throws \Qordoba\Exception\DocumentException
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
