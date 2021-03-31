<?php

require_once __DIR__.'/vendor/autoload.php';
use Symfony\Component\Dotenv\Dotenv;
use VK\Client\VKApiClient;


$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');


$vk = new VKApiClient('5.130',  VK\Client\Enums\VKLanguage::RUSSIAN);



$usersUidProvider = new \Matucana\VkPhoneParser\UsersUidProvider();

$app = new \Matucana\VkPhoneParser\App($vk, $usersUidProvider);
foreach ($app->run() as $value) {
    echo $value."\n";
}
