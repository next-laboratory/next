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

namespace Max\Database\Eloquent;

use Exception;
use Max\Database\Collection;

class Paginate extends Collection
{

    protected $page;

    protected $sizePerPage;

    public function paginate(int $page = 1, int $sizePerPage = 15)
    {
        $this->page        = $page;
        $this->sizePerPage = $sizePerPage;
    }

    public function raw()
    {
        if ($page < 1 || $page > $totalPage) {
            throw new Exception('没有查找到内容🤔🤔🤔');
        }
        if ($totalPage <= 1) {
            return '';
        }
        $pages = [];
        for ($i = 1; $i >= 0; $i--) {
            if ($page - 1 - $i > 0) {
                $pages[$page - 1 - $i] = $page - 1 - $i;
            }
        }
        $pages[$page] = (int)$page;
        for ($i = 0; $i <= 1; $i++) {
            if ($page + 1 + $i <= $totalPage) {
                $pages[$page + 1 + $i] = $page + 1 + $i;
            }
        }
        ksort($pages);
        $request      = $this->request->get();
        $request['p'] = 1;
        $query        = '?' . http_build_query($request);
        $paginate     = ($page == 1) ? '' : '<li><a href="' . $query . '">首页</a></li>';
        foreach ($pages as $p => $name) {
            $query = '?p=' . $p;
            if (!empty($request)) {
                $request['p'] = $p;
                $query        = '?' . http_build_query($request);
            }
            if ($p == $page) {
                $paginate .= '<li style="background-color: white;"><a style="color: black" >' . $name . '</a></li>';
            } else {
                $paginate .= '<li><a href="' . $query . '">' . $name . '</a></li>';
            }
        }
        $request['p'] = $totalPage;
        $query        = '?' . http_build_query($request);
        $paginate     .= ($page == $totalPage) ? '' : '<li><a href="' . $query . '">尾页</a></li>';
        return $paginate;
    }
}
