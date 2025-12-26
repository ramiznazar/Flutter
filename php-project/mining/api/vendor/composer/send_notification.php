<?php

require 'vendor/autoload.php';
//require __DIR__ . '/vendor/autoload.php';

use onesignal\client\api\DefaultApi;
use onesignal\client\Configuration;
use onesignal\client\model\Notification;
use onesignal\client\model\StringMap;
use GuzzleHttp\Client;

const APP_ID = 'c207e2a6-4878-4b50-a735-27166cd6c7d1';
const APP_KEY_TOKEN = 'OTgyMjQ0YjYtMGI0MS00Zjk2LWFjYjctMTNkMDIzODljNzkz';
const USER_KEY_TOKEN = 'YThlN2VlMGEtZGIyMi00YmM5LWI1YTgtNDcwODk1OGJjZjkz';

$config = Configuration::getDefaultConfiguration()
    ->setAppKeyToken(APP_KEY_TOKEN)
    ->setUserKeyToken(USER_KEY_TOKEN);

$apiInstance = new DefaultApi(
    new Client(),
    $config
);

function createNotification($title, $message, $userTagKey, $userTagValue): Notification {
    $titleContent = new StringMap();
    $titleContent->setEn($title);
    $messageContent = new StringMap();
    $messageContent->setEn($message);

    $notification = new Notification();
    $notification->setAppId(APP_ID);
    $notification->setHeadings($titleContent);
    $notification->setContents($messageContent);
    $notification->setSmallIcon('ic_launcher');
    $notification->setFilters([['field' => 'tag', 'key' => $userTagKey, 'relation' => '=', 'value' => $userTagValue]]);

    return $notification;
}

$inputJson = file_get_contents('php://input');
$inputData = json_decode($inputJson, true);

if ($inputData === null) {
    $response = array('error' => 'Error parsing JSON data');
    echo json_encode($response);
    exit;
}

$title = $inputData['title'];
$message = $inputData['message'];
$userTagKey = $inputData['userId'];
$userTagValue = $inputData['userId'];

$notification = createNotification($title, $message, $userTagKey, $userTagValue);

try {
    $result = $apiInstance->createNotification($notification);
    $response = array('message' => $result);
    echo json_encode($response);
} catch (Exception $e) {
    $response = array('error' => 'Exception when calling DefaultApi->createNotification', 'message' => $e->getMessage());
    echo json_encode($response);
}
