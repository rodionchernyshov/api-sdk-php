<?php

namespace Qordoba;

use Qordoba\Exception\DocumentException;

use Qordoba\Project;
use Qordoba\Connection;

use Qordoba\TranslateSection;
use Qordoba\TranslateString;


class Document {

  private $connection         = null;
  private $project            = null;
  private $translationStrings = [];
  private $translationResult  = [];
  private $type               = "default";
  private $tag                = "New";
  private $name               = "";
  private $languages          = null;

  public $_sections          = [];

  public function __construct($apiUrl, $username, $password, $projectId, $organizationId) {
    $this->connection   = new Connection($apiUrl, $username, $password);
    $this->project      = new Project($projectId, $organizationId, $this->connection);
  }

  public function getProject() {
    return $this->project;
  }

  public function getConnection() {
    return $this->connection;
  }

  public function setName($name) {
    $this->name = $name;
  }

  public function getName() {
    return $this->name;
  }

  public function setType($type) {
    $this->type = $type;
  }

  public function getType() {
    return $this->type;
  }

  public function setTag($tag) {
    $this->tag = $tag;
  }

  public function getTag() {
    return $this->tag;
  }

  public function addSection($key) {
    $this->_sections[$key] = new TranslateSection($key);
    return $this->_sections[$key];
  }

  public function getTranslationString($key) {
    if(!isset($this->translationStrings[$key])) {
      return false;
    }

    return $this->translationStrings[$key];
  }

  public function getTranslationStrings() {
    return $this->translationStrings;
  }

  public function fetchMetadata() {
    if($this->languages == null) {
      $this->languages = $this->connection->fetchLanguages();
    }
  }

  public function getMetadata() {
    $this->fetchMetadata();

    return [
      'languages' => $this->languages
    ];
  }

  public function getProjectLanguages() {
    return $this->project->getMetadata()->project->target_languages;
  }

  public function createTranslation() {
    /*$translateStruct = new \StdClass();
    $translateStruct->{$this->getType()} = [];
    array_push($translateStruct->{$this->getType()}, $this->translationStrings);*/


    return $this->project->upload($this->getName(), json_encode($this->_sections), $this->getTag());
  }

  public function updateTranslation() {
    /*$translateStruct = new \StdClass();
    $translateStruct->{$this->getType()} = [];
    array_push($translateStruct->{$this->getType()}, $this->translationStrings);*/

    return $this->project->update($this->getName(), json_encode($this->_sections), $this->getTag());
  }

  public function checkTranslation($languageCode = null) {
    return $this->project->check($this->getName(), $languageCode, $this->getTag());
  }

  public function fetchTranslation($languageCode = null) {
    return $this->project->fetch($this->getName(), $languageCode, $this->getTag());
  }

  public function getProjectLanguageCodes() {
    $langs = [];
    foreach($this->project->getMetadata()->project->target_languages as $key => $lang) {
      array_push($langs, ['id' => $lang->id, 'code' => $lang->code]);
    }

    return $langs;
  }

  public function addTranslationString($key, $value) {
    if(isset($this->_sections[$key])) {
      throw new DocumentException("String already exists. Please use method to edit it.", DocumentException::TRANSLATION_STRING_EXISTS);
    }

    $this->_sections[$key] = new TranslateString($key, $value, $this);
    return true;
  }

  public function updateTranslationString($key, $value) {
    if(!isset($this->_sections[$key]) || $this->_sections[$key] instanceof TranslateSection) {
      throw new DocumentException("String not exists. Please use method to edit it.", DocumentException::TRANSLATION_STRING_NOT_EXISTS);
    }

    $this->_sections[$key] = new TranslateString($key, $value, $this);;
    return true;
  }


  public function removeTranslationString($searchChunk) {
    if(isset($this->_sections[$searchChunk])) {
      return $this->removeTranslationStringByKey($searchChunk);
    } else {
      return $this->removeTranslationStringByValue($searchChunk);
    }
  }

  private function removeTranslationStringByKey($searchChunk) {
    if(isset($this->_sections[$searchChunk]) && $this->_sections[$searchChunk] instanceof TranslateString) {
      unset($this->_sections[$searchChunk]);
      return true;
    }

    return false;
  }

  private function removeTranslationStringByValue($searchChunk) {
    $result = false;
    foreach($this->_sections as $key => $val) {
      if($searchChunk == $val && $this->_sections[$key] instanceof TranslateString) {
        unset($this->_sections[$key]);
        $result = true;
      }
    }

    return $result;
  }
}