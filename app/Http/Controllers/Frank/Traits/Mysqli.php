<?php
/**
 * Created by PhpStorm.
 * Date: 18.9.17
 * Time: 10:11
 */

namespace App\Http\Controllers\Frank\Traits;

trait Mysqli {

    private $_mysqli;
    private $_mysqli_charset = 'UTF8';

    private function initMysqli() {
        if (!$this->_mysqli) {
            $this->_mysqli = new \mysqli(env('DB_HOST'), env('DB_USERNAME'), env('DB_PASSWORD'), env('DB_DATABASE'), env('DB_PORT'));
            // $this->_mysqli->query('SET NAMES UTF8');
            $this->_mysqli->set_charset($this->_mysqli_charset);
        }
        // else {
        //     $this->_mysqli->ping();
        // }
    }

    protected function queryRows($sql, $resulttype = MYSQLI_ASSOC) {
        $this->initMysqli();
        $res = $this->_mysqli->query($sql);
        if (!$res) {
            throw new \Exception('SQL ERROR: ' . $this->_mysqli->error, 100);
        }
        $rows = $res->fetch_all($resulttype);
        $res->free();
        return $rows;
    }

    protected function queryRow($sql, $resulttype = MYSQLI_ASSOC) {
        $rows = $this->queryRows($sql, $resulttype);
        return empty($rows) ? [] : current($rows);
    }

    protected function queryOne($sql, $resulttype = MYSQLI_ASSOC) {
        $row = $this->queryRow($sql, $resulttype);
        return empty($row) ? null : current($row);
    }
}
