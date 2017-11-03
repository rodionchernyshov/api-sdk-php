<?php

namespace Qordoba;

use Faker\Factory;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\TestCase;

/**
 * Class UploadTest
 * @package Qordoba
 */
class UploadTest extends TestCase
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
     * @throws Exception\UploadException
     */
    public function testUpload()
    {
        $connection = new Connection($this->apiUrl, $this->login, $this->password);
        $upload = new Upload($connection, $this->projectId, $this->organizationId);

        $fileName = Factory::create()->word;
        $upload->setFileName($fileName);
        $this->assertEquals($fileName, $upload->getFileName());
    }

    /**
     * @throws Exception\UploadException
     */
    public function testSendFile()
    {
        $connection = new Connection($this->apiUrl, $this->login, $this->password);
        $upload = new Upload($connection, $this->projectId, $this->organizationId);

        $fileName = Factory::create()->word;
        $upload->setFileName($fileName);
        $this->assertInternalType(
            IsType::TYPE_STRING,
            $upload->sendFile(Factory::create()->word, Factory::create()->randomDigit)
        );
    }

    /**
     *
     */
    public function testUpdateSendFile()
    {
        $connection = new Connection($this->apiUrl, $this->login, $this->password);
        $upload = new Upload($connection, $this->projectId, $this->organizationId);
        $this->assertInternalType(
            IsType::TYPE_STRING,
            $upload->sendFile(Factory::create()->word . '.' . self::DOCUMENT_TYPE_HTML, Factory::create()->randomDigit)
        );
        $this->assertInternalType(IsType::TYPE_INT, $upload->appendToProject());
    }
}
