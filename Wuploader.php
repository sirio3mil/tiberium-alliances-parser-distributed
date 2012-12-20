<?php
error_reporting(E_ALL);

require_once "Util/Curler.php";
require_once "Util/Timer.php";
require_once "lib/0MQ/0MQ/Worker.php";
require_once "lib/0MQ/0MQ/Log.php";

$wrk = new Worker("tcp://192.168.123.1:5557", 0, 2500, 10000);
$log = new Log("tcp://192.168.123.2:5558", "wuploader");

$wrk->setExecuter(function ($data) use ($log) {
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
        $log->warn("fail");
    }
    return $resp;
});

$wrk->work();


