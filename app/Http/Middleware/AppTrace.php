<?php

namespace App\Http\Middleware;

class AppTrace
{
    public function handle($request, \Closure $next)
    {
        //不准确的框架启动时间
        $startTime = microtime(true);
        //不准确的框架运行初始内存
        $startMemoryUsage = memory_get_usage();
        $response         = $next($request);
        $SQL              = '';
        foreach (app('db')->getHistory() as $query) {
            [$sql, $time, $binds] = [htmlspecialchars($query['query']), $query['time'] . 'ms', htmlspecialchars(json_encode($query['boundParameters']))];
            $SQL .= "<p style='margin: 0 auto; display: flex; justify-content: space-between'><span title='{$binds}'>{$sql}</span><span>{$time}</span></p><hr>";
        }
        $timeCost    = round(microtime(true) - $startTime, 4);
        $memoryUsage = round((memory_get_usage() - $startMemoryUsage) / 1024 / 1024, 3);
        $files       = '';
        foreach (get_included_files() as $key => $file) {
            $files .= $key + 1 . ': ' . $file . ' [' . round(filesize($file) / 1024, 3) . 'KB] <hr>';
        }
        echo <<<EOT
<style>
    *::-webkit-scrollbar {
        width: 5px;
    }
    *::-webkit-scrollbar-thumb {
        border-radius: 2px;
        background-color: #333a41;
    }
    #box {
        height: 40%; 
        width: 100%; 
        position: fixed; 
        bottom: 0;  
        left:0;
        right: 0;
        display: none;
        z-index: 100001;
        margin: 0 auto;
        box-sizing: border-box;
    }

    #btn {
        cursor: pointer;
        font-size: 13px;
        text-align:center;
        font-weight:bolder;
        line-height: 40px;
        background-color: #333a41;
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
        font-size: .9em;
        width: 6em;
        text-decoration: none;
        color: white;
        text-align: center;
        cursor:pointer;
        display: inline-block;
    }
    .item:hover {
           background-color: #181c21;
    }

    #title {
        list-style-type: none;
        padding: 0;
        line-height:2.5em;
        height: 2.5em;
        position: relative;
        margin: 0;
        display: flex;
        justify-content: center;
        background-color: #333a41;
    }
</style>
<div style="position: fixed; bottom:.5em; border-radius: 50px; background-color: rgba(255,255,255,0.76); right: .5em; display: flex; z-index: 100000; padding-left: 1em">
<div style="font-size: .5em; font-weight: bold;margin-right: .5em; line-height: 20px">
    {$timeCost}s
    <br>
    {$memoryUsage}MB
</div>
<div id = "btn">
       Max
</div>
</div>
<div id="box">
    <ul id="title">
        <li class="item" data-name="database">Database</li>
        <li class="item" data-name="cache">Cache</li>
        <li class="item" data-name="files">Files</li>
        <span style="line-height: 2.3em; color: white; cursor: pointer; position: absolute; right: .8em; font-weight: bold" id="close">x</span>
    </ul>
    <div id="debug-content" style="height: calc(100% - 3em); background-color: #ebeff8;padding: .5em; overflow-y: scroll; font-size: .85em; box-sizing: border-box; word-break: break-all">
        {$SQL}
    </div>
</div>
<script>

    const SQL = "{$SQL}";
    const files = "{$files}"

    document.getElementById('btn').onclick = function() {
        document.getElementById('box').style.display = 'block';
    }
    document.getElementById('close').onclick = function() {
        document.getElementById('box').style.display = 'none';
    }

    const item = document.getElementsByClassName('item');
    for (i in item) {
        item[i].onclick = function() {
            let data = this.getAttribute('data-name');
            let content = '';
            switch (data){
                case 'database':
                    content = SQL;
                    break;
                case 'files':
                    content = files;
                    break;
            }
            document.getElementById('debug-content').innerHTML = content;
        }
    }

</script>
EOT;
        return $response->contentType('text/html');
    }

}
