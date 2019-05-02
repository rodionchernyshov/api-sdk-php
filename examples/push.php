<?php
/**
 * @category Qordoba PHP SDK
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */
require __DIR__ . '/vendor/autoload.php';
define('API_URL', 'https://app.qordoba.com/api');
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
$translationDocument->setName('test-issue-7');
$translationDocument->setTag('issue-2');
foreach ($documentToTranslate['content'] as $key => $item) {
    $translationDocument->addTranslationString($key, $item);
}
$translationDocument->createTranslation();
exit(0);