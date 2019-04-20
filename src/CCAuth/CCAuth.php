<?php

namespace limitium\TAPD\CCAuth;

use limitium\TAPD\Util\Curler;

class CCAuth
{
    /** @var Curler */
    private $curler;
    /** @var string */
    private $username;
    /** @var string */
    private $password;
    /** @var bool */
    private $verbose;

    private $session;
    /** @var string */
    protected $basePath;
    /** @var string */
    protected $cookieFileName;

    public function __construct($username, $password, $verbose = false)
    {
        $this->username = $username;
        $this->password = $password;
        $this->verbose = $verbose;
        $this->cookieFileName = md5($username);
        $this->curler = Curler::create();
    }

    /**
     * @param string $basePath
     * @return CCAuth
     */
    public function setBasePath(string $basePath): CCAuth
    {
        $this->basePath = $basePath;
        $this->curler->setCookieFile($this->basePath . DIRECTORY_SEPARATOR . $this->cookieFileName);
        return $this;
    }

    /**
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    private function reloadSession(): void
    {
        $this->session = null;

        $this->initCookie();

        $this->getLoginPage();

        $this->postLoginData();

        $this->launch();
    }

    /**
     * @param string|null $res
     */
    protected function debug(?string $res): void
    {
        if ($this->verbose) {
            $trace = debug_backtrace();
            $caller = $trace[1];
            file_put_contents($this->basePath . DIRECTORY_SEPARATOR . $caller['function'], $res);
            print_r($caller['function'] . PHP_EOL);
        }
    }

    private function initCookie(): void
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
        $this->debug($res);
    }

    private function getLoginPage(): void
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
        $this->debug($res);
    }

    private function postLoginData(): void
    {
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
        $this->debug($res);
    }

    private function launch(): void
    {
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
        $this->debug($res);
        preg_match('<input type="hidden" name="sessionId" value="(.*)?" \/>', $res, $session);
        if (isset($session[1])) {
//            preg_match("/ action=\"(.*?)\/index.aspx\" /", $res, $url);
            $this->session = $session[1];
        }
    }

    /**
     * @param bool $reload
     * @return string|null
     */
    public function getSession($reload = false): ?string
    {
        if (!$this->session || $reload) {
            $this->reloadSession();
        }
        return $this->session;
    }

}
