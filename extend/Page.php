<?php

class Page
{
    private $total;    //总数据条数
    private $listRows; //每页显示多少条
    private $limit;    //限制每次取得的数据
    private $totalPage;//总页数
    private $page;     //当前页

    function __construct($total, $listRows = 5)
    {
        $this->total = $total;
        $this->listRows = $listRows;
        $this->totalPage = ceil($this->total / $this->listRows);
        $this->page = isset($_GET["page"]) ? $_GET["page"] : 1;
        $this->limit = $this->setLimit();
    }

    //在外部访问内部部分私有属性
    function __get($name)
    {
        if ($name == 'totalPage' || $name = 'limit') {
            return $this->$name;
        } else return '不能访问' . $name . '属性！';
    }

    private function setLimit()
    {
        if ($this->page > 0)
            return 'limit ' . ($this->page - 1) * $this->listRows . ',' . $this->listRows;
        return 'limit 0' . $this->total;
    }

    private function next()
    {
        if (isset($_GET['page']) && $_GET['page'] < $this->totalPage) {
            return '<li><a href="?m='.MODULE.'&c=' . CONTROLLER . '&page=' . ($_GET['page'] + 1) . '">' . ($_GET['page'] + 1) . '</a></li>';
        } else if (!isset($_GET['page']) && $this->totalPage >= 2) {
            return '<li><a href="?m='.MODULE.'&c=' . CONTROLLER . '&page=2">2</a></li>';
        }
    }

    private function prev()
    {
        if (isset($_GET['page']) && $_GET['page'] > 1) {
            return '<li><a href="?m='.MODULE.'&c=' . CONTROLLER . '&page=' . ($_GET['page'] - 1) . '">' . ($_GET['page'] - 1) . '</a></li>';
        }
    }

    public function __toString()
    {
        if($this->total > 0){
            $page = isset($_GET['page']) ? $_GET['page'] : 1;
            if ($page > $this->totalPage) {
                header('location:?m='.MODULE.'&c=' . CONTROLLER . '&page=' . $this->totalPage);
                exit;
            } else if ($page < 1) {
                header('location:?m='.MODULE.'&c=' . CONTROLLER . '&page=1');
                exit;
            }
            if ($this->totalPage > 1) {
                $page = '<ul><li><a href="?m='.MODULE.'&c=' . CONTROLLER . '&page=1">F</a></li>' . $this->prev() . '<li><a href="?m='.MODULE.'&c=' . CONTROLLER . '&page=' . $page . '">' . $page . '</a></li>' . $this->next() . '<li><a href="?m='.MODULE.'&c=' . CONTROLLER . '&page=' . $this->totalPage . '">E</a></li>';
                return $page;
            }
        }
        return '';
    }
}
