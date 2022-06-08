<?php

declare(strict_types=1);

/**
 * This file is part of the Max package.
 *
 * (c) Cheng Yao <987861463@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Max\Http\Server\ResponseEmitter;

use Max\Http\Message\Cookie;
use Max\Http\Message\Stream\FileStream;
use Max\Http\Server\Contracts\ResponseEmitterInterface;
use Psr\Http\Message\ResponseInterface;

class FPMResponseEmitter implements ResponseEmitterInterface
{
    /**
     * @param ResponseInterface $psrResponse
     * @param null $sender
     *
     * @return void
     */
    public function emit(ResponseInterface $psrResponse, $sender = null): void
    {
        header(sprintf('HTTP/%s %d %s', $psrResponse->getProtocolVersion(), $psrResponse->getStatusCode(), $psrResponse->getReasonPhrase()), true);
        foreach ($psrResponse->getHeader('Set-Cookie') as $cookie) {
            $cookie = Cookie::parse($cookie);
            setcookie(
                $cookie->getName(), $cookie->getValue(),
                $cookie->getExpires(), $cookie->getPath(),
                $cookie->getDomain(), $cookie->isSecure(),
                $cookie->isHttponly()
            );
        }
        $psrResponse = $psrResponse->withoutHeader('Set-Cookie');
        foreach ($psrResponse->getHeaders() as $name => $value) {
            header($name . ': ' . implode(', ', $value));
        }
        $body = $psrResponse->getBody();
        if ($body instanceof FileStream) {
            header("Pragma: public");                                            //1 Public指示响应可被任何缓存区缓存。
            header("Expires: 0");                                                //2 浏览器不会响应缓存
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); //3
            //            header("Content-Type:application/force-download");                  //4 请求该页面就出现下载保存窗口。
            //            header("Content-Type:application/octet-stream");                    //5  二进制流，不知道下载文件类型。
            //            header("Content-Type:application/vnd.ms-excel;");                   //6
            header("Content-Type: application/download");                        //7 提示用户将当前文件保存到本地。
            header("Content-Transfer-Encoding: binary");                         //9
        }
        echo $body?->getContents();
        $body?->close();
    }
}
