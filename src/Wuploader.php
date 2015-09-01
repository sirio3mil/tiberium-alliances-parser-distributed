<?php
use limitium\TAPD\Util\Curler;
use limitium\TAPD\Util\Timer;
use limitium\zmq\Worker;
use limitium\zmq\ZLogger;

error_reporting(E_ALL);
require __DIR__ . '/../vendor/autoload.php';


$wrk = new Worker("tcp://localhost:5557", 2500, 10000, null, false);
$log = new ZLogger("wuploader", "tcp://localhost:5558");

$wrk->setExecutor(function ($data) use ($log) {
    $id = intval(substr($data, 0, 3));
    $zip = substr($data, 3);
    Timer::set("upload");
    $curler = Curler::create()
        ->setUrl("http://data.tiberium-alliances.com/savedata")
        ->setPostData(Curler::encodePost(
            array(
                'key' => "wohdfo97wg4iurvfdc t7yaigvrufbs",
                'world' => $id,
                'data' => $zip)
        )
        )
        ->withHeaders(false);
    $resp = $curler->post();
    $curler->close();
    $uploadTime = Timer::get("upload");

    print_r("Uploading $id... $resp: " . $uploadTime . "\r\n\r\n");

    if ($resp == "ok") {
        $log->info($uploadTime);
    } else {
        $log->warning("fail");
    }
    return $resp;
});

$wrk->work();


