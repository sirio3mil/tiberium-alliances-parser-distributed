<?php
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';


use limitium\TAPD\CCApi\CCApi;
use limitium\TAPD\CCAuth\CCAuth;
use limitium\TAPD\Register;
use limitium\TAPD\Util\Helper;


$servers = require Helper::pathToServers();

$auths = [];
$r = new Register();
foreach (array_reverse(array_values($servers)) as $k => $server) {
    if (is_numeric($k)) {
        print_r($server['Id']);
        print_r("\r\n");

        if (!isset($auths[$server['u']])) {
            $a = new CCAuth($server['u'], $server['p']);
            $auths[$server['u']] = $a->getSession();
        }
        $api = new CCApi($server["Url"], $auths[$server['u']]);
        if ($api->openSession()) {
            $r->postMessageOnForum($api, $server);
        } else {
            print_r("Session problem\r\n");
        }
    }
}