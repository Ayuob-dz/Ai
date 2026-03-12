<?php
if (!defined('ACCESS_ALLOWED')) {
    die('Access Denied');
}

function sendTelegramMessage($message, $chat_id = null) {
    if (!$chat_id) {
        $chat_id = TELEGRAM_CHAT_ID_ADMIN;
    }
    $token = TELEGRAM_BOT_TOKEN;
    $url = "https://api.telegram.org/bot$token/sendMessage";
    
    $data = [
        'chat_id' => $chat_id,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];
    
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);
    return $response;
}
?>
