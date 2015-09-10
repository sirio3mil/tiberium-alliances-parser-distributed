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

    /**
     * @param $server
     * @param $ses
     * @return bool
     */
    public function registerNewServer(&$server, $ses)
    {
        if ($server['AcceptNewPlayer']) {
            if ($server['u'] != 'limitium@gmail.com') {
                $api = new CCApi($server["Url"], $ses);
                if ($api->openSession() && $api->register("limitium")) {
                    $server["u"] = "limitium@gmail.com";
                    print_r("Registered successful\r\n");
                    sleep(5);
                    $this->postMessageOnForum($api, $server);
                    return true;
                } else {
                    print_r("Register fail...sleeping\r\n");
                    sleep(60 * 60);
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

    public function postMessageOnForum(CCApi $api, $server)
    {
        $title = "World minimap!!!";
        $msg = "World minimap!
Everyone welcome!
[url]http://map.tiberium-alliances.com/[/url]


";

        $post = "

 ";
        $time = CCApi::getTime();
        $data = $api->poll(array(
            "requests" => "WC:A\fCTIME:$time\fCHAT:\fWORLD:\fGIFT:\fACS:0\fASS:0\fCAT:0\f"
        ));
        foreach ($data as $part) {
            if ($part->t == "FORUM") {
                $forumId = $part->d->f[0]->i;
                break;
            }
        }
//        if (!$forumId) {
//            file_put_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . "spam" . DIRECTORY_SEPARATOR . "errorForum" . $server["Id"], json_encode($data));
//            print_r("No forumId found");
//        }
//        $fileName = dirname(__FILE__) . DIRECTORY_SEPARATOR . "spam" . DIRECTORY_SEPARATOR . $server["Id"];
//        if (!is_file($fileName) || !($threadId = file_get_contents($fileName))) {

        $id = $api->createThread($title, $msg . $post, $forumId);

//            file_put_contents($fileName, $id);
//        } else {
//            $api->addPost($post, $forumId, $threadId);
//            file_put_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . "spam" . DIRECTORY_SEPARATOR . "p" . $server["Id"], 1);
//        }
        $api->close();

        print_r("Forum spammed");
    }

}
