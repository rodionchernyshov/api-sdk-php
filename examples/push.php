<?php
/**
 * @category Qordoba PHP SDK
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

use Faker\Factory;

require __DIR__ . '/vendor/autoload.php';
define('API_URL', 'https://app.qordoba.com/api');
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
    0000, // Qordoba Application Workspace ID
    0000 // Qordoba Application Organization ID
);
// Set document name that will created on Qorodba Application via REST API
$translationDocument->setName('test-issue-7');
// Set document version that will created on Qorodba Application via REST API
$translationDocument->setTag('issue-2');
// Add sections to document that will created on Qorodba Application via REST API
foreach ($documentToTranslate['content'] as $key => $item) {
    $translationDocument->addTranslationString($key, $item);
}
// Request document creation on Qorodba Application via REST API
$translationDocument->createTranslation();
exit(0);