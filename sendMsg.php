<?php
error_reporting(E_ALL);

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "Util" . DIRECTORY_SEPARATOR . "Curler.php";

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "CCAuth" . DIRECTORY_SEPARATOR . "CCAuth.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "CCApi" . DIRECTORY_SEPARATOR . "CCApi.php";

$servers = require dirname(__FILE__) . DIRECTORY_SEPARATOR . "servers.php";


$start = microtime(true);

$server = $servers[$argv[1]];

$authorizator = new CCAuth("limitium@gmail.com", "qweqwe123");
$ses = $authorizator->getSession();

$api = new CCApi($server["Url"], $ses);

if ($api->openSession()) {

    $title = "World minimap!!!";
    $msg = "World minimap!
Everyone welcome!
[url]http://map.tiberium-alliances.com/[/url]


";

    $post = "
 Added Dünya3, World 55, Mundo 8 (España), Świat 5, World 56, Wereld 3. 
 ";

    $time = CCApi::getTime();
    $data = $api->poll(array(
        "requests" => "WC:A\fCTIME:$time\fCHAT:\fWORLD:\fGIFT:\fACS:0\fASS:0\fCAT:0\f"
    ));
    foreach ($data as $part) {
        if ($part->t == "FORUM") {
            $forumId = $part->d->f[1]->i;
            break;
        }
    }
    if (!$forumId) {
        file_put_contents("c:/WebServers/home/cc/www/parser/spam/errorForum" . $server["Id"], json_encode($data));
        print_r("No forumId found");
    }
    $fileName = dirname(__FILE__) . DIRECTORY_SEPARATOR . "spam" . DIRECTORY_SEPARATOR . $server["Id"];
    if (!is_file($fileName) || !($threadId = file_get_contents($fileName))) {
        $id = $api->createThread($title, $msg . $post, $forumId);
        file_put_contents($fileName, $id);
    } else {
        $api->addPost($post, $forumId, $threadId);
        file_put_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . "spam" . DIRECTORY_SEPARATOR . "p" . $server["Id"], 1);
    }
    $api->close();

    print_r("Time: " . (microtime(true) - $start));
}