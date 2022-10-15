<?php

namespace Max\Watcher\Driver;

use Max\Watcher\Contract\DriverInterface;

class InotifyDriver implements DriverInterface
{
    //需要监控的事件
    const MONITOR_EVENT = IN_MODIFY | IN_CREATE | IN_DELETE | IN_DELETE_SELF | IN_CLOSE_WRITE;

    //事件掩码
    const EVENT_MASK = [
        IN_ACCESS        => 'File was accessed (read)',
        IN_MODIFY        => 'File was modified',
        IN_ATTRIB        => 'Metadata changed',
        IN_CLOSE_WRITE   => 'File opened for writing was closed',
        IN_CLOSE_NOWRITE => 'File not opened for writing was closed',
        IN_OPEN          => 'File was opened',
        IN_MOVED_TO      => 'File moved into watched directory',
        IN_MOVED_FROM    => 'File moved out of watched directory',
        IN_CREATE        => 'File or directory created in watched directory',
        IN_DELETE        => 'File or directory deleted in watched directory',
        IN_DELETE_SELF   => 'Watched file or directory was deleted',
        IN_MOVE_SELF     => 'Watch file or directory was moved',
        IN_CLOSE         => 'Equals to IN_CLOSE_WRITE | IN_CLOSE_NOWRITE',
        IN_MOVE          => 'Equals to IN_MOVED_FROM | IN_MOVED_TO',
        IN_ALL_EVENTS    => 'Bitmask of all the above constants',
        IN_UNMOUNT       => 'File system containing watched object was unmounted',
        IN_Q_OVERFLOW    => 'Event queue overflowed (wd is -1 for this event)',
        IN_IGNORED       => 'Watch was removed (explicitly by inotify_rm_watch() or because file was removed or filesystem unmounted',
        IN_ISDIR         => 'Subject of this event is a directory',
        IN_ONLYDIR       => 'Only watch pathname if it is a directory',
        IN_DONT_FOLLOW   => 'Do not dereference pathname if it is a symlink',
        IN_MASK_ADD      => 'Add events to watch mask for this pathname if it already exists',
        IN_ONESHOT       => 'Monitor pathname for one event, then remove from watch list.',
        1073741840       => 'High-bit: File not opened for writing was closed',
        1073741856       => 'High-bit: File was opened',
        1073742080       => 'High-bit: File or directory created in watched directory',
        1073742336       => 'High-bit: File or directory deleted in watched directory',
    ];

    /**
     * 用于保存inotify_init返回的资源
     */
    public array $fds = [];

    /**
     * 用于保存监控的文件路径
     */
    public array $paths = [];

    /**
     * 用于保存inotify_add_watch返回的监控描述符
     */
    public array $wds = [];

    /**
     * 超时时间
     */
    public int $timeout = 3;

    protected array $modified = [];

    /**
     * $paths添加监控的路径数组，可以是目录或文件
     */
    public function __construct(
        array              $paths,
        protected \Closure $callback,
        protected int      $interval = 1000000,
    )
    {
        if (!empty($paths)) {
            foreach ($paths as $path) {
                if (file_exists($path)) {
                    if (is_dir($path)) {
                        $this->addDir($path);
                    } else {
                        $this->addFile($path);
                    }
                }
            }
        }
    }

    public function __destruct()
    {
        if (!empty($this->fds)) {
            foreach ($this->fds as $fd) {
                fclose($fd);
            }
        }
    }

    /**
     * 添加文件监控
     */
    public function addFile($file)
    {
        $file = realpath($file);
        $fd   = inotify_init();
        $fid  = (int)$fd;
        //保存inotify资源
        $this->fds[$fid] = $fd;
        //设置为非阻塞模式
        stream_set_blocking($this->fds[$fid], 0);
        //保存文件路径
        $this->paths[$fid] = $file;
        //保存监控描述符
        $this->wds[$fid] = inotify_add_watch($this->fds[$fid], $file, self::MONITOR_EVENT);
    }

    /**
     * 添加目录监控
     */
    public function addDir($dir)
    {
        $dir = realpath($dir);
        if ($dh = opendir($dir)) {
            //将目录加入监控中
            $fd = inotify_init();
            //一般文件的资源描述符是一个整形，可以用来当索引
            $fid             = (int)$fd;
            $this->fds[$fid] = $fd;
            stream_set_blocking($this->fds[$fid], 0);
            $this->paths[$fid] = $dir;
            $this->wds[$fid]   = inotify_add_watch($this->fds[$fid], $dir, self::MONITOR_EVENT);
            //遍历目录下文件
            while (($file = readdir($dh)) !== false) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                $file = $dir . DIRECTORY_SEPARATOR . $file;
                if (is_dir($file)) {
                    $this->addDir($file);
                }
            }
            closedir($dh);
        }
    }

    /**
     * 移除监控
     */
    public function remove($fid)
    {
        unset($this->paths[$fid]);
        fclose($this->fds[$fid]);
        unset($this->fds[$fid]);
    }

    /**
     * 运行监控
     */
    public function watch(): void
    {
        while (true) {
            usleep($this->interval);
            $reads  = $this->fds;
            $write  = [];
            $except = [];
            if (stream_select($reads, $write, $except, $this->timeout) > 0) {
                if (!empty($reads)) {
                    foreach ($reads as $read) {
                        //从可读流中读取数据
                        $events = inotify_read($read);
                        //资源描述符，整形
                        $fid = (int)$read;
                        //获取inotify实例的路径
                        $path = $this->paths[$fid];
                        foreach ($events as $event) {
                            $file = $path . DIRECTORY_SEPARATOR . $event['name'];
                            switch ($event['mask']) {
                                case IN_CREATE:
                                case 1073742080:
                                    if (is_dir($file)) {
                                        echo 'add ...', PHP_EOL;
                                        echo 'fid : ', $fid, PHP_EOL;
                                        $this->addDir($file);
                                    }
                                    break;
                                case IN_DELETE_SELF:
                                    if (is_dir($file)) {
                                        echo 'remove ...', PHP_EOL;
                                        echo 'fid : ', $fid, PHP_EOL;
                                        $this->remove($fid);

                                        $key = array_search($read, $reads);
                                        unset($reads[$key]);
                                    }
                                    break;
                            }
                            $this->modified[] = $file;
                        }
                    }
                    if (!empty($this->modified)) {
                        ($this->callback)($this->modified);
                        $this->modified = [];
                    }
                }
            }
        }
    }
}
