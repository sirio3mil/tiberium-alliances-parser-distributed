<?php
use limitium\TAPD\Util\Timer;
use limitium\zmq\Concentrator;
use Psr\Log\LogLevel;

error_reporting(E_ALL);
require __DIR__ . '/../vendor/autoload.php';


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
        $this->lastRender = microtime(1) * 1000;
        $this->events = array();
    }

    private function cleanup()
    {
        Timer::set("cleanup");

        $barrier = microtime(1) * 1000 - $this->eventsSaveInterval;
        foreach ($this->events as $k => $event) {
            if ($event["ts"] < $barrier) {
                unset($this->events[$k]);
            }
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
        $this->lastRender = microtime(1) * 1000;
        $this->cleanup();
        $this->printData($this->calculate());
    }

    private function calculate()
    {
        $data = [
            "world_parsed" => 0,
            "world_parsed_fail" => 0,
            "world_parsed_time" => 0,
            "session_dropped" => 0,

            "world_uploaded" => 0,
            "world_uploaded_fail" => 0,
            "world_uploaded_time" => 0,

            "auth" => 0,
            "auth_fail" => 0,
            "auth_time" => 0,

            "errors" => 0,
            "events" => 0,

            "parse_workers" => array(),
            "upload_workers" => array(),
        ];
        foreach ($this->events as $event) {
            $data["events"]++;
            if ($event["level"] == LogLevel::ERROR) {
                $data["errors"]++;
            }
            switch ($event["type"]) {
                case "wparser":
                    $data["parse_workers"][$event["id"]] = 1;
                    if ($event["level"] == LogLevel::WARNING) {
                        if ($event["data"] == "ses_drop") {
                            $data["session_dropped"]++;
                        }
                        if ($event["data"] == "parse_fail") {
                            $data["world_parsed_fail"]++;
                        }
                    }
                    if ($event["level"] == LogLevel::INFO) {
                        $data["world_parsed"]++;
                        $data["world_parsed_time"] += $event["data"][0];
                    }
                    break;
                case "wuploader":
                    $data["upload_workers"][$event["id"]] = 1;
                    if ($event["level"] == LogLevel::WARNING) {
                        $data["world_uploaded_fail"]++;
                    }
                    if ($event["level"] == LogLevel::INFO) {
                        $data["world_uploaded"]++;
                        $data["world_uploaded_time"] += $event["data"][0];
                    }
                    break;
                case "auth":
                    if ($event["level"] == LogLevel::WARNING) {
                        $data["auth_fail"]++;
                    }
                    if ($event["level"] == LogLevel::INFO) {
                        $data["auth"]++;
                        $data["auth_time"] += $event["data"][0];
                    }
                    break;
            }
        }
        $data["parse_workers"] = sizeof($data["parse_workers"]);
        $data["upload_workers"] = sizeof($data["upload_workers"]);
        return $data;
    }

    private function printData($data)
    {
        echo PHP_EOL, PHP_EOL, PHP_EOL, PHP_EOL, PHP_EOL, PHP_EOL, PHP_EOL, PHP_EOL, PHP_EOL, PHP_EOL, PHP_EOL, PHP_EOL, PHP_EOL, PHP_EOL;
        echo "Monitor report: ", date("H:i:s"), PHP_EOL;
        echo "=======================================", PHP_EOL;
        echo ">>>>> Speed per min", PHP_EOL;
        echo "World parse:            " . sprintf("%01.2f", $data["world_parsed"] == 0 ? 0 : ($data["world_parsed"] / ($this->eventsSaveInterval / 60000))), PHP_EOL;
        echo "World upload:           " . sprintf("%01.2f", $data["world_uploaded"] == 0 ? 0 : ($data["world_uploaded"] / ($this->eventsSaveInterval / 60000))), PHP_EOL;
        echo ">>>>> Average op in sec", PHP_EOL;
        echo "World parse:            " . sprintf("%01.2f", $data["world_parsed"] == 0 ? 0 : ($data["world_parsed_time"] / $data["world_parsed"]) / 1000), PHP_EOL;
        echo "World upload:           " . sprintf("%01.2f", $data["world_uploaded"] == 0 ? 0 : ($data["world_uploaded_time"] / $data["world_uploaded"]) / 1000), PHP_EOL;
        echo "Authorize:              " . sprintf("%01.2f", $data["auth"] == 0 ? 0 : ($data["auth_time"] / $data["auth"]) / 1000), PHP_EOL;
        echo ">>>>> Absolute in " . sprintf("%01.2f", $this->eventsSaveInterval / 1000) . " seconds", PHP_EOL;
        echo "World parsed success:   " . $data["world_parsed"], PHP_EOL;
        echo "World parsed fail:      " . $data["world_parsed_fail"], PHP_EOL;
        echo "World parse workers:    " . $data["parse_workers"], PHP_EOL;
        echo "World uploaded success: " . $data["world_uploaded"], PHP_EOL;
        echo "World uploaded fail:    " . $data["world_uploaded_fail"], PHP_EOL;
        echo "World upload workers:   " . $data["upload_workers"], PHP_EOL;
        echo "Auth success:           " . $data["auth"], PHP_EOL;
        echo "Auth failed:            " . $data["auth_fail"], PHP_EOL;
        echo "Session dropped:        " . $data["session_dropped"], PHP_EOL;
        echo "Events:                 " . $data["events"], PHP_EOL;
        echo "Errors:                 " . $data["errors"], PHP_EOL;
    }

}

$monitor = new Monitor(300000, 1000);
$c = new Concentrator("tcp://*:5558");
$c->setReceiver(function ($data) use ($monitor) {
    if ($data && $data[0] != 'auth' && $data[1] != 'auth') {
        $monitor->addEvent([
                "id" => isset($data[5]) ? json_decode($data[5])->id : 1,
                "type" => $data[0],
                "ts" => $data[1],
                "level" => $data[2],
                "data" => array_slice($data, 3)
            ]
        );
    }
    $monitor->render();
});
$c->listen();