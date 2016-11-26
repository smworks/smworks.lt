<?php

class GCM {

    public static function sendMessage($gcmTokenArray, $message) {
        $msg = array('message' => $message);
        return self::sendGoogleCloudMessage($msg, $gcmTokenArray);
    }

    public static function sendGoogleCloudMessage($data, $ids) {
        // Insert real GCM API key from Google APIs Console
        // https://code.google.com/apis/console/
        $apiKey = 'AIzaSyAXaeyQYVBtDWv5SJFTyYf5edTF58PI520';
        // Define URL to GCM endpoint
        $url = 'https://gcm-http.googleapis.com/gcm/send';
        // Set GCM post variables (device IDs and push payload)
        $post = array(
            'registration_ids' => $ids,
            'data' => $data,
        );
        // Set CURL request headers (authentication and type)
        $headers = array(
            'Authorization: key=' . $apiKey,
            'Content-Type: application/json'
        );
        // Initialize curl handle
        $ch = curl_init();
        // Set URL to GCM endpoint
        curl_setopt($ch, CURLOPT_URL, $url);
        // Set request method to POST
        curl_setopt($ch, CURLOPT_POST, true);
        // Set our custom headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // Get the response back as string instead of printing it
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Set JSON post data
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
        // Actually send the push
        $result = curl_exec($ch);
        // Error handling
        if (curl_errno($ch)) {
            $result .= 'GCM error: ' . curl_error($ch);
        }
        // Close curl handle
        curl_close($ch);
        // Debug GCM response
        return $result;
    }
}