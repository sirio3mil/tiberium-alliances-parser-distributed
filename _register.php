<?php
error_reporting(E_ALL);
date_default_timezone_set("UTC");

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "Util" . DIRECTORY_SEPARATOR . "Curler.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "Util" . DIRECTORY_SEPARATOR . "Timer.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "Util" . DIRECTORY_SEPARATOR . "Helper.php";

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "CCAuth" . DIRECTORY_SEPARATOR . "CCAuth.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "CCApi" . DIRECTORY_SEPARATOR . "CCApi.php";

$servers = require dirname(__FILE__) . DIRECTORY_SEPARATOR . "servers.php";

$k = $argv[1];
if (is_numeric($k)) {
    if ($servers[$k]['AcceptNewPlayer']) {
        if ($servers[$k]['u'] != 'limitium@gmail.com') {

            $authorizator = new CCAuth("limitium@gmail.com", "qweqwe123");
            $ses = $authorizator->getSession();

            $api = new CCApi($servers[$k]["Url"], $ses);
            if ($api->openSession()) {
                $api->register("limitium");
            }
        } else {
            print_r("Registered already\r\n");
        }
    } else {
        print_r("No space\r\n");
    }
}
saveServers($servers);