<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    public function sendNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'message' => 'required|string',
            'userId' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Error parsing JSON data'
            ], 400);
        }

        // OneSignal configuration
        $appId = 'c207e2a6-4878-4b50-a735-27166cd6c7d1';
        $appKeyToken = 'OTgyMjQ0YjYtMGI0MS00Zjk2LWFjYjctMTNkMDIzODljNzkz';
        $userKeyToken = 'YThlN2VlMGEtZGIyMi00YmM5LWI1YTgtNDcwODk1OGJjZjkz';

        // Prepare notification data
        $notificationData = [
            'app_id' => $appId,
            'headings' => ['en' => $request->title],
            'contents' => ['en' => $request->message],
            'small_icon' => 'ic_launcher',
            'filters' => [
                [
                    'field' => 'tag',
                    'key' => $request->userId,
                    'relation' => '=',
                    'value' => $request->userId
                ]
            ]
        ];

        // Send notification via OneSignal REST API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://onesignal.com/api/v1/notifications');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Basic ' . $appKeyToken
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($notificationData));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return response()->json([
                'error' => 'Exception when calling OneSignal API',
                'message' => $error
            ], 500);
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            $result = json_decode($response, true);
            return response()->json([
                'message' => $result
            ]);
        } else {
            return response()->json([
                'error' => 'Exception when calling OneSignal API',
                'message' => $response
            ], $httpCode);
        }
    }
}


