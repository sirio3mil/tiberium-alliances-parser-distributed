<?php
require_once "commands.php";
require_once "zmsg.php";


class Worker
{
    private $context;
    private $broker;
    private $socket;
    private $poll;
    private $verbose;

// Heartbeat management
    private $heartbeat_at; // When to send HEARTBEAT
    private $heartbeat; // Heartbeat delay, msecs
    private $reconnect;
    private $triesLeft;
    private $tries = 3;

    public function __construct($broker, $verbose = false, $heartbeat = 2500, $reconnect = 5000)


    {
        $this->context = new ZMQContext();
        $this->poll = new ZMQPoll();

        $this->broker = $broker;
        $this->verbose = $verbose;
        $this->heartbeat = $heartbeat;
        $this->reconnect = $reconnect;
        $this->connect();
    }

    private function connect()
    {
        if ($this->socket) {
            $this->poll->remove($this->socket);
            unset($this->socket);
        }

        $this->socket = $this->context->getSocket(ZMQ::SOCKET_DEALER);
        $this->socket->connect($this->broker);
        $this->poll->add($this->socket, ZMQ::POLL_IN);

        if ($this->verbose) {
            printf("I: connecting to broker at %s... %s", $this->broker, PHP_EOL);
        }
        $this->send(W_READY);
        $this->triesLeft = $this->tries;
        $this->heartbeat_at = microtime(true) + ($this->heartbeat / 1000);
    }

    public function send($command, $msg = null)
    {
        if (!$msg) {
            $msg = new Zmsg();
        }
        $msg->push($command);
        $msg->push("");
        if ($this->verbose) {
            printf("I: sending %s to broker %s", $command, PHP_EOL);
            echo $msg->__toString(), PHP_EOL;
        }
        $msg->set_socket($this->socket)->send();
    }

    public function recv()
    {
        $read = $write = array();
        while (true) {
            $events = $this->poll->poll($read, $write, $this->heartbeat);
            if ($events) {
                $zmsg = new Zmsg($this->socket);
                $zmsg->recv();
                if ($this->verbose) {
                    echo "I: received message from broker:", PHP_EOL;
                    echo $zmsg->__toString(), PHP_EOL;
                }
                $this->triesLeft = $this->tries;

                $zmsg->pop();
                $command = $zmsg->pop();
                if ($command == W_HEARTBEAT) {

                } elseif ($command == W_REQUEST) {
                    return $zmsg;
                } else {
                    echo "I: Unsupported command `$command`, reconnect.", PHP_EOL, PHP_EOL;
                    $this->connect();
                }
            } elseif (--$this->triesLeft == 0) {
                if ($this->verbose) {
                    echo "I: disconnected from broker - retrying... ", PHP_EOL;
                    usleep($this->reconnect * 1000);
                    $this->connect();
                }
            }

            if (microtime(true) > $this->heartbeat_at) {
                $this->send(W_HEARTBEAT);
                $this->heartbeat_at = microtime(true) + ($this->heartbeat / 1000);
            }
        }
    }

}


$wrk = new Worker("tcp://localhost:5555", true);
while (1) {
    $msg = $wrk->recv();

    $wrk->send(W_RESPONSE);
}


