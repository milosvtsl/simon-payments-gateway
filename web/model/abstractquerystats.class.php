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

    public function printPagination($baseURL) {

        $page = $this->getCurrentPage();
        $pageTotal = $this->getTotalPages();
        $fraction = (floor($pageTotal / 100) * 10) ?: 1;

        $args = $_GET;
        if ($page > 1) {
            $args['page'] = $page - 1;
            echo "<a href='", $baseURL, http_build_query($args), "'>Previous</a> ";
        }

        // TODO print current page
        echo "<a href='", $baseURL, http_build_query(array('page' => 1) + $args), "'>", 1, "</a> ";

        for ($i = 2; $i <= $pageTotal; $i += $fraction) {
            if ($i == $page) {
                echo '[', $i, '] ';
            } else {
                echo "<a href='", $baseURL, http_build_query(array('page' => $i) + $args), "'>", $i, "</a> ";
            }
        }
        if($i-$fraction<$pageTotal)
            echo "<a href='", $baseURL, http_build_query(array('page' => $pageTotal) + $args), "'>", $pageTotal, "</a> ";

        echo "<a href='", $baseURL, http_build_query(array('page' => $page + 1) + $args), "'>Next</a> ";
    }
}