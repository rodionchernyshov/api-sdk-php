<?php
/**
 * @category Qordoba PHP SDK
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

use Faker\Factory;
use Qordoba\Interfaces\DocumentInterface;

require __DIR__ . '/../vendor/autoload.php';
define('API_URL', 'https://app.qordoba.com/api');
define('TARGET_LANGUAGE_CODE', 'ar-sa');
// Document mock will be sent to Qorodba Application via REST API
$documentToTranslate = [
    'content' => [
        Factory::create()->text(),
        Factory::create()->text(),
        Factory::create()->text(),
        Factory::create()->text(),
    ]
];
// Initiate connection to Qorodba Application via REST API
$translationDocument = new Qordoba\Document(
    API_URL, // Qordoba Application API url
    '**********@mail.com', // Qordoba Application user login
    '******', // Qordoba Application user password
    0000, // Workspace ID
    0000 // Organizaiotn ID
);
// Set document name that will be downloaded from Qorodba Application via REST API
$translationDocument->setName('translation-document-unique-id');
// Set document version that will be downloaded from Qorodba Application via REST API
$translationDocument->setTag(DocumentInterface::DEFAULT_TAG_NAME);
// Request document translation from Qorodba Application via REST API
var_dump($translationDocument->fetchTranslation(TARGET_LANGUAGE_CODE));
exit(0);