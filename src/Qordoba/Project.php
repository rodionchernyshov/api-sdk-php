<?php
/**
 * @category Qordoba PHP SDK
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba;

use Qordoba\Exception\DocumentException;
use Qordoba\Exception\ProjectException;


/**
 * Class Project
 *
 * @package Qordoba
 */
class Project
{

	/**
	 * @var
	 */
	private $projectId;
	/**
	 * @var
	 */
	private $organizationId;
	/**
	 * @var \Qordoba\Connection
	 */
	private $connection;
	/**
	 * @var
	 */
	private $metadata;

	/**
	 * @var null|\Qordoba\Upload
	 */
	private $upload;

	/**
	 * Project constructor.
	 *
	 * @param $projectId
	 * @param $organizationId
	 * @param \Qordoba\Connection $connection
	 */
	public function __construct($projectId, $organizationId, Connection $connection)
	{
		$this->setProjectId($projectId);
		$this->setOrganizationId($organizationId);
		$this->connection = $connection;
		$this->upload = new Upload($this->connection, $this->getProjectId(), $this->getOrganizationId());
	}

	/**
	 * @return mixed
	 */
	public function getProjectId()
	{
		return $this->projectId;
	}

	/**
	 * @param $projectId
	 */
	public function setProjectId($projectId)
	{
		$this->projectId = $projectId;
	}

	/**
	 * @return mixed
	 */
	public function getOrganizationId()
	{
		return $this->organizationId;
	}

	/**
	 * @param $organizationId
	 */
	public function setOrganizationId($organizationId)
	{
		$this->organizationId = $organizationId;
	}

	/**
	 * @return null|\Qordoba\Upload
	 */
	public function getUpload()
	{
		return $this->upload;
	}

	/**
	 * @param $documentName
	 * @param $jsonToTranslate
	 * @param null $tag
	 * @param string $type
	 * @return mixed
	 * @throws \Exception
	 * @throws \Qordoba\Exception\AuthException
	 * @throws \Qordoba\Exception\ConnException
	 * @throws \Qordoba\Exception\DocumentException
	 * @throws \Qordoba\Exception\ServerException
	 * @throws \Qordoba\Exception\UploadException
	 */
	public function upload($documentName, $jsonToTranslate, $tag = null, $type = 'json')
	{
		$this->fetchMetadata();
		$this->checkProjectType($type);
		$this->upload->sendFile($documentName . '.' . $type, $jsonToTranslate);
		return $this->upload->appendToProject($tag);
	}

	/**
	 * @return mixed
	 * @throws \Exception
	 * @throws \Qordoba\Exception\AuthException
	 * @throws \Qordoba\Exception\ConnException
	 * @throws \Qordoba\Exception\ServerException
	 */
	public function fetchMetadata()
	{
		$this->metadata = $this->connection->fetchProject($this->getProjectId());

		$newLanguages = [];
		$languages = $this->metadata->project->target_languages;
		foreach ($languages as $key => $lang) {
			$newLanguages[$lang->id] = $lang;
		}
		$this->metadata->project->target_languages = $newLanguages;
		return $this->getMetadata();
	}

	/**
	 * @return mixed
	 * @throws \Exception
	 * @throws \Qordoba\Exception\AuthException
	 * @throws \Qordoba\Exception\ConnException
	 * @throws \Qordoba\Exception\ServerException
	 */
	public function getMetadata()
	{
		if (!$this->metadata) {
			$this->fetchMetadata();
		}
		return $this->metadata;
	}

	/**
	 * @param $projectType
	 * @throws \Exception
	 * @throws \Qordoba\Exception\AuthException
	 * @throws \Qordoba\Exception\ConnException
	 * @throws \Qordoba\Exception\DocumentException
	 * @throws \Qordoba\Exception\ServerException
	 */
	private function checkProjectType($projectType)
	{
		$meta = $this->getMetadata();

		$type_found = false;
		foreach ($meta->project->content_type_codes as $key => $type) {
			if ($type->extensions[0] == $projectType) {
				$type_found = true;
			}
		}

		if (!$type_found) {
			throw new DocumentException('Sorry, this type of documents not supported by the project.');
		}
	}

