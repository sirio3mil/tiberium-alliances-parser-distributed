<?php

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