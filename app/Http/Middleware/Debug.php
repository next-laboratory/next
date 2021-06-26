<?php

namespace App\Http\Middleware;

class Debug
{
    public function handle($request, \Closure $next)
    {
        //框架启动时间
        $startTime = microtime(true);
        //框架运行初始内存
        $startMemoryUsage = memory_get_usage();
        $response         = $next($request);
        $SQL              = '';
        foreach (app('db')->getHistory() as $query) {
            [$sql, $time] = [htmlspecialchars($query[0]), $query[1]];
            $SQL .= "<p style='margin: 0 auto; display: flex; justify-content: space-between'><span>{$sql}</span><span>{$time}ms</span></p><hr>";
        }
        $timeCost    = round(microtime(true) - $startTime, 3);
        $memoryUsage = round((memory_get_usage() - $startMemoryUsage) / 1024 / 1024, 3);
        echo <<<EOT
<style>

    #box {
        height: 40%; width: 100%; position: fixed; bottom: 0;  display: none;z-index: 9999;
    }
    
    #btn {
        cursor: pointer; 
        font-size: 13px;
        text-align:center; 
        font-weight:bolder;
        line-height: 40px;
        background-color: dodgerblue;
        box-shadow: grey 0 0 3px 1px;
        border-radius: 50%;
        color:white; 
        width: 40px;
        height: 40px; 
    }
    #btn:hover{
        transition: all .5s;
        transform: scale(.9);
    }
    
    .item {
        display: block; 
        width: 5em; 
        text-decoration: none; 
        color: white; 
        font-weight: bold
    }
    
    #title {
        padding: 0 1em;
        line-height:2.5em; 
        height: 2.5em; 
        position: relative; 
        border-bottom: 2px solid #d5d5d5; 
        display: flex;
        background-color: #708af5;
    }
</style>
<div style=" position: fixed; 
        bottom:.5em; 
        right: .5em; display: flex;">  
<div style="font-size: .8em; font-weight: bold;margin-right: .5em; line-height: 20px">
    {$timeCost}s 
    <br>
    {$memoryUsage}MB
</div>
<div id = "btn">
       Max
</div>
</div>
<div id="box">
    <div id="title">
        <a class="item" href="javascript:void(0)">数据库</a>
        <a class="item" href="javascript:void(0)">缓存</a>
        <span style="cursor: pointer; position: absolute; right: .8em; font-weight: bold" id="close">X</span>
    </div>
    <div style="height: 100%;background-color: #ebeff8;padding: .5em; overflow-y: scroll">
        $SQL
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
        return $response->contentType('text/html');
    }

}
