<?php

namespace limitium\TAPD\Util;

class Curler
{
    private $ch;

    /**
     * @return Curler
     */
    public static function create()
    {
        return new self();
    }

    public function __construct()
    {
        $this->ch = curl_init();

        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->ch, CURLOPT_HEADER, true);

        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($this->ch, CURLOPT_TIMEOUT, 40);

        return $this;
    }

    public function setCookieFile($file)
    {
        curl_setopt($this->ch, CURLOPT_COOKIEJAR, $file);
        curl_setopt($this->ch, CURLOPT_COOKIEFILE, $file);
        return $this;
    }

    public function setUrl($url)
    {
        curl_setopt($this->ch, CURLOPT_URL, $url);
        return $this;
    }

    public function setHeaders($headers)
    {
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
        return $this;
    }

    public function get()
    {
        curl_setopt($this->ch, CURLOPT_POST, 0);
        return curl_exec($this->ch);
    }

    public function setPostData($data)
    {
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
        return $this;
    }

    public function post()
    {
        curl_setopt($this->ch, CURLOPT_POST, 1);
        return curl_exec($this->ch);
    }

    public function withHeaders($with)
    {
        curl_setopt($this->ch, CURLOPT_HEADER, $with);
        return $this;
    }

    public function error()
    {
        return curl_error($this->ch);
    }

    public function close()
    {
        curl_close($this->ch);
    }

    public static function encodePost($data)
    {
        $postData = array();
        foreach ($data as $key => $value) {
            $postData[] = urlencode($key) . "=" . urlencode($value);
        }
        $postData = implode("&", $postData);
        return $postData;
    }
}