	/**
	 * @param $documentName
	 * @param $jsonToTranslate
	 * @param null $tag
	 * @param null $fileId
	 * @param string $type
	 * @return mixed
	 * @throws \Exception
	 * @throws \Qordoba\Exception\AuthException
	 * @throws \Qordoba\Exception\ConnException
	 * @throws \Qordoba\Exception\DocumentException
	 * @throws \Qordoba\Exception\ServerException
	 * @throws \Qordoba\Exception\UploadException
	 */
	public function update($documentName, $jsonToTranslate, $tag = null, $fileId = null, $type = 'json')
	{
		$this->fetchMetadata();

		$this->checkProjectType($type);

		$this->upload->sendFile($documentName . '.' . $type, $jsonToTranslate, true, $fileId, $tag);
		return $this->upload->appendToProject($tag);
	}

	/**
	 * @param $documentName
	 * @param null $languageCode
	 * @param null $tag
	 * @param string $type
	 * @return array
	 * @throws \Exception
	 * @throws \Qordoba\Exception\AuthException
	 * @throws \Qordoba\Exception\ConnException
	 * @throws \Qordoba\Exception\ProjectException
	 * @throws \Qordoba\Exception\ServerException
	 */
	public function fetch($documentName, $languageCode = null, $tag = null, $type = 'json')
	{
		if (!$documentName || '' === $documentName) {
			throw new ProjectException('Document name is not defined.');
		}

		$this->fetchMetadata();
		$pages = $this->check($documentName, $languageCode, null, 'completed', $type);
		$results = [];

		foreach ($pages as $lang => $page) {
			if ($page->meta->paging->total_results == 0) {
				continue;
			}

			if ($tag !== null) {
				foreach ($page->pages as $key => $doc) {
					if ($doc->version_tag == $tag) {
						$results[$lang] = $doc;
						break;
					}
				}
			} else {
				$results[$lang] = array_shift($page->pages);
			}
		}

		$languagesByCode = [];
		foreach ($this->getMetadata()->project->target_languages as $key => $lang) {
			$languagesByCode[$lang->code] = ['id' => $lang->id, 'code' => $lang->code];
			$result[$lang->code] = $this->connection->fetchProjectSearch(
				$this->getProjectId(),
				$lang->id,
				$documentName . '.' . $type
			);
		}

		foreach ($results as $lang => $version) {
			if (isset($languagesByCode[$lang])) {
				$results[$languagesByCode[$lang]['code']] = $this->connection->fetchTranslationFile($this->getProjectId(),
					$languagesByCode[$lang]['id'], $version->page_id);
			}
		}
		return $results;
	}

	/**
	 * @param $documentName
	 * @param null $languageCode
	 * @param null $tag
	 * @param string $status
	 * @param string $type
	 * @return array
	 * @throws \Exception
	 * @throws \Qordoba\Exception\AuthException
	 * @throws \Qordoba\Exception\ConnException
	 * @throws \Qordoba\Exception\ProjectException
	 * @throws \Qordoba\Exception\ServerException
	 */
	public function check($documentName, $languageCode = null, $tag = null, $status = 'completed', $type = 'json')
	{
		if (!$documentName || '' === $documentName) {
			throw new ProjectException('Document name is not defined.');
		}

		$this->fetchMetadata();

		$result = [];
		$languagesByCode = [];

		foreach ($this->getMetadata()->project->target_languages as $key => $lang) {
			$languagesByCode[$lang->code] = ['id' => $lang->id, 'code' => $lang->code];
			$result[$lang->code] = $this->connection->fetchProjectSearch($this->getProjectId(), $lang->id,
				$documentName . '.' . $type, $status);
		}

		if (($languageCode !== null && $languagesByCode[$languageCode] !== null) && isset($result[$languageCode])) {
			return [$languageCode => $result[$languageCode]];
		} else {
			if ($languageCode != null && !isset($result[$languageCode])) {
				throw new ProjectException('Checked language ID not found in project');
			}
		}

		return $result;
	}
}