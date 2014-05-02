<?php
require_once "Util/Curler.php";
require_once "Util/Timer.php";
require_once "CCAuth/CCAuth.php";
require_once "lib/0MQ/0MQ/Log.php";

class Generator
{
    const MAX_FAILS_PER_LOGIN = 20;
    private $servers;
    private $keys = array();
    private $sessions = array();
    private $log;

    public function __construct()
    {
        $this->servers = require dirname(__FILE__) . DIRECTORY_SEPARATOR . "servers.php";
        $this->log = new Log("tcp://192.168.123.2:5558", "auth");
    }

    public function nextServer()
    {
        if (sizeof($this->keys) == 0) {
            $this->keys = array_keys($this->servers);
        }
        while (!is_numeric($id = array_shift($this->keys)) || isset($this->servers[$id]['skip'])) {
        }
        $server = $this->servers[$id];
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
            $this->sessions[$username] = array(
                "auth" => new CCAuth($username, $password),
                "fails" => 0,
                "forceReload" => true
            );
        }
        Timer::set("session");
        $session = $this->sessions[$username]["auth"]->getSession($this->sessions[$username]["forceReload"]);
        if ($session) {
            if ($this->sessions[$username]["forceReload"]) {
                $this->log->info(Timer::get("session"));
            }
            $this->sessions[$username]["forceReload"] = false;
        } else {
            $this->log->warn("ses_fail");
        }
        return $session;
    }

    public function reloadSession($serverId)
    {
        $username = $this->servers[$serverId]["u"];
        if (isset($this->sessions[$username])) {
            $this->sessions[$username]["fails"]++;
            if($this->sessions[$username]["fails"] > self::MAX_FAILS_PER_LOGIN){
                $this->sessions[$username]["forceReload"] = true;
            }
        }
    }
}
