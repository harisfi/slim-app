<?php

use Slim\App;

return function (App $app) {
    $container = $app->getContainer();

    // view renderer
    $container['renderer'] = function ($c) {
        $settings = $c->get('settings')['renderer'];
        return new \Slim\Views\PhpRenderer($settings['template_path']);
    };

    // monolog
    $container['logger'] = function ($c) {
        $settings = $c->get('settings')['logger'];
        $logger = new \Monolog\Logger($settings['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
        return $logger;
    };

    // database
    $container['db'] = function ($c){
        $settings = $c->get('settings')['db'];
        $server = $settings['driver'].":host=".$settings['host'].";dbname=".$settings['dbname'];
        //$conn = new PDO($server, $settings["user"], $settings["pass"]);
        $host = "127.0.0.1";
        $user = "root";
        $pass = "";
        $dbname = "tokokopi";
        $charset = 'utf8mb4'; // Always set charset for database
        $port = '3306';
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $conn = new PDO("mysql:host=$host;dbname=$dbname;port=$port;charset=$charset", $user, $pass, $options);
        //$conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
        //$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    };
};
