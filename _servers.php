<?php
error_reporting(E_ALL);
date_default_timezone_set("UTC");

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "Util" . DIRECTORY_SEPARATOR . "Curler.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "Util" . DIRECTORY_SEPARATOR . "Timer.php";

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "CCAuth" . DIRECTORY_SEPARATOR . "CCAuth.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "CCApi" . DIRECTORY_SEPARATOR . "CCApi.php";

$servers = require dirname(__FILE__) . DIRECTORY_SEPARATOR . "servers.php";

$authorizator = new CCAuth("lutil@mailinator.com", "qweqwe123");
$ses = $authorizator->getSession();

$api = new CCApi("https://gamecdnorigin.alliances.commandandconquer.com", $ses);

checkNewServers($api, $servers);

function checkNewServers(CCApi $api, &$servers)
{
    foreach ($api->getServers()->Servers as $server) {
        if (!isset($servers[$server->Id])) {
            $server->x = 32;
            $server->y = 32;
            $server->u = "empty";
            $server->p = "qweqwe123";
            unset($server->Faction);
            unset($server->Friends);
            unset($server->Invites);
            unset($server->PlayerCount);
            unset($server->Online);
            unset($server->LastSeen);
            $servers[$server->Id] = (array)$server;
        } else {
            $servers[$server->Id]["Url"] = $server->Url;
        }
        if ($servers[$server->Id]["u"] == "empty") {
            print_r("New world: $server->Id - $server->Name\r\n");
        }
    }

}

function saveServers($servers)
{
    $dir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
    file_put_contents($dir . "servers.php", "<?php return " . var_export($servers, 1) . ";");

    function sortName($a, $b)
    {
        return (strtolower($a['name']) > strtolower($b['name'])) ? 1 : -1;
    }

    $clientServers = array();
    foreach ($servers as $k => $server) {
        if (!in_array($k, array('limitium', 'util'))) {
            $clientServers[] = array('id' => $server['Id'], 'name' => $server['Name']);
        }
    }

    uasort($clientServers, "sortName");
    file_put_contents("c:\\WebServers\\home\\ta-f\\www\\models\\servers.php", "<?php return " . var_export(array_values($clientServers), 1) . ";");
}
