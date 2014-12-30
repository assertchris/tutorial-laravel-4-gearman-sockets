<?php

namespace Formativ;

use Exception;
use GearmanClient;
use Ratchet\ConnectionInterface as Connection;
use Ratchet\MessageComponentInterface;
use React\EventLoop\LoopInterface;
use SplObjectStorage;

class Server implements MessageComponentInterface
{
    /**
     * @var SplObjectStorage
     */
    protected $connections;

    /**
     * @var GearmanClient
     */
    protected $client;

    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @param LoopInterface $loop
     */
    public function setLoop(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    /**
     * @var array
     */
    protected $values = [];

    public function __construct()
    {
        $this->connections = new SplObjectStorage();

        $this->client = new GearmanClient();
        $this->client->addServer();
    }

    /**
     * @param Connection $connection
     */
    public function onOpen(Connection $connection)
    {
        $this->connections->attach($connection);
    }

    /**
     * @param Connection $connection
     * @param string     $message
     */
    public function onMessage(Connection $connection, $message)
    {
        $message = json_decode($message, true);

        if ($message["type"] === "ping") {
            $job = $this->client->doBackground("ping", "noop");

            $connection->send("before the 'received' message");

            $timer = $this->loop->addPeriodicTimer(
                0.1,
                function () use ($connection, $job, &$timer) {
                    $status = $this->client->jobStatus($job);

                    if ($status[0] === false) {
                        $value = $this->values[$job];

                        $connection->send("job: {$job}");
                        $connection->send("value: {$value}");

                        $timer->cancel();
                    }
                }
            );
        }

        if ($message["type"] === "worker.complete") {
            $this->values[$message["id"]] = $message["value"];
            $connection->send("got it!");
        }
    }

    /**
     * @param Connection $connection
     */
    public function onClose(Connection $connection)
    {
        $this->connections->detach($connection);
    }

    /**
     * @param Connection $connection
     * @param Exception  $exception
     */
    public function onError(Connection $connection, Exception $exception)
    {
        $connection->close();
    }
}