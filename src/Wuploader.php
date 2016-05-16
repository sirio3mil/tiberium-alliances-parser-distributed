<?php
use limitium\TAPD\Util\Curler;
use limitium\TAPD\Util\Timer;
use limitium\zmq\Worker;
use limitium\zmq\ZLogger;

error_reporting(E_ALL);
require __DIR__ . '/../vendor/autoload.php';


$wrk = new Worker("tcp://localhost:5557", 2500, 10000, null, false);
$log = new ZLogger("wuploader", "tcp://localhost:5558");
$log->id = md5(microtime());
$wrk->setExecutor(function ($data) use ($log) {
    $id = intval(substr($data, 0, 3));
    $zip = substr($data, 3);
    Timer::set("upload");
    $curler = Curler::create()
        ->setUrl("http://data.tiberium-alliances.com/savedata")
        ->setPostData(Curler::encodePost(
            array(
                'key' => "qwe",
                'world' => $id,
                'data' => $zip)
        )
        )
        ->withHeaders(false);
    $resp = $curler->post();
    $error = $curler->error();
    $curler->close();
    $uploadTime = Timer::get("upload");

    print_r("Uploading $id... $resp|$error: " . $uploadTime . "\r\n\r\n");

    if ($resp == "ok") {
        $log->info($uploadTime, ['id' => $log->id]);
    } else {
        $log->warning("fail", ['id' => $log->id]);
    }
    return $resp;
});

$wrk->work();


