<?php

$client = new GearmanClient();
$client->addServer();

print "sending\n";

$job = $client->doBackground("ping", "noop");

print "before the 'received' message\n";

do {
    sleep(1);

    print "checking\n";

    $status = $client->jobStatus($job);

    if ($status[0] === false) {
        break;
    }
} while (true);