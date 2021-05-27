<?php


class Spider
{
    const REFERER = 'http://www.baidu.com';

    private $ch = null;
    private $url = '';
    private $data = '';

    public function __construct(string $url, int $timeout, string $method = 'GET', $data = null)
    {
        // 线上开启错误显示
        // ini_set('display_errors',0);
        $this->ch = curl_init();
        //将连接超时时间设置大于curl超时时间
        set_time_limit($timeout + 2);
        $this->url = $url;
        $opt = [
            CURLOPT_URL => $url,
            CURLOPT_REFERER => self::REFERER,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.83 Safari/537.36 Edg/85.0.564.44',
            CURLOPT_CONNECTTIMEOUT => $timeout,
        ];
        if (substr($url, 0, 5) === 'https') {
            $opt[CURLOPT_SSL_VERIFYPEER] = false;
            $opt[CURLOPT_SSL_VERIFYHOST] = false;
        }
        if ($method == 'POST') {
            $opt[CURLOPT_POST] = 1;
            $opt[CURLOPT_POSTFIELDS] = $data;
        }
        curl_setopt_array($this->ch, $opt);
    }

    private function _getEncode($url)
    {
        $header = get_headers($url);
        foreach ($header as $h) {
            if (preg_match('/charset=(.*)?/i', $h, $match) && isset($match[1])) {
                return ($match[1]);
            }
        }
    }

    public function exec()
    {
        $this->data = curl_exec($this->ch);
        $this->_check();
        return $this->data;
    }

    private function _check()
    {
        if (0 != ($status = curl_errno($this->ch))) {
            throw new \Exception(curl_error($this->ch), $status);
        }
    }

    public function __destruct()
    {
        curl_close($this->ch);
    }
}