<?php
require_once "commands.php";
require_once "zmsg.php";

class Ventilator
{
    private $context;
    private $socket;
    private $poll;
    private $verbose;

// Heartbeat management
    private $heartbeat_at; // When to send HEARTBEAT
    private $heartbeat; // Heartbeat delay, msecs
    private $triesLeft;
    private $tries = 3;

    //workers
    private $workers;
    private $workersFree;

    public function __construct($verbose = false, $heartbeat = 2500)
    {
        $this->context = new ZMQContext();
        $this->socket = $this->context->getSocket(ZMQ::SOCKET_ROUTER);
        $this->socket->setSockOpt(ZMQ::SOCKOPT_LINGER, 0);
        $this->poll = new ZMQPoll();

        $this->verbose = $verbose;
        $this->heartbeat = $heartbeat;
        $this->workers = new SplObjectStorage();
        $this->workersFree = new SplQueue();

    }

    public function bind($endpoint)
    {
        $this->socket->bind($endpoint);
        $this->poll->add($this->socket, ZMQ::POLL_IN);
        if ($this->verbose) {
            printf("I: Broker is active at %s %s", $endpoint, PHP_EOL);
        }

    }

    public function listen()
    {
        $read = $write = array();
        while (1) {
            $events = $this->poll->poll($read, $write, $this->heartbeat);
            if ($events > 0) {
                $zmsg = new Zmsg($this->socket);
                $zmsg->recv();
                if ($this->verbose) {
                    echo "I: received message:", PHP_EOL, $zmsg->__toString(), PHP_EOL;
                }

                $sender = $zmsg->pop();
                $empty = $zmsg->pop();
                $header = $zmsg->pop();
                if ($header == W_WORKER) {
                    $this->process($sender, $zmsg);
                } else {
                    echo "E: invalid header `$header` in  message", PHP_EOL, $zmsg->__toString(), PHP_EOL, PHP_EOL;
                }
            }

//            if (microtime(true) > $this->heartbeat_at) {
//                $this->purge_workers();
//                foreach ($this->workers as $worker) {
//                    $this->worker_send($worker, MDPW_HEARTBEAT, NULL, NULL);
//                }
//                $this->heartbeat_at = microtime(true) + (HEARTBEAT_INTERVAL / 1000);
//            }
        }
    }

    private function process($sender, $zmsg)
    {
        $command = $zmsg->pop();
        switch ($command) {
            case W_READY:
                break;
            case W_HEARTBEAT:
                break;
            case W_RESPONSE:
                break;
            default:
                echo "I: Unsupported command `$command`.", PHP_EOL;
                echo $zmsg->__toString(), PHP_EOL, PHP_EOL;

        }
    }

}

class VWorker
{
    public $address;
    public $expiry;

    public function __construct($address)
    {
        $this->address = $address;
    }
}
