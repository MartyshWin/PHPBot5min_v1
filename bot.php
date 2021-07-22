<?php

    require "TelegramAPI.php";
    require "config.php";
    require "function.php";


    $TelegramAPI = new TelegramAPI($token);
    $data = file_get_contents("php://input");
    $data = json_decode($data, TRUE);
    $data = $data['callback_query'] ? $data['callback_query'] : $data['message'];
    FilePutLog($data);
    
    $TelegramAPI->sendMessage($data['chat']['id'], $data['text']);