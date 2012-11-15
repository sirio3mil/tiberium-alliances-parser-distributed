<?php


class CCAuth
{
    private $curler;
    private $username;
    private $password;
    private $verbose;
    private $session;

    public function __construct($username, $password, $verbose = false)
    {
        $this->username = $username;
        $this->password = $password;
        $this->verbose = $verbose;
        $this->curler = Curler::create()
            ->setCookieFile("." . DIRECTORY_SEPARATOR . "cookies" . DIRECTORY_SEPARATOR . "cookies_auth.txt");

    }

    private function reloadSession()
    {
        $this->session = null;

        $this->initCookie();

        $this->getLoginPage();

        $this->postLoginData();

        $this->launch();
    }


    private function initCookie()
    {
        $res = $this->curler
            ->setUrl("https://www.tiberiumalliances.com/home")
            ->setHeaders(array(
            "User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:11.0) Gecko/20100101 Firefox/11.0",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Accept-Language: en-us,en;q=0.5",
            "Accept-Encoding: gzip, deflate",
            "Cache-Control: no-cache",
            "Connection: keep-alive"
        ))
            ->get();

        if ($this->verbose) {
            file_put_contents("c:\\init.html", $res);
        }
        print_r("Cookie inited\r\n");
    }

    private function getLoginPage()
    {
        $res = $this->curler
            ->setUrl("https://www.tiberiumalliances.com/login/auth")
            ->setHeaders(array(
            "User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:11.0) Gecko/20100101 Firefox/11.0",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Accept-Language: en-us,en;q=0.5",
            "Accept-Encoding: gzip, deflate",
            "Referer: http://tiberiumalliances.com/intro/index",
            "Cache-Control: no-cache",
            "Connection: keep-alive"
        ))
            ->get();
        if ($this->verbose) {
            file_put_contents("c:\\logpage.html", $res);
        }
        print_r("Login page retrieved\r\n");
    }

    /**
     * @param $this->ch
     * @return String $worldSession
     * @throws Exception
     */
    private function postLoginData()
    {

        print_r("Login " . $this->username . ":" . $this->password . "\r\n");

        $res = $this->curler
            ->setUrl("https://www.tiberiumalliances.com/j_security_check")
            ->setHeaders(array("Host: www.tiberiumalliances.com",
            "User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:11.0) Gecko/20100101 Firefox/11.0",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Accept-Language: en-us,en;q=0.5",
            "Accept-Encoding: gzip, deflate",
            "DNT 1",
            "Referer: https://www.tiberiumalliances.com/login/auth",
            "Connection: keep-alive",
        ))
            ->setPostData(Curler::encodePost(array(
            '_web_remember_me' => '',
            'spring-security-redirect' => '',
            'timezone' => 4,
            'id' => '',
            'j_username' => $this->username,
            'j_password' => $this->password
        )))
            ->post();
        if ($this->verbose) {
            file_put_contents("c:\\login_post.html", $res);
        }
    }


    private function launch()
    {
        print_r("Launching game \r\n");
        $res = $this->curler
            ->setUrl("https://www.tiberiumalliances.com/game/launch")
            ->setHeaders(array("Host: www.tiberiumalliances.com",
            "User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:11.0) Gecko/20100101 Firefox/11.0",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Accept-Language: en-us,en;q=0.5",
            "Accept-Encoding: gzip, deflate",
            "Referer: https://tiberiumalliances.com/home",
            "Connection: keep-alive",
        ))
            ->get();
        if ($this->verbose) {
            file_put_contents("c:\\launch.html", $res);
        }
        preg_match('<input type="hidden" name="sessionId" value="(.*)?" \/>', $res, $session);

        if (isset($session[1])) {
            print_r("World session: {$session[1]}\r\n");

            preg_match("/ action=\"(.*?)\/index.aspx\" /", $res, $url);
            $this->session = $session[1];
        }
    }

    public function getSession($reload = false)
    {
        if (!$this->session || $reload) {
            $this->reloadSession();
        }
        return $this->session;
    }

}
