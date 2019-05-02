<?php
/**
 * @category Qordoba PHP SDK
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */
require __DIR__ . '/../vendor/autoload.php';
define('API_URL', 'https://app.qordoba.com/api');
define('TARGET_LANGUAGE_CODE', 'ar-sa');

$documentToTranslate = [
    'content' => [
        \Faker\Factory::create()->text(),
        \Faker\Factory::create()->text(),
        \Faker\Factory::create()->text(),
        \Faker\Factory::create()->text(),
    ]
];
$translationDocument = new Qordoba\Document(
    API_URL,
    '**********@mail.com',
    '******',
    0000, // Workspace ID
    0000 // Organizaiotn ID
);
$translationDocument->setName('translation-document-unique-id');
$translationDocument->setTag(\Qordoba\Interfaces\DocumentInterface::DEFAULT_TAG_NAME);
var_dump($translationDocument->fetchTranslation(TARGET_LANGUAGE_CODE));
exit(0);