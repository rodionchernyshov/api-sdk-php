<?php

namespace Qordoba\Test;

use Faker\Factory;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\TestCase;
use Qordoba;
use Qordoba\Document;

/**
 * Class QordobaDocumentTest
 * @package Qordoba\Test
 */
class QordobaDocumentTest extends TestCase
{
    /**
     * @const string
     */
    const STANDARD_DOCUMENT_NAME = 'Test-Check-HTML-Document';
    /**
     * @const int
     */
    const STANDARD_DOCUMENT_TAG = 1;
    /**
     * @const string
     */
    const DEFAULT_DOCUMENT_TAG = 'New';
    /**
     * @const string
     */
    const DOCUMENT_TYPE_JSON = 'json';
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
     * @var Document
     */
    public $document;

    /**
     * @return Document
     */
    public function createDocument()
    {
        return new Document(
            $this->apiUrl,
            $this->login,
            $this->password,
            $this->projectId,
            $this->organizationId
        );
    }

    /**
     * @return string
     */
    public function createFileName()
    {
        return str_replace(' ', '-', Factory::create()->sentence());
    }

    /**
     * @throws Qordoba\Exception\DocumentException
     */
    public function testDocumentDefaults()
    {
        $document = $this->createDocument();
        $this->assertNull($document->getId());
        $this->assertInstanceOf('Qordoba\Project', $document->getProject());
        $this->assertInstanceOf('Qordoba\Connection', $document->getConnection());
        $this->assertEmpty($document->getTranslationStrings());
        $this->assertEquals(self::DOCUMENT_TYPE_JSON, $document->getType());
        $this->assertEquals(self::DEFAULT_DOCUMENT_TAG, $document->getTag());
        $this->assertEmpty($document->getName());
        $this->assertInternalType(IsType::TYPE_ARRAY, $document->getProjectLanguages());
        $this->assertFalse(0 === count($document->getProjectLanguages()), 'Project has to have languages');
    }

    /**
     *
     */
    public function testSetName()
    {
        $document = $this->createDocument();
        $documentName = Factory::create()->sentence();
        $document->setName($documentName);
        $this->assertEquals($documentName, $document->getName());
    }


    /**
     *
     */
    public function testGetTag()
    {
        $this->assertEquals(self::DEFAULT_DOCUMENT_TAG, $this->createDocument()->getTag());
    }


    /**
     *
     */
    public function testSetTag()
    {
        $document = $this->createDocument();
        $documentTag = Factory::create()->randomDigitNotNull;
        $document->setTag($documentTag);
        $this->assertEquals($documentTag, $document->getTag());
    }


    /**
     *
     */
    public function testGetType()
    {
        $this->assertEquals(self::DOCUMENT_TYPE_JSON, $this->createDocument()->getType());
    }


    /**
     *
     */
    public function testSetType()
    {
        $document = $this->createDocument();
        $document->setType(self::DOCUMENT_TYPE_JSON);
        $this->assertEquals(self::DOCUMENT_TYPE_JSON, $document->getType());
    }


    /**
     * @throws Qordoba\Exception\DocumentException
     */
    public function testGetTranslationString()
    {
        $document = $this->createDocument();
        $this->assertFalse($document->getTranslationString(Factory::create()->word));

        $stringKey = Factory::create()->word;
        $stringBody = Factory::create()->sentence();

        $this->assertTrue($document->addTranslationString($stringKey, $stringBody));
    }


    /**
     * @throws Qordoba\Exception\DocumentException
     */
    public function testAddTranslationString()
    {
        $document = $this->createDocument();
        $this->assertFalse($document->getTranslationString(Factory::create()->word));

        $section = $document->addSection(Factory::create()->word);

        $this->assertInstanceOf('Qordoba\TranslateSection', $section);
        $this->assertInternalType(IsType::TYPE_ARRAY, $section->jsonSerialize());
        $this->assertEquals(0, count($section->jsonSerialize()));

        $this->assertTrue($section->addTranslationString(Factory::create()->word, Factory::create()->sentence()));
        $this->assertTrue($section->addTranslationString(Factory::create()->word, Factory::create()->sentence()));

        $this->assertInternalType(IsType::TYPE_ARRAY, $section->jsonSerialize());
        $this->assertEquals(2, count($section->jsonSerialize()));
    }

    /**
     * @throws Qordoba\Exception\DocumentException
     */
    public function testUpdateTranslationString()
    {
        $document = $this->createDocument();
        $stringKey = Factory::create()->word;
        $this->assertTrue(
            $document->addTranslationString($stringKey, Factory::create()->sentence())
        );
        $this->assertTrue(
            $document->updateTranslationString($stringKey, Factory::create()->sentence())
        );
    }

