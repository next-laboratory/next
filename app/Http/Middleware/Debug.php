<?php

namespace App\Http\Middleware;

use Max\Contracts\Middleware;

class Debug implements Middleware
{
    public function handle($request, \Closure $next)
    {
        //框架启动时间
        $startTime = microtime(true);
        //框架运行初始内存
        $startMemoryUsage = memory_get_usage();
        $response         = $next($request);
        if (app('config')->get('app.debug')) {
            $SQL = '';
            foreach (app('db')->getHistory() as $query) {
                [$sql, $time] = [htmlspecialchars($query[0]), $query[1]];
                $SQL .= "<p style='margin: 0 auto;'>{$sql}: {$time}ms </p>";
            }
            $timeCost    = round(microtime(true) - $startTime, 3);
            $memoryUsage = round((memory_get_usage() - $startMemoryUsage) / 1024 / 1024, 3);
            echo <<<EOT
<div style="cursor: pointer; font-size: 15px;text-align:center; font-weight:bolder;line-height: 40px;background-color: dodgerblue;color:white; width: 40px;height: 40px; position: fixed; bottom:0; right: 0" id = "btn">
       Max
</div>
<div style="height: 40%; width: 100%; position: fixed; bottom: 0;  display: none;z-index: 9999; " id="box">
    <div style="height: 2.5em; position: relative; border-bottom: 2px solid #d5d5d5; display: flex;background-color: #708af5;">
        <a href="#">SQL</a>
        <a href="#">REQ</a>
        
        <span style="cursor: pointer; position: absolute; right: 0" id="close">X</span>
    </div>
    <div style="height: 100%;background-color: #ebeff8;">
        $SQL
    </div>
    <div style="position: absolute;bottom:0; height: 1.5em;">
        <a href="#">{$timeCost}s</a>
        <a href="#">{$memoryUsage}MB</a>
</div>
</div>
<script>
    document.getElementById('btn').onclick = function() {
        document.getElementById('box').style.display = 'block';
    }
    document.getElementById('close').onclick = function() {
        document.getElementById('box').style.display = 'none';
    }
</script>
EOT;
        }
        return $response->contentType('text/html');
    }

}
