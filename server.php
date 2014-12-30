<?php

require "vendor/autoload.php";

use Formativ\Server;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

$custom = new Server();

$server = IoServer::factory(
    $http = new HttpServer(
        new WsServer(
            $custom
        )
    ),
    8080,
    "127.0.0.1"
);

$custom->setLoop($server->loop);

$server->run();