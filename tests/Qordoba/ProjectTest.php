<?php

namespace Qordoba\Test;

use Faker\Factory;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\TestCase;
use Qordoba;
use Qordoba\Connection;
use Qordoba\Project;

/**
 * Class QordobaProjectTest
 * @package Qordoba\Test
 */
class QordobaProjectTest extends TestCase
{
    /**
     * @const string
     */
    const STANDARD_DOCUMENT_NAME = 'Test-Check-HTML-Document';
    /**
     * @const string
     */
    const STANDARD_DOCUMENT_LANGUAGE_CODE = 'fr-fr';
    /**
     * @const int
     */
    const STANDARD_DOCUMENT_TAG = 1;
    /**
     * @const string
     */
    const DOCUMENT_TYPE_HTML = 'html';

    /**
     * @var string
     */
    public $apiUrl = 'https://app.qordoba.com/api/';
    /**
     * @var string
     */
    public $login = 'rodion.chernyshov@easternpeak.com';
    /**
     * @var string
     */
    public $password = 'NeoMacuser571';
    /**
     * @var int
     */
    public $projectId = 5824;
    /**
     * @var int
     */
    public $organizationId = 3169;

    /**
     *
     */
    public function testProjectFetchMetaData()
    {
        $connection = new Connection($this->apiUrl, $this->login, $this->password);
        $project = new Project($this->projectId, $this->organizationId, $connection);
        $meta = $project->getMetadata();
        $this->assertObjectHasAttribute('project', $meta);
        $this->assertObjectHasAttribute('name', $meta->project);
        $this->assertObjectHasAttribute('id', $meta->project);
        $this->assertObjectHasAttribute('organization_id', $meta->project);
        $this->assertObjectHasAttribute('url', $meta->project);
        $this->assertObjectHasAttribute('subdomain', $meta->project);
        $this->assertObjectHasAttribute('source_language', $meta->project);
        $this->assertInternalType(IsType::TYPE_OBJECT, $meta->project->source_language);
        $this->assertObjectHasAttribute('target_languages', $meta->project);
        $this->assertInternalType(IsType::TYPE_ARRAY, $meta->project->target_languages);
        $this->assertObjectHasAttribute('content_type', $meta->project);
        $this->assertObjectHasAttribute('content_type_code', $meta->project);
        $this->assertObjectHasAttribute('admin_only_reports', $meta->project);
        $this->assertObjectHasAttribute('text_record_token', $meta->project);
        $this->assertObjectHasAttribute('mobile_project_info', $meta->project);
        $this->assertObjectHasAttribute('content_source', $meta->project);
        $this->assertObjectHasAttribute('api_key', $meta->project);
        $this->assertObjectHasAttribute('source_branch', $meta->project);
        $this->assertObjectHasAttribute('content_type_codes', $meta->project);
        $this->assertObjectHasAttribute('segmentation', $meta->project);
        $this->assertObjectHasAttribute('timezone', $meta->project);
        $this->assertObjectHasAttribute('allow_remove_variables', $meta->project);
        $this->assertObjectHasAttribute('initial_translation_mode', $meta->project);
        $this->assertObjectHasAttribute('tm_match_mode', $meta->project);
        $this->assertObjectHasAttribute('do_not_modify_list', $meta->project);
    }

    /**
     *
     */
    public function testProjectData()
    {
        $connection = new Connection($this->apiUrl, $this->login, $this->password);
        $project = new Project($this->projectId, $this->organizationId, $connection);
        $this->assertEquals($this->organizationId, $project->getOrganizationId());
        $this->assertEquals($this->projectId, $project->getProjectId());
    }

    /**
     *
     */
    public function testProjectDocumentUpload()
    {
        $connection = new Connection($this->apiUrl, $this->login, $this->password);
        $project = new Project($this->projectId, $this->organizationId, $connection);
        $this->assertInternalType(IsType::TYPE_INT, $project->upload(
            Factory::create()->word,
            Factory::create()->randomHtml(),
            (string)Factory::create()->randomDigit,
            self::DOCUMENT_TYPE_HTML
        ));
    }

    /**
     *
     */
    public function testProjectDocumentUpdate()
    {
        $connection = new Connection($this->apiUrl, $this->login, $this->password);
        $project = new Project($this->projectId, $this->organizationId, $connection);
        $documentName = Factory::create()->word;
        $tag = Factory::create()->randomDigit;
        $uploadedFileId = $project->upload(
            $documentName,
            json_encode(['test' => Factory::create()->sentence(rand(5, 10))]),
            (string)$tag
        );
        $this->assertInternalType(IsType::TYPE_INT, $uploadedFileId);
        $this->assertInternalType(IsType::TYPE_INT, $project->update(
            $documentName,
            json_encode(['updated_test' => sprintf('Im updated \'%s\' ', Factory::create()->sentence(rand(5, 10)))]),
            (string)++$tag,
            $uploadedFileId
        ));
    }

    /**
     *
     * @throws Qordoba\Exception\ProjectException
     */
    public function testProjectDocumentFetch()
    {
        $connection = new Connection($this->apiUrl, $this->login, $this->password);
        $project = new Project($this->projectId, $this->organizationId, $connection);
        $document = $project->fetch(
            self::STANDARD_DOCUMENT_NAME,
            self::STANDARD_DOCUMENT_LANGUAGE_CODE,
            self::STANDARD_DOCUMENT_TAG,
            self::DOCUMENT_TYPE_HTML
        );
        $this->assertInternalType(IsType::TYPE_ARRAY, $document);
        foreach ($document as $lang => $content) {
            $this->assertEquals(self::STANDARD_DOCUMENT_LANGUAGE_CODE, $lang);
            $this->assertInternalType(IsType::TYPE_STRING, $content);
        }
    }
}
