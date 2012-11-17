<?php
class Generator
{
    private $servers;
    private $keys = array();
    private $sessions = array();

    public function __construct()
    {
        $this->servers = require dirname(__FILE__) . DIRECTORY_SEPARATOR . "servers.php";
    }

    public function nextServer()
    {
        if (sizeof($this->keys) == 0) {
            $this->keys = array_keys($this->servers);
        }
        while (!is_numeric($id = array_shift($this->keys))) {
        }
        $server = $this->servers[$id];
        $session = $this->getSession($server["u"]);
        if ($session) {
            $server["session"] = $session;
            return $server;
        }
        return false;
    }

    private function getSession($username)
    {
        if (!isset($this->sessions[$username])) {
            $this->sessions[$username] = array(
                "auth" => new CCAuth($username, "qweqwe123"),
                "forceReload" => true
            );
        }
        $session = $this->sessions[$username]["auth"]->getSession($this->sessions[$username]["forceReload"]);
        if ($session) {
            $this->sessions[$username]["forceReload"] = false;
        }
        return $session;
    }

    public function reloadSession($serverId)
    {
        $username = $this->servers[$serverId]["u"];
        if (isset($this->sessions[$username])) {
            $this->sessions[$username]["forceReload"] = true;
        }
    }
}
