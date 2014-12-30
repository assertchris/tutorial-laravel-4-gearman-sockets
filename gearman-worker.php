<?php

require "vendor/autoload.php";

use WebSocket\Client;

$worker = new GearmanWorker();
$worker->addServer();

$client = new Client("ws://127.0.0.1:8080");

$worker->addFunction(
    "ping",
    function (GearmanJob $job) use ($client) {
        print "received: {$job->handle()}\n";

        for ($x = 1; $x < 6; $x++) {
            print "ping {$x}\n";
            sleep(1);
        }

        $client->send(
            json_encode(
                [
                    "type" => "worker.complete",
                    "id" => $job->handle(),
                    "value" => "done"
                ]
            )
        );

        print "response: {$client->receive()}\n";

        print "waiting\n";
    }
);

print "waiting\n";

while ($worker->work());