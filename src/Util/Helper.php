<?php

namespace limitium\TAPD\Util;


class Helper
{
    public static function saveServers(array $servers)
    {
        file_put_contents(self::pathToServers(), "<?php return " . var_export($servers, 1) . ";");


        $clientServers = [];
        foreach ($servers as $k => $server) {
            if (!in_array($k, array('limitium', 'util'))) {
                $clientServers[] = array('id' => $server['Id'], 'name' => $server['Name']);
            }
        }

        uasort($clientServers, function ($a, $b) {
            return (strtolower($a['name']) > strtolower($b['name'])) ? 1 : -1;
        });

//        post to front
//        file_put_contents("c:\\WebServers\\home\\ta-f\\www\\models\\servers.php", "<?php return " . var_export(array_values($clientServers), 1) . ";");
    }

    /**
     * @return string
     */
    public static function pathToServers()
    {
        return dirname(__FILE__) . "/../../servers.php";
    }
}