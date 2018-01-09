<?php

namespace Qordoba;

use Qordoba\Exception\DocumentException;


/**
 * Class Document
 *
 * @package Qordoba
 */
class Document
{
	/**
	 * @const string
	 */
	const TYPE_JSON = 'json';
	/**
	 * @const string
	 */
	const TYPE_HTML = 'html';

	/**
	 * @var array
	 */
	public $_sections = [];
	/**
	 * @var null|\Qordoba\Connection
	 */
	private $connection;
	/**
	 * @var null|\Qordoba\Project
	 */
	private $project;
	/**
	 * @var array
	 */
	private $translationStrings = [];
	/**
	 * @var string
	 */
	private $translationContent = '';
	/**
	 * @var string
	 */
	private $type = self::TYPE_JSON;
	/**
	 * @var string
	 */
	private $tag = 'New';
	/**
	 * @var string
	 */
	private $name = '';
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
		$this->connection = new Connection($apiUrl, $username, $password);
		$this->project = new Project($projectId, $organizationId, $this->connection);
	}

	/**
	 * @return null|\Qordoba\Connection
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
		if (self::TYPE_JSON !== $this->getType()) {
			throw new DocumentException("Strings can be added only to appropriate project. Please set type to 'json'.",
				DocumentException::TRANSLATION_WRONG_TYPE);
		}

		$this->_sections[$key] = new TranslateSection($key);
		return $this->_sections[$key];
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
	 * @param $key
	 * @return bool|mixed
	 * @throws \Qordoba\Exception\DocumentException
	 */
	public function getTranslationString($key)
	{
		if (self::TYPE_JSON !== $this->getType()) {
			throw new DocumentException("Strings can be added only to appropriate project. Please set type to 'json'.",
				DocumentException::TRANSLATION_WRONG_TYPE);
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
		if (self::TYPE_JSON !== $this->getType()) {
			throw new DocumentException("Strings can be added only to appropriate project. Please set type to 'json'.",
				DocumentException::TRANSLATION_WRONG_TYPE);
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
		return [
			'languages' => $this->languages
		];
	}

	/**
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
	 * @return mixed
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
	 * @return null
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

		switch ($this->getType()) {
			case self::TYPE_JSON:
				{
					$contents = json_encode($this->_sections);
					break;
				}
			case self::TYPE_HTML:
				{
					$contents = $this->getTranslationContent();
					break;
				}
		}

		if (empty($contents)) {
			throw new DocumentException('Contents for upload is empty');
		}

		$this->id = $this->project->upload($this->getName(), $contents, $this->getTag(), $this->getType());
		return $this->getId();
	}

	/**
	 * @return mixed
	 * @throws \Qordoba\Exception\DocumentException
	 */
	public function getTranslationContent()
	{
		if (self::TYPE_HTML !== $this->getType()) {
			throw new DocumentException("HTML content can be added only to appropriate project. Please set type to 'html'.",
				DocumentException::TRANSLATION_WRONG_TYPE);
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
	 * @param $name
	 */
	public function setName($name)
	{
		$this->name = $name;
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
	 * @return null
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return void
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
		if (!$this->getId()) {
			//Search for file
			$locales = $this->getProject()->check($this->getName(), null, null, 'none');
			$locale = null;
			foreach ($locales as $key => $val) {
				if (count($val->pages) > 0) {
					foreach ($val->pages as $inkey => $page) {
						//if($page->version_tag == $this->getTag()) {
						$locale = $val->pages[$inkey];
						break;
						//}
					}
					break;
				}
			}

			if (!$locale) {
				throw new DocumentException('You must create file before updating.');
			}

			$this->setId($locale->page_id);
		}
		$contents = null;

		switch ($this->getType()) {
			case self::TYPE_JSON:
				{
					$contents = json_encode($this->_sections);
					break;
				}
			case self::TYPE_HTML:
				{
					$contents = $this->getTranslationContent();
					break;
				}
		}

		if (empty($contents)) {
			throw new DocumentException('Contents for upload is empty');
		}

		if ($this->project->update($this->getName(), $contents, $this->getTag(), $this->getId(), $this->getType())) {
			return $this->getId();
		}
	}

	/**
	 * @return null|\Qordoba\Project
	 */
	public function getProject()
	{
		return $this->project;
	}

	/**
	 * @param null $languageCode
	 * @return array
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
	 * @param null $languageCode
	 * @return array
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
		foreach ($this->project->getMetadata()->project->target_languages as $key => $lang) {
			$languages = ['id' => $lang->id, 'code' => $lang->code];
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
		if ($this->getType() !== self::TYPE_HTML) {
			throw new DocumentException(
				'HTML content can be added only to appropriate project. Please set type to \'html\'.',
				DocumentException::TRANSLATION_WRONG_TYPE);
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
		if ($this->getType() !== self::TYPE_JSON) {
			throw new DocumentException(
				'Strings can be added only to appropriate project. Please set type to \'json\'.',
				DocumentException::TRANSLATION_WRONG_TYPE
			);
		}

		if (isset($this->_sections[$key])) {
			throw new DocumentException(
				'String already exists. Please use method to edit it.',
				DocumentException::TRANSLATION_STRING_EXISTS
			);
		}

		$this->_sections[$key] = new TranslateString($key, $value, $this);
		return true;
	}

	/**
	 * @param $value
	 * @return bool
	 * @throws \Qordoba\Exception\DocumentException
	 */
	public function updateTranslationContent($value)
	{
		if ($this->getType() !== self::TYPE_HTML) {
			throw new DocumentException(
				'HTML content can be added only to appropriate project. Please set type to \'html\'.',
				DocumentException::TRANSLATION_WRONG_TYPE
			);
		}

		if (!$this->translationContent) {
			throw new DocumentException('Cannot update not existing content.', DocumentException::TRANSLATION_WRONG_TYPE);
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
		if ($this->getType() !== self::TYPE_JSON) {
			throw new DocumentException(
				'Strings can be added only to appropriate project. Please set type to \'json\'.',
				DocumentException::TRANSLATION_WRONG_TYPE
			);
		}

		if (!isset($this->_sections[$key]) || $this->_sections[$key] instanceof TranslateSection) {
			throw new DocumentException(
				'String not exists. Please use method to edit it.',
				DocumentException::TRANSLATION_STRING_NOT_EXISTS
			);
		}

		$this->_sections[$key] = new TranslateString($key, $value, $this);
		return true;
	}


	/**
	 * @return bool
	 * @throws \Qordoba\Exception\DocumentException
	 */
	public function removeTranslationContent()
	{
		if ($this->getType() !== self::TYPE_HTML) {
			throw new DocumentException(
				'HTML content can be added only to appropriate project. Please set type to \'html\'.',
				DocumentException::TRANSLATION_WRONG_TYPE);
		}

		if (!$this->translationContent) {
			throw new DocumentException('Cannot update not existing content.', DocumentException::TRANSLATION_WRONG_TYPE);
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
		if ($this->getType() !== self::TYPE_JSON) {
			throw new DocumentException(
				'Strings can be added only to appropriate project. Please set type to \'json\'.',
				DocumentException::TRANSLATION_WRONG_TYPE
			);
		}

		if (isset($this->_sections[$searchChunk])) {
			return $this->removeTranslationStringByKey($searchChunk);
		} else {
			return $this->removeTranslationStringByValue($searchChunk);
		}
	}

	/**
	 * @param $searchChunk
	 * @return bool
	 * @throws \Qordoba\Exception\DocumentException
	 */
	private function removeTranslationStringByKey($searchChunk)
	{
		if ($this->getType() !== self::TYPE_JSON) {
			throw new DocumentException(
				'Strings can be added only to appropriate project. Please set type to \'json\'.',
				DocumentException::TRANSLATION_WRONG_TYPE
			);
		}

		if (isset($this->_sections[$searchChunk]) && $this->_sections[$searchChunk] instanceof TranslateString) {
			unset($this->_sections[$searchChunk]);
			return true;
		}

		return false;
	}

	/**
	 * @param $searchChunk
	 * @return bool
	 * @throws \Qordoba\Exception\DocumentException
	 */
	private function removeTranslationStringByValue($searchChunk)
	{
		if ($this->getType() !== self::TYPE_JSON) {
			throw new DocumentException(
				"Strings can be added only to appropriate project. Please set type to 'json'.",
				DocumentException::TRANSLATION_WRONG_TYPE
			);
		}

		$result = false;
		foreach ($this->_sections as $key => $val) {
			if (($searchChunk === $val) && ($this->_sections[$key] instanceof TranslateString)) {
				unset($this->_sections[$key]);
				$result = true;
			}
		}
		return $result;
	}
}