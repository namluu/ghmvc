<?php
namespace Core;

use App\Helper;

class Paginator
{
    public $totalPage;

    public $page = 1;

    public $numLinks = 5;

    public $routeParam;

    public $start;

    public $end;

    public $half;

    public function init($total, $limit, $page, $routeParam)
    {
        $this->totalPage = ceil( $total / $limit );

        $this->half = floor( $this->numLinks / 2 );

        $this->start = ($page - $this->half > 0) ? $page - $this->half : 1;

        if ($page + $this->half >= $this->totalPage) {
            $this->end = $this->totalPage;
            if ($this->totalPage > $this->numLinks) {
                $this->start = $this->totalPage - $this->numLinks + 1;
            }
        } elseif ($page + $this->half < $this->numLinks) {
            $this->end = $this->numLinks;
        } else {
            $this->end = $page + $this->half;
        }

        $this->page = $page;
        $this->routeParam = $routeParam;

        return $this;
    }

    public function isFirstPage()
    {
        return $this->page == 1;
    }

    public function isLastPage()
    {
        return $this->page == $this->totalPage;
    }

    public function getPageLink()
    {
        $url = Helper::getUrl();
        if ($this->routeParam['path']) {
            $url .= sprintf('%s', $this->routeParam['path']);
        }
        return $url;
    }

    public function isStartJump()
    {
        return $this->page - $this->half - 1 > 0;
    }

    public function isEndJump()
    {
        return $this->page + $this->half  < $this->totalPage;
    }
}