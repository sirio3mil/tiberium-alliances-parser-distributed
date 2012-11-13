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
    private $heartbeatAt; // When to send HEARTBEAT
    private $heartbeatDelay; // Heartbeat delay, msecs
    private $heartbeatMaxFails = 3;

    //workers
    private $workers;
    private $workersFree;

    public function __construct($verbose = false, $heartbeatDelay = 2500)
    {
        $this->context = new ZMQContext();
        $this->socket = $this->context->getSocket(ZMQ::SOCKET_ROUTER);
        $this->socket->setSockOpt(ZMQ::SOCKOPT_LINGER, 0);
        $this->poll = new ZMQPoll();

        $this->verbose = $verbose;
        $this->heartbeatDelay = $heartbeatDelay;
        $this->workers = array();
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
            $events = $this->poll->poll($read, $write, $this->heartbeatDelay);
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

            $this->purgeWorkers();
            $this->sendHeartbeats();
        }
    }

    private function sendHeartbeats()
    {
        if (microtime(true) > $this->heartbeatAt) {
            if ($this->verbose) {
                echo "I: send heartbeats to {$this->workersFree->count()} workers", PHP_EOL;
            }
            foreach ($this->workersFree as $worker) {
                $this->workerSend($worker, W_HEARTBEAT);
            }
            $this->heartbeatAt = microtime(true) + (HEARTBEAT_INTERVAL / 1000);
        }
    }

    private function process($sender, $zmsg)
    {
        $command = $zmsg->pop();
        switch ($command) {
            case W_READY:
                if (!$this->hasWorker($sender)) {
                    $this->addWorker($sender);
                } else {
                    echo "disconnect!", PHP_EOL;
                }
                break;
            case W_HEARTBEAT:
                if ($this->hasWorker($sender)) {
                    $this->live($this->workers[$sender]);
                } else {
                    echo "disconnect!", PHP_EOL;
                }
                break;
            case W_RESPONSE:
                if ($this->hasWorker($sender)) {
                    $this->free($this->workers[$sender]);
                } else {
                    echo "disconnect!", PHP_EOL;
                }
                break;
            default:
                echo "I: Unsupported command `$command`.", PHP_EOL;
                echo $zmsg->__toString(), PHP_EOL, PHP_EOL;

        }
    }

    private function addWorker($address)
    {
        if ($this->verbose) {
            echo "I: add new worker:", PHP_EOL;
        }
        $worker = new VWorker($address);
        $this->workers[$address] = $worker;
        $this->free($worker);
        return $worker;
    }

    private function free($worker)
    {
        $this->workersFree->enqueue($worker);
        $this->live($worker);
    }

    private function live(VWorker $worker)
    {
        $worker->aliveFor($this->heartbeatMaxFails * $this->heartbeatDelay);
    }

    private function hasWorker($address)
    {
        return isset($this->workers[$address]);
    }

    private function workerSend(VWorker $worker, $command, $zmsg = null)
    {
        $zmsg = $zmsg ? $zmsg : new Zmsg();
        $zmsg->push($command);
        $zmsg->push(W_WORKER);
        $zmsg->wrap($worker->address, "");
        if ($this->verbose) {
            printf("I: sending `%s` to worker %s", $command, PHP_EOL);
            echo $zmsg->__toString(), PHP_EOL, PHP_EOL;
        }
        $zmsg->set_socket($this->socket)->send();
    }

    private function purgeWorkers()
    {
        foreach ($this->workersFree as $worker) {
            if ($worker->expiry < microtime(1)) {
                $this->deleteWorker($worker);
            } else {
                break;
            }
        }

    }

    private function deleteWorker(VWorker $worker, $disconnect = false)
    {
        if ($this->verbose) {
            echo "I: remove worker `$worker->address` " . ($disconnect ? "disconnect" : ""), PHP_EOL;
        }
        if ($disconnect) {
            $this->workerSend($worker, W_DISCONNECT);
        }
        unset($this->workers[$worker->address]);
        for ($i = 0; $i < $this->workersFree->count(); $i++) {
            if ($worker == $this->workersFree[$i]) {
                $this->workersFree->offsetUnset($i);
                break;
            }
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

    public function aliveFor($time)
    {
        echo "alivefor + $time",PHP_EOL;
        $this->expiry = microtime(1) + $time / 1000;
    }
}
