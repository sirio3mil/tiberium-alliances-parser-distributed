<?php

namespace limitium\TAPD;

use limitium\TAPD\CCApi\CCApi;
use limitium\TAPD\Util\Helper;

class Register
{

    public function checkServers(&$servers, $ses)
    {
        print_r("Check servers\r\n");
        $api = new CCApi("https://gamecdnorigin.alliances.commandandconquer.com", $ses);
        $responseServers = $api->getServers();
        if ($responseServers) {
            foreach ($responseServers->Servers as $server) {
                if (!isset($servers[$server->Id]) || $servers[$server->Id]["u"] == "empty") {
                    $size = 32;
                    if (isset($server->MaxPlayers)) {
                        if ($server->MaxPlayers == 30000) {
                            $size = 35;
                            $server->version = 2;
                        }
                        if ($server->MaxPlayers == 50000) {
                            $size = 50;
                        }
                    }
                    $server->x = $size;
                    $server->y = $size;
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
                    if ($this->registerNewServer($servers[$server->Id], $ses)) {
                        Helper::saveServers($servers);
                    }
                }
            }
            Helper::saveServers($servers);
        } else {
            print_r("Empty response servers\r\n");
        }
    }

    public function registerNewServer(&$server, $ses)
    {
        if ($server['AcceptNewPlayer']) {
            if ($server['u'] != 'limitium@gmail.com') {
                $api = new CCApi($server["Url"], $ses);
                if ($api->openSession() && $api->register("limitium")) {
                    $server["u"] = "limitium@gmail.com";
                    print_r("Registered successful\r\n");
                    return true;
                } else {
                    print_r("Register fail...sleeping\r\n");
                    sleep(60*60);
                }
            } else {
                print_r("Registered already\r\n");
            }
        } else {
            $server['AcceptNewPlayer'] = false;
            print_r("No space\r\n");
        }
        return false;
    }

}