    /**
     * @throws Qordoba\Exception\DocumentException
     */
    public function testRemoveTranslationString()
    {
        $document = $this->createDocument();
        $stringKey = Factory::create()->word;
        $this->assertTrue($document->addTranslationString($stringKey, Factory::create()->sentence(rand(5, 10))));
        $this->assertTrue($document->removeTranslationString($stringKey));
        $this->assertTrue($document->addTranslationString($stringKey, Factory::create()->sentence(rand(5, 10))));
    }

    /**
     *
     */
    public function testGetLanguagesMetaData()
    {
        $document = $this->createDocument();
        $meta = $document->getMetadata();
        $this->assertEquals(2, $document->getConnection()->getRequestCount());
        $this->assertArrayHasKey('languages', $meta);
        $this->assertTrue(0 < count($meta['languages']));
    }

    /**
     * @throws Qordoba\Exception\DocumentException
     */
    public function testCreateHTMLDocument()
    {
        $document = $this->createDocument();
        $document->setType(self::DOCUMENT_TYPE_HTML);
        $document->setTag((string)Factory::create()->randomDigit);
        $document->setName($this->createFileName());
        $this->assertTrue($document->addTranslationContent(Factory::create()->randomHtml(5)));
        $this->assertInternalType(IsType::TYPE_INT, $document->createTranslation());
    }

    /**
     *
     */
    public function testFetchHTMLDocument()
    {
        $document = $this->createDocument();
        $document->setType(self::DOCUMENT_TYPE_HTML);
        $document->setTag(self::STANDARD_DOCUMENT_TAG);
        $document->setName(self::STANDARD_DOCUMENT_NAME);

        $translation = $document->fetchTranslation();
        $this->assertInternalType(IsType::TYPE_ARRAY, $translation);
        $this->assertTrue(0 < count($translation));
        foreach ($translation as $language => $content) {
            $this->assertRegExp('/[a-z]{2}-[a-z]{2}/', $language);
            $this->assertNotEmpty($content);
        }
    }

    /**
     * @throws Qordoba\Exception\DocumentException
     */
    public function testDocumentCreate()
    {
        $document = $this->createDocument();

        $section = $document->addSection(Factory::create()->word);

        $section->addTranslationString(Factory::create()->word, Factory::create()->sentence());
        $section->addTranslationString(Factory::create()->word, Factory::create()->sentence());
        $section->addTranslationString(Factory::create()->word, Factory::create()->sentence());
        $section->addTranslationString(Factory::create()->word, Factory::create()->sentence());

        $filename = $this->createFileName();
        $document->setTag((string)Factory::create()->randomDigit);
        $document->setName($filename);
        $this->assertInternalType(IsType::TYPE_INT, $document->createTranslation());

        $this->assertEquals(4, $document->getConnection()->getRequestCount());

        foreach ($document->getConnection()->getRequests() as $key => $response) {
            $this->assertEquals(200, $response->getStatusCode());
        }

        $languages = (array)$document->getProjectLanguages();
        $language = array_shift($languages);

        $submittedDocs = $document->getConnection()
            ->fetchProjectSearch($this->projectId, $language->id, $filename, 'none');

        $this->assertTrue(0 < $submittedDocs->meta->paging->total_results);
    }

    /**
     * @throws Qordoba\Exception\DocumentException
     */
    public function testDocumentUpdate()
    {
        $tag = sprintf('%d', Factory::create()->randomDigit);
        $updateTag = sprintf('Updated from v.%s', $tag);
        $filename = $this->createFileName();
        $sectionName = Factory::create()->word;

        $document = $this->createDocument();
        $document->setTag($tag);
        $document->setName($filename);

        $section = $document->addSection($sectionName);

        $this->assertTrue($section->addTranslationString(Factory::create()->word, Factory::create()->sentence()));
        $this->assertTrue($section->addTranslationString(Factory::create()->word, Factory::create()->sentence()));
        $this->assertTrue($section->addTranslationString(Factory::create()->word, Factory::create()->sentence()));

        $this->assertInternalType(IsType::TYPE_INT, $document->createTranslation());
        $this->assertEquals(4, $document->getConnection()->getRequestCount());

        foreach ($document->getConnection()->getRequests() as $key => $response) {
            $this->assertInstanceOf('GuzzleHttp\Psr7\Response', $response);
            $this->assertEquals(200, $response->getStatusCode());
        }

        $languages = (array)$document->getProjectLanguages();
        $language = array_shift($languages);

        $this->assertObjectHasAttribute('id', $language);
        $this->assertObjectHasAttribute('name', $language);
        $this->assertObjectHasAttribute('code', $language);
        $this->assertObjectHasAttribute('direction', $language);
        $this->assertObjectHasAttribute('meta', $language);
        $this->assertObjectHasAttribute('tm_id', $language);
        $this->assertObjectHasAttribute('glossary_id', $language);
        $this->assertRegExp('/[a-z]{2}-[a-z]{2}/', $language->code);

        $submittedDocs = $document->getConnection()
            ->fetchProjectSearch($this->projectId, $language->id, $filename, 'none');


        $this->assertInternalType(IsType::TYPE_ARRAY, $submittedDocs->pages);
        $this->assertTrue(0 < count($submittedDocs->pages));

        $this->assertInternalType(IsType::TYPE_OBJECT, $submittedDocs->meta);

        $this->assertObjectHasAttribute('paging', $submittedDocs->meta);
        $this->assertObjectHasAttribute('total_results', $submittedDocs->meta->paging);
        $this->assertObjectHasAttribute('total_enabled', $submittedDocs->meta->paging);
        $this->assertInternalType(IsType::TYPE_INT, $submittedDocs->meta->paging->total_enabled);
        $this->assertInternalType(IsType::TYPE_INT, $submittedDocs->meta->paging->total_results);

        $document = $this->createDocument();
        $document->setName($filename);
        $document->setTag($updateTag);

        $section = $document->addSection($sectionName);

        $this->assertTrue(
            $section->addTranslationString(
                Factory::create()->word,
                sprintf('Hello! I\'m an new one \'%s\'', Factory::create()->sentence())
            )
        );

        $this->assertInternalType(IsType::TYPE_INT, $document->updateTranslation());

        $submittedDocs = $document->getConnection()
            ->fetchProjectSearch($this->projectId, $language->id, $filename, 'none');

        $this->assertInternalType(IsType::TYPE_ARRAY, $submittedDocs->pages);
        $this->assertTrue(0 < count($submittedDocs->pages));

        $this->assertInternalType(IsType::TYPE_OBJECT, $submittedDocs->meta);

        $this->assertObjectHasAttribute('paging', $submittedDocs->meta);
        $this->assertObjectHasAttribute('total_results', $submittedDocs->meta->paging);
        $this->assertObjectHasAttribute('total_enabled', $submittedDocs->meta->paging);
        $this->assertInternalType(IsType::TYPE_INT, $submittedDocs->meta->paging->total_enabled);
        $this->assertInternalType(IsType::TYPE_INT, $submittedDocs->meta->paging->total_results);

        $latestDocumentVersion = array_shift($submittedDocs->pages);

        $this->assertEquals($updateTag, $latestDocumentVersion->version_tag);
        $this->assertEquals($latestDocumentVersion->url, sprintf('%s.json', $filename));
    }


    /**
     *
     */
    public function testDocumentCheck()
    {
        $languages = [];
        $document = $this->createDocument();
        $document->setName('Translation Test');
        $projectLanguages = $document->getProjectLanguages();
        $translations = (array)$document->checkTranslation();

        foreach ($projectLanguages as $key => $language) {
            $this->assertObjectHasAttribute('id', $language);
            $this->assertObjectHasAttribute('name', $language);
            $this->assertObjectHasAttribute('code', $language);
            $this->assertObjectHasAttribute('direction', $language);
            $this->assertObjectHasAttribute('meta', $language);
            $this->assertObjectHasAttribute('tm_id', $language);
            $this->assertObjectHasAttribute('glossary_id', $language);
            $this->assertRegExp('/[a-z]{2}-[a-z]{2}/', $language->code);
            array_push($languages, $language->code);
        }

        foreach ($languages as $key => $code) {
            $this->assertArrayHasKey($code, $translations);
            $translation = (array)$document->checkTranslation($code);
            $this->assertArrayHasKey($code, $translation);
        }
    }


    /**
     *
     */
    public function testDocumentFetch()
    {
        $document = $this->createDocument();
        $document->setType(self::DOCUMENT_TYPE_HTML);
        $document->setTag(self::STANDARD_DOCUMENT_TAG);
        $document->setName(self::STANDARD_DOCUMENT_NAME);
        $projectLanguages = $document->getProjectLanguages();
        $translation = $document->fetchTranslation();
        $projectLanguageCodes = [];

        foreach ($projectLanguages as $key => $language) {
            $this->assertObjectHasAttribute('id', $language);
            $this->assertObjectHasAttribute('name', $language);
            $this->assertObjectHasAttribute('code', $language);
            $this->assertObjectHasAttribute('direction', $language);
            $this->assertObjectHasAttribute('meta', $language);
            $this->assertObjectHasAttribute('tm_id', $language);
            $this->assertObjectHasAttribute('glossary_id', $language);
            $this->assertRegExp('/[a-z]{2}-[a-z]{2}/', $language->code);
            array_push($projectLanguageCodes, $language->code);
        }

        foreach ($projectLanguageCodes as $code) {
            $this->assertRegExp('/[a-z]{2}-[a-z]{2}/', $code);
            $this->assertArrayHasKey($code, $translation);
        }
    }
}
