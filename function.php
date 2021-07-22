<?php

    function FilePutLog($data, $filename='/input.txt')
    {
        ob_start();
        print_r($data);
        $out = ob_get_clean(); 
        file_put_contents(__DIR__ . $filename, $out."\n", FILE_APPEND);
    }