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
    'rodion.chernyshov@easternpeak.com',
    'NeoMacuser571',
    6340, // Workspace ID
    3169 // Organization ID
);
$translationDocument->setName('test-issue-7');
$translationDocument->setTag('issue-1');
foreach ($documentToTranslate['content'] as $key => $item) {
    $translationDocument->addTranslationString($key, $item);
}
$translationDocument->updateTranslation();
exit(0);