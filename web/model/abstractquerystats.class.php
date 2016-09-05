<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/2/2016
 * Time: 12:24 PM
 */
namespace Model;

abstract class AbstractQueryStats
{
    const DEFAULT_LIMIT = 50;
    const MAX_LIMIT = 250;

    protected $page = null;
    protected $limit = null;
    protected $message = null;

    abstract public function getCount();

    public function setMessage($message) {
        $this->message = $message;
    }

    public function getMessage() {
        return $this->message;
    }

    public function setPage($page, $limit) {
        $this->page = $page ?: 1;
        $this->limit = $limit ?: static::DEFAULT_LIMIT;
        if ($this->limit > static::MAX_LIMIT)
            $this->limit = static::MAX_LIMIT;
    }

    public function getLimit() {
        return $this->limit ?: static::DEFAULT_LIMIT;
    }

    public function getCurrentPage() {
        return $this->page;
    }

    public function getOffset() {
        return (($this->page?:1)-1) * $this->limit;
    }

    public function getTotalPages() {
        return ceil($this->getCount() / $this->getLimit());
    }

    public function printPagination($baseURL, Array $args=null) {

        $page = $this->getCurrentPage();
        $pageTotal = $this->getTotalPages();

        $args = $args ?: $_GET;
        $pages = array(1, $pageTotal);
        $pi = 1;
        while(sqrt($pageTotal) > sizeof($pages)) {
            if($page - $pi > 0)
                $pages[] = $page - $pi;
            if($page + $pi < $pageTotal)
                $pages[] = $page + $pi;
            $pi*=2;
            if($pi > 999999) break;
        }
        $pages = array_unique($pages);
        sort($pages);

        if($page > 1)
           echo "<a href='", $baseURL, http_build_query(array('page' => $page - 1) + $args), "'>Previous</a> ";
        foreach($pages as $p) {
            if($p != $page)
                echo "<a href='", $baseURL, http_build_query(array('page' => $p) + $args), "'>", $p, "</a> ";
            else
                echo '[', $p, '] ';
        }
        echo "<a href='", $baseURL, http_build_query(array('page' => $page + 1) + $args), "'>Next</a> ";
    }
}