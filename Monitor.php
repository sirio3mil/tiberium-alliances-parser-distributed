<?php
error_reporting(E_ALL);

require_once "Util/Timer.php";
require_once "lib/0MQ/0MQ/Concentrator.php";
require_once "lib/0MQ/0MQ/Publisher.php";

class Monitor
{
    private $events;
    private $eventsSaveInterval;
    private $renderInterval;
    private $lastRender = 0;
    private $verbose;


    public function __construct($eventsSaveInterval = 300000, $renderInterval = 5000, $verbose = false)
    {
        $this->eventsSaveInterval = $eventsSaveInterval;
        $this->renderInterval = $renderInterval;
        $this->verbose = $verbose;
    }

    private function cleanup()
    {
        Timer::set("cleanup");
        $barrier = microtime(1) * 1000 - $this->eventsSaveInterval;
        while (isset($this->events[0]) && $this->events[0]["ts"] < $barrier) {
            array_shift($this->events);
        }

        if ($this->verbose) {
            print_r("Cleanup time: " . Timer::get("cleanup") . PHP_EOL);
        }
    }

    public function addEvent($event)
    {
        $this->events[] = $event;
    }

    public function render()
    {
        if ($this->lastRender + $this->renderInterval > microtime(1) * 1000) {
            return;
        }
        $this->cleanup();
        $this->printData($this->calculate());
    }

    private function calculate()
    {
        $data = array();
        foreach ($this->events as $event) {

        }
        return $data;
    }

    private function printData($data)
    {
        echo PHP_EOL, PHP_EOL, PHP_EOL, PHP_EOL, PHP_EOL, PHP_EOL, PHP_EOL, PHP_EOL, PHP_EOL, PHP_EOL, PHP_EOL, PHP_EOL, PHP_EOL, PHP_EOL;
        echo "Monitor report: ", date("H:i:s"), PHP_EOL;
        echo "==========================================================", PHP_EOL;
        echo ">>>>>Speed per min", PHP_EOL;
        echo "World parse:            ", PHP_EOL;
        echo "World upload:           ", PHP_EOL;
        echo ">>>>>Average in sec", PHP_EOL;
        echo "World parse:            ", PHP_EOL;
        echo "World upload:           ", PHP_EOL;
        echo ">>>>>Absolute in " . sprintf("%d", $this->eventsSaveInterval / 1000) . " seconds", PHP_EOL;
        echo "World parsed success:   ", PHP_EOL;
        echo "World parsed fail:      ", PHP_EOL;
        echo "World uploaded success: ", PHP_EOL;
        echo "World uploaded fail:    ", PHP_EOL;
        echo "Auth success:           ", PHP_EOL;
        echo "Auth failed:            ", PHP_EOL;
        echo "Session dropped:        ", PHP_EOL;
        echo "Errors:                 ", PHP_EOL;
    }

}

$monitor = new Monitor();
$c = new Concentrator("tcp://*:5558");
$c->setReceiver(function ($data) use ($monitor) {
    print_r($data);
    echo PHP_EOL;
    $level = array(
        1 => "ERROR",
        2 => "WARN",
        3 => "INFO",
        4 => "DEBUG",
    );
    $monitor->addEvent(array(
            "from" => $data[0],
            "ts" => $data[1],
            "level" => $level[$data[2]],
            "data" => array_slice($data, 3))
    );
    $monitor->render();
});
$c->bind();
$c->listen();