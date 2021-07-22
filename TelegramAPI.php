<?php
	
	/**
	 * summary
	 */
	class TelegramAPI
	{
	    /**
	     * summary
	     */
	    public $token;
	    public $method;

	    public function __construct($token)
	    {
	        $this->token = $token;
	    }

	    public function SendPro($method, $send, $headers = [])
	    {
	        $curl = curl_init();
	        $this->$method = $method;
	        curl_setopt($curl, CURLOPT_URL, 'https://api.telegram.org/bot'.$this->token.'/'.$this->$method);
	        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	        curl_setopt($curl, CURLOPT_HEADER, 0);
	        // Указываем, что у нас POST запрос
	        curl_setopt($curl, CURLOPT_POST, 1);
	        // Добавляем переменные
	        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($send));
	        curl_setopt($curl, CURLOPT_HTTPHEADER, array_merge(array('Content-Type: application/json'), $headers));
	        $result = curl_exec($curl);
	        curl_close($curl);
	        return $result;
	    }

	    public function SendFile($method, $data, $file)
	    {

	        $curl = curl_init();
	        $this->$method = $method;
	        curl_setopt($curl, CURLOPT_URL, 'https://api.telegram.org/bot'.$this->token.'/'.$this->$method);
	        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	        // curl_setopt($curl, CURLOPT_HEADER, 0);
	        // Указываем, что у нас POST запрос
	        curl_setopt($curl, CURLOPT_POST, 1);
	        // Добавляем переменные
	        // curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
	        // $data = json_encode($data);
	        $data += array($file['name'] => new \CurlFile($file['path']));
	        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data'));
	        $result = curl_exec($curl);
	        curl_close($curl);
	        return $result;
	    }

	   	public function setWebhook($url)
	    {
	        $result = $this->SendPro('setWebhook', ['url' => $url]);
	        return $result;
	    }

	    public function getMe()
	    {
	        $result = $this->SendPro('getMe', '');
	        return $result;
	    }

	    public function sendMessage($chat_id, $text, $keyboard = [], $reply_to_message_id = '')
	    {
	    	$dataArr = array(
		        'chat_id' => $chat_id,
		        'text' => $text,
		    );
		    if (!empty($keyboard)){
	    		$dataArr += ['reply_markup' => $keyboard];
	    	}
		    if (count($reply_to_message_id) > 0){
	    		$dataArr += ['reply_to_message_id' => $reply_to_message_id];
	    	}
	        $result = $this->SendPro('sendMessage', $dataArr);
	        return $result;
	    }

	    public function sendPhoto($chat_id, $url, $caption = '', $keyboard = [], $reply_to_message_id = '', $disable_notification = 'False')
	    {
	    	/*
	    		$chat_id - Идентификатор чата
	    		$url - ['url'] Адрес к картинке; ['type'] если file, то он должен хранится локально, а отправлять будет с multipart, иначе будет отправлен url на img
	    		$caption - Описание к фотографии, текст
	    		$keyboard - Массив с клавиатурой
	    		$reply_to_message_id - id сообщения, на которое вы отвечаете
	    		$disable_notification - Отправить без уведомления (True;False)

	    		Пример использования:
	    			$TelegramApi->sendPhoto(id, ['url' => $urlPhoto, 'type' => 'file']);
	    			$TelegramApi->sendPhoto(id, ['url' => $urlPhoto, 'type' => 'file'], 'text is PHP', $keyboard, $reply_to_message_id, $disable_notification);
	    	*/
	    	$dataArr = array(
		        'chat_id' => $chat_id,
		        'disable_notification' => $disable_notification,
		    );
		    if (count($caption) > 0){
	    		$dataArr += ['caption' => $caption];
	    	}
	    	if (!empty($keyboard)){
	    		$dataArr += ['reply_markup' => $keyboard];
	    	}
	    	if (count($reply_to_message_id) > 0){
	    		$dataArr += ['reply_to_message_id' => $reply_to_message_id];
	    	}
	    	
	        $result = $this->SendPro('sendPhoto', $dataArr);
	        if ($url['type'] == 'file') {
	        	$result = $this->SendFile('sendPhoto', $dataArr, ['path' => $url['url'], 'name' => 'photo']);
	        	unset($dataArr);
	        	$result = json_decode($result, TRUE);
	        	$dataArr = ['chat_id' =>  $result['result']['chat']['id'], 'message_id' => $result['result']['message_id']];
	        	if (!empty($keyboard))
	    			$dataArr += ['reply_markup' => $keyboard];
	        	$result = $this->SendPro('editMessageMedia', $dataArr);
	        }else {
	        	$dataArr += ['photo' => $url['url']];
	        	$result = $this->SendPro('sendPhoto', $dataArr);
	        }
	        return $result;
	    }

	    public function sendVideoNote($chat_id, $url, $thumb = '', $keyboard = [], $reply_to_message_id = '', $length = '', $disable_notification = 'False')
	    {
	    	/*
	    		$chat_id - Идентификатор чата
	    		$url - ['url'] Адрес к видео файлу; ['type'] если file, то он должен хранится локально, а отправлять будет с multipart, иначе будет отправлен url на video
	    		$thumb - превью для видео, если не указывать, то телеграм сам его сделает
	    		$keyboard - Массив с клавиатурой
	    		$reply_to_message_id - id сообщения, на которое вы отвечаете
	    		$length - Ширина и высота видео, т.е. диаметр видео сообщения
	    		$disable_notification - Отправить без уведомления (True;False)

	    		Пример использования:
	    			$TelegramApi->sendVideoNote('719683512', ['url' => $urlVideoNote, 'type' => 'file']);
	    			$TelegramApi->sendVideoNote('719683512', ['url' => $urlVideoNote, 'type' => 'file'], 'text is PHP', $keyboard, $reply_to_message_id, $disable_notification);
	    	*/
	    	$dataArr = array(
		        'chat_id' => $chat_id,
		        'length' => '360',
		        'disable_notification' => $disable_notification,
		    );
		    // if (count(($length) > 0){
	    	// 	$dataArr += ['length' => $length];
	    	// }
	    	if (!empty($thumb)){
	    		$dataArr += ['thumb' => $thumb];
	    	}
	    	if (!empty($keyboard)){
	    		$dataArr += ['reply_markup' => $keyboard];
	    	}
	    	if (count($reply_to_message_id) > 0){
	    		$dataArr += ['reply_to_message_id' => $reply_to_message_id];
	    	}
	    	if ($url['type'] == 'file') {
	    		$result = $this->SendFile('sendVideoNote', $dataArr, ['path' => $url['url'], 'name' => 'video_note']);
	        	unset($dataArr);
	        	$result = json_decode($result, TRUE);
	        	$dataArr = ['chat_id' =>  $result['result']['chat']['id'], 'message_id' => $result['result']['message_id']];
	        	if (!empty($keyboard))
	    			$dataArr += ['reply_markup' => $keyboard];
	    	
	        	$result = $this->SendPro('editMessageMedia', $dataArr);
	        }else {
	        	$dataArr += ['video_note' => $url['url']];
	        	$result = $this->SendPro('sendVideoNote', $dataArr);
	        }

	        return $result;
	    }

	    public function sendVoice($chat_id, $url, $caption = '', $keyboard = [], $reply_to_message_id = '', $disable_notification = 'False')
	    {
	    	/*
	    		$chat_id - Идентификатор чата
	    		$url - ['url'] Адрес к видео файлу; ['type'] если file, то он должен хранится локально, а отправлять будет с multipart, иначе будет отправлен url на video
	    		$caption - текст, который будет идти с голосовым
	    		$keyboard - Массив с клавиатурой
	    		$reply_to_message_id - id сообщения, на которое вы отвечаете
	    		$disable_notification - Отправить без уведомления (True;False)

	    		Пример использования:
	    			$TelegramApi->sendVoice('719683512', ['url' => $urlVideoNote, 'type' => 'file']);
	    			$TelegramApi->sendVoice('719683512', ['url' => $urlVideoNote, 'type' => 'file'], 'text is PHP', $keyboard, $reply_to_message_id, $disable_notification);
	    	*/
	    	$dataArr = array(
		        'chat_id' => $chat_id,
		        'disable_notification' => $disable_notification,
		        'duration' => '4s',
		    );
	    	if (!empty($caption)){
	    		$dataArr += ['caption' => $caption];
	    	}
	    	if (!empty($keyboard))
	    		$dataArr += ['reply_markup' => $keyboard];

	    	if (count($reply_to_message_id) > 0){
	    		$dataArr += ['reply_to_message_id' => $reply_to_message_id];
	    	}
	    	
	        if ($url['type'] == 'file') {
	    		$result = $this->SendFile('sendVoice', $dataArr, ['path' => $url['url'], 'name' => 'voice']);
	        	unset($dataArr);
	        	if (!empty($keyboard)) {
	        		$result = json_decode($result, TRUE);
	        		$dataArr = ['chat_id' =>  $result['result']['chat']['id'], 'message_id' => $result['result']['message_id'], 'reply_markup' => $keyboard];
	    			$result = $this->SendPro('editMessageMedia', $dataArr);
	        	}
	        }else {
	        	$dataArr += ['voice' => $url['url']];
	        	$result = $this->SendPro('sendVoice', $dataArr);
	        }
	        return $result;
	    }

	    public function sendAudio($chat_id, $url, $caption = '', $keyboard = [], $reply_to_message_id = '', $length = '')
	    {
	    	$dataArr = array(
		        'chat_id' => $chat_id,
		    );
		    // if (count(($length) > 0){
	    	// 	$dataArr += ['length' => $length];
	    	// }
	    	if (!empty($caption)){
	    		$dataArr += ['caption' => $caption];
	    	}
	    	if (!empty($keyboard)){
	    		$dataArr += ['reply_markup' => $keyboard];
	    	}
	    	if (count($reply_to_message_id) > 0){
	    		$dataArr += ['reply_to_message_id' => $reply_to_message_id];
	    	}
	    	
	        if ($url['type'] == 'file')
		        $result = $this->SendFile('sendAudio', $dataArr, ['path' => $url, 'name' => 'voice']);
	        else {
	        	$dataArr += ['voice' => $url['url']];
	        	$result = $this->SendPro('sendAudio', $dataArr);
	        }
	        return $result;
	    }
	    
	    public function copyMessage($chat_id, $from_id, $message_id, $caption = '', $keyboard = [], $reply_to_message_id = '', $disable_notification = '')
	    {
	    	$dataArr = array(
		        'chat_id' => $chat_id,
		        'from_chat_id' =>  $from_id,
		        'message_id' => $message_id,
		    );

	    	if (!empty($caption)){
	    		$dataArr += ['caption' => $caption];
	    	}
	    	if (!empty($keyboard)){
	    		$dataArr += ['reply_markup' => $keyboard];
	    	}
	    	if (count($reply_to_message_id) > 0){
	    		$dataArr += ['reply_to_message_id' => $reply_to_message_id];
	    	}
	    	if (count($disable_notification) > 0){
	    		$dataArr += ['disable_notification' => $disable_notification];
	    	}
	    	
	        $result = $this->SendPro('copyMessage', $dataArr);
	        return $result;
	    }
	}