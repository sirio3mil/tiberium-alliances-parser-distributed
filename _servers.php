<?php
$dir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
require_once $dir . "CnCApi.php";
$api = new CCApi("util");
$api->checkNewServers();
$api->saveServers();




public function checkNewServers()
{
    foreach ($this->getServers()->Servers as $server) {
        if (!isset($this->servers[$server->Id])) {
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
            $this->servers[$server->Id] = (array)$server;
        } else {
            $this->servers[$server->Id]["Url"] = $server->Url;
        }
        if ($this->servers[$server->Id]["u"] == "empty") {
            print_r("New world: $server->Id - $server->Name\r\n");
        }
    }

}

public function saveServers()
{
    $dir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
    file_put_contents($dir . "servers.php", "<?php return " . var_export($this->servers, 1) . ";");

    function sortName($a, $b)
    {
        return (strtolower($a['name']) > strtolower($b['name'])) ? 1 : -1;
    }

    $clientServers = array();
    foreach ($this->servers as $k => $server) {
        if (!in_array($k, array('limitium', 'util'))) {
            $clientServers[] = array('id' => $server['Id'], 'name' => $server['Name']);
        }
    }

    uasort($clientServers, "sortName");
    file_put_contents("c:\\WebServers\\home\\ta-f\\www\\models\\servers.php", "<?php return " . var_export(array_values($clientServers), 1) . ";");
}
