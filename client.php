<?php
error_reporting(E_ALL);

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "Util" . DIRECTORY_SEPARATOR . "Curler.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "Util" . DIRECTORY_SEPARATOR . "Timer.php";

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "CCAuth" . DIRECTORY_SEPARATOR . "CCAuth.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "CCApi" . DIRECTORY_SEPARATOR . "CCApi.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "CCDecoder" . DIRECTORY_SEPARATOR . "GameObjects.php";

require_once "lib/0MQ/0MQ/Worker.php";

$wrk = new Worker("tcp://192.168.123.1:5555", false, 5000, 10000);

$wrk->setExecuter(function ($data)
{

    Timer::set("start");

    Timer::set("get");

    $server = (array)json_decode($data);

    $api = new CCApi($server["Url"], $server["session"]);
    $result = array(
        "Id" => $server["Id"],
        "status" => 2,
        "data" => null,
    );
    if ($api->openSession()) {
        $world = new World($server["Id"]);

        $time = CCApi::getTime();

        $resp = $api->poll(array(
            "requests" => "WC:A\fCTIME:$time\fCHAT:\fWORLD:\fGIFT:\fACS:0\fASS:0\fCAT:0\f"
        ));

        $successParts = 0;
        $squares = array();
        for ($y = 0; $y <= $server["y"]; $y += 1) {

            $request = $world->request(0, $y, $server["x"], $y);

            $time = CCApi::getTime();
            $resp = $api->poll(array(
                "requests" => "UA\fWC:A\fCTIME:$time\fCHAT:\fWORLD:$request\fGIFT:\fACS:1\fASS:1\fCAT:1\f"
            ), true);


            if ($resp) {
                $data = json_decode($resp);
                if ($data) {
                    $squares = array();
//                    print_r("Row: $y");
                    $hasSquares = false;
                    foreach ($data as $part) {
                        if (isset($part->d->__type) && $part->d->__type == "WORLD") {
                            $hasSquares = true;

                            unset($part->d->u);
                            unset($part->d->t);
                            unset($part->d->v);

                            $squaresSize = sizeof($part->d->s);
//                            print_r(" squares: " . $squaresSize . "\r\n");
                            if ($squaresSize != $server["x"]) {
                                break 2;
                            } else {
                                $successParts++;
                            }
                            foreach ($part->d->s as $squareData) {
                                $world->addSquare(Square::decode($squareData));
                            }
                        }
                    }
                    if (!$hasSquares) {
                        break;
                    }
                }
            }
        }
        print_r("\r\nSucces parts:$successParts, time: " . Timer::get("get") . "\r\n\r\n");
        if ($successParts == $server["y"]) {

            Timer::set("decode");
            foreach ($squares as $squareData) {
                $world->addSquare(Square::decode($squareData));
            }
            print_r("Decoded, time: " . Timer::get("decode") . " \r\n\r\n");


            Timer::set("upload");
            $zip = $world->toServer();
            print_r("Uploading: " . Timer::get("upload") . "\r\n\r\n");
            $result["status"] = 1;
            $result["data"] = $zip;
        }
        print_r("Total time: " . Timer::get("start"). "\r\n\r\n");

    } else {
        $result["status"] = 3;
    }
    $api->close();
    return sprintf("%03s", $result["Id"]) . sprintf("%02s", $result["status"]) . $result["data"];
});

$wrk->work();


