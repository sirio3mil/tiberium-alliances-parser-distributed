<?php

namespace limitium\TAPD\Util;


class Helper
{
    public static function saveServers(array $servers)
    {
        file_put_contents(self::pathToServers(), "<?php return " . var_export($servers, 1) . ";");


        $clientServers = [];
        foreach ($servers as $k => $server) {
            if (!in_array($k, ['limitium', 'util'])) {
                $clientServers[] = ['id' => $server['Id'], 'name' => $server['Name']];
            }
        }

        uasort($clientServers, function ($a, $b) {
            return (strtolower($a['name']) > strtolower($b['name'])) ? 1 : -1;
        });

        $curler = Curler::create()
            ->setUrl("http://map.tiberium-alliances.com/savedata")
            ->setPostData(Curler::encodePost(
                [
                    'key' => "wohdfo97wg4iurvfdc t7yaigvrufbs",
                    'servers' => serialize(array_values($clientServers))
                ]
            ))
            ->withHeaders(false);
        $curler->post();
        $curler->close();
        print_r("Updated servers\r\n");

    }

    /**
     * @return string
     */
    public static function pathToServers()
    {
        return dirname(__FILE__) . "/../../servers.php";
    }
}