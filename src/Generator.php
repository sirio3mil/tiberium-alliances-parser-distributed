<?php

namespace limitium\TAPD;

use limitium\TAPD\Util\Helper;
use limitium\TAPD\Util\Timer;
use limitium\TAPD\CCAuth\CCAuth;
use limitium\zmq\ZLogger;

class Generator
{
    const MAX_FAILS_PER_LOGIN = 0;
    private $servers;
    private $keys = [];
    private $sessions = [];
    private $log;
    private $lastServersCheck = 0;

    public function __construct()
    {
        $this->servers = require Helper::pathToServers();
        $this->log = new ZLogger("auth", "tcp://localhost:5558");
        $this->register = new Register();
    }

    public function nextServer()
    {
        if (sizeof($this->keys) == 0) {
            $this->keys = array_keys($this->servers);
        }
        while (!is_numeric($id = array_shift($this->keys)) || isset($this->servers[$id]['skip'])) {
        }
        $server = $this->servers[$id];
        if ($server["u"] == "empty") {
            return false;
        }
        $session = $this->getSession($server["u"], $server["p"]);
        if ($session) {
            $server["session"] = $session;
            return $server;
        }
        return false;
    }

    private function getSession($username, $password)
    {
        if (!isset($this->sessions[$username])) {
            $this->sessions[$username] = [
                "auth" => new CCAuth($username, $password),
                "fails" => 0,
                "forceReload" => true
            ];
        }
        Timer::set("session");
        $session = $this->sessions[$username]["auth"]->getSession($this->sessions[$username]["forceReload"]);
        if ($session) {
            if ($this->sessions[$username]["forceReload"]) {
                $this->log->info(Timer::get("session"));
            }
            $this->sessions[$username]["forceReload"] = false;
        } else {
            $this->log->warning("ses_fail");
            print_r("Session fail.....sleping");
            sleep(60 * 60);
        }
        return $session;
    }

    public function reloadSession($serverId)
    {
        $username = $this->servers[$serverId]["u"];
        if (isset($this->sessions[$username])) {
            $this->sessions[$username]["fails"]++;
            if ($this->sessions[$username]["fails"] > self::MAX_FAILS_PER_LOGIN) {
                $this->sessions[$username]["forceReload"] = true;
            }
        }
    }

    public function checkNewServers()
    {
        if ($this->lastServersCheck > microtime(1) + 60 * 60 * 12) {
            $this->lastServersCheck = microtime(1);
            $ses = $this->getSession("limitium@gmail.com", "qweqwe123");
            $this->register->checkServers($this->servers, $ses);
        }
    }
}
