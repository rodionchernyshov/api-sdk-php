<?php

namespace Qordoba;

use Qordoba\Exception\DocumentException;
use Qordoba\Exception\ProjectException;


class Project {

  private $projectId;
  private $organizationId;
  private $connection;
  private $metadata;

  private $upload = null;

  public function __construct($projectId, $organizationId, Connection $connection) {
    $this->setProjectId($projectId);
    $this->setOrganizationId($organizationId);
    $this->connection = $connection;
    $this->upload = new Upload($this->connection, $this->getProjectId(), $this->getOrganizationId());
  }

  public function setProjectId($projectId) {
    $this->projectId = $projectId;
  }

  public function getProjectId() {
    return $this->projectId;
  }

  public function setOrganizationId($organizationId) {
    $this->organizationId = $organizationId;
  }

  public function getOrganizationId() {
    return $this->organizationId;
  }

  public function fetchMetadata() {
    $this->metadata = $this->connection->fetchProject($this->getProjectId());

    $newLanguages = [];
    $languages = $this->metadata->project->target_languages;
    foreach($languages as $key => $lang) {
     $newLanguages[$lang->id] = $lang;
    }

    $this->metadata->project->target_languages = $newLanguages;

    return $this->getMetadata();
  }

  public function getMetadata() {
    if(!$this->metadata) {
      $this->fetchMetadata();
    }

    return $this->metadata;
  }

  public function upload($documentName, $jsonToTranslate, $tag = null) {
    $this->fetchMetadata();

    $this->upload->sendFile($documentName . ".json", $jsonToTranslate);
    return $this->upload->appendToProject($tag);
  }


  public function update($documentName, $jsonToTranslate, $tag = null) {
    $this->fetchMetadata();

    $this->upload->sendFile($documentName . ".json", $jsonToTranslate);
    return $this->upload->appendToProject($tag);
  }

  public function check($documentName, $languageCode = null, $tag = null) {
    if(!$documentName || mb_strlen($documentName) == 0) {
      throw new ProjectException("Document name is not defined.");
    }

    $this->fetchMetadata();

    $result = [];
    $langsByCode = [];
    foreach($this->getMetadata()->project->target_languages as $key => $lang) {
      $langsByCode[$lang->code] = ['id' => $lang->id, 'code' => $lang->code];
      $result[$lang->code] = $this->connection->fetchProjectSearch($this->getProjectId(), $lang->id, $documentName . ".json", "completed");
    }
    if(($languageCode != null && $langsByCode[$languageCode] != null) && isset($result[$languageCode])) {
      return [$languageCode => $result[$languageCode]];
    } else if ($languageCode != null && !isset($result[$languageCode])) {
      throw new ProjectException('Checked language ID not found in project');
    }

    return $result;
  }

  public function fetch($documentName, $languageCode = null, $tag = null) {
    if(!$documentName || mb_strlen($documentName) == 0) {
      throw new ProjectException("Document name is not defined.");
    }

    $this->fetchMetadata();

    $pages = $this->check($documentName, $languageCode);
    $results = [];

    foreach($pages as $lang => $page) {
      if($page->meta->paging->total_results == 0) {
        continue;
      }

      if($tag !== null) {
        foreach($page->pages as $key => $doc) {
          if($doc->version_tag == $tag) {
            $results[$lang] = $doc;
            break;
          }
        }
      } else {
        $results[$lang] = array_shift($page->pages);
      }
    }

    $langsByCode = [];
    foreach($this->getMetadata()->project->target_languages as $key => $lang) {
      $langsByCode[$lang->code] = ['id' => $lang->id, 'code' => $lang->code];
      $result[$lang->code] = $this->connection->fetchProjectSearch($this->getProjectId(), $lang->id, $documentName . ".json", "completed");
    }

    foreach($results as $lang => $version) {
      if(isset($langsByCode[$lang])) {
        $results[$langsByCode[$lang]['code']] = $this->connection->fetchTranslationFile($this->getProjectId(), $langsByCode[$lang]['id'], $version->page_id);
      }
    }

    return $results;
  }
}