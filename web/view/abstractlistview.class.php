<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/5/2016
 * Time: 10:47 AM
 */
namespace View;

abstract class AbstractListView extends AbstractView
{
    const DEFAULT_LIMIT = 50;
    const MAX_LIMIT = 250;

    const FIELD_ORDER = 'order';
    const FIELD_ORDER_BY = 'order-by';

    private $_page = 1;
    private $_limit = null;
    private $_row_count = null;
    private $_list_query = null;

    public function setRowCount($count) {
        $this->_row_count = $count;
    }

    public function getRowCount() {
        if($this->_row_count === null)
            throw new \InvalidArgumentException("Row count was not set");
        return $this->_row_count;
    }

    public function setPageParameters($page, $limit=null) {
        $this->_page = $page;
        $this->_limit = $limit ?: static::DEFAULT_LIMIT;
        if ($this->_limit > static::MAX_LIMIT)
            $this->_limit = static::MAX_LIMIT;
    }

    public function getLimit() {
        return $this->_limit ?: static::DEFAULT_LIMIT;
    }

    public function getCurrentPage() {
        return $this->_page;
    }

    public function getOffset() {
        return (($this->_page?:1)-1) * $this->_limit;
    }

    public function getTotalPages() {
        return ceil($this->getRowCount() / $this->getLimit());
    }

    protected function setListQuery(\PDOStatement $ListQuery) {
        $this->_list_query = $ListQuery;
    }

    /**
     * @return \PDOStatement
     */
    protected function getListQuery() {
        return $this->_list_query;
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


    function getSortURL($field, Array $args=null) {
        $args = $args ?: $_GET;
        if(@$args[self::FIELD_ORDER_BY] == $field && strcasecmp(@$args[self::FIELD_ORDER], 'DESC') !== 0)
            return http_build_query(array(self::FIELD_ORDER => 'DESC') + $args);
        return http_build_query(array(self::FIELD_ORDER => 'ASC', self::FIELD_ORDER_BY => $field) + $args);
    }

}