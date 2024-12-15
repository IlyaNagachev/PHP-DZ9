<?php


require_once('./vendor/autoload.php');

use Geekbrains\Application1\Application\Application;

try{
    $app = new Application();

    $result = $app->runApp();

    echo $result;
}

catch (Exception $e) {
    file_put_contents(
        'error_log.txt', 
        "[" . date('Y-m-d H:i:s') . "] " . $e->getMessage() . "\n", 
        FILE_APPEND
    );
    echo "Произошла ошибка: " . $e->getMessage();
}

