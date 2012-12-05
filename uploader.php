<?php
error_reporting(E_ALL);

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "Util" . DIRECTORY_SEPARATOR . "Curler.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "Util" . DIRECTORY_SEPARATOR . "Timer.php";
require_once "lib/0MQ/0MQ/Subscriber.php";

$subscriber = new Subscriber("tcp://192.168.123.1:5556", 5000);

$subscriber->setListner(function($data)
{
    $id = substr($data, 0, 3);
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
    $curler->post();
    $curler->close();
    print_r("Uploading $id: " . Timer::get("upload") . "\r\n\r\n");
});

$subscriber->setMisser(function($delay)
{
    print_r("Long work :$delay" . PHP_EOL);
});

$subscriber->listen();