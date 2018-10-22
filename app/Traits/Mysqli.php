<?php
/**
 * 对于复杂的统计查询，建议写 SQL 语句，用 Eloquent 反而易出问题
 */

namespace App\Traits;

trait Mysqli {

    /**
     * @var \mysqli PhpStorm 需要对应类型信息，否则无语法提示
     */
    private $_mysqli;
    protected $_mysqli_charset = 'UTF8';

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

    /**
     * 执行SQL，并返回所有行
     * @param $sql
     * @return array
     * @throws MysqliException
     */
    protected function queryRows($sql, $resulttype = MYSQLI_ASSOC) {
        $this->initMysqli();
        $res = $this->_mysqli->query($sql);
        if (!$res) {
            throw new MysqliException($this->_mysqli->error, 100);
        }
        $rows = $res->fetch_all($resulttype);
        $res->free();
        return $rows;
    }

    /**
     * 执行SQL，以一维数组形式返回结果中所有行的第一个字段的值
     * @param $sql
     * @return array
     * @throws MysqliException
     */
    protected function queryFields($sql) {
        $fields = [];
        $rows = $this->queryRows($sql);
        foreach ($rows as $row) {
            $fields[] = current($row);
        }
        return $fields;
    }

    /**
     * 执行SQL，返回结果的第一行
     * 若无结果，返回空数组
     * @param $sql
     * @return array
     * @throws MysqliException
     */
    protected function queryRow($sql, $resulttype = MYSQLI_ASSOC) {
        $rows = $this->queryRows($sql, $resulttype);
        return empty($rows) ? [] : current($rows);
    }

    /**
     * 执行SQL，返回结果的第一行中的第一个字段的值
     * @param $sql
     * @return string|null
     * @throws MysqliException
     */
    protected function queryOne($sql) {
        $row = $this->queryRow($sql);
        return empty($row) ? null : current($row);
    }

    /**
     * 获取 Enum 枚举字段所有可能值
     * @param $table
     * @param $field
     * @return array
     * @throws MysqliException
     */
    protected function enumOptions($table, $field) {
        $info = $this->queryRow("SHOW COLUMNS FROM $table WHERE Field='$field'");
        $strs = explode("'", $info['Type']);
        $options = [];
        for ($i = 1; $i < count($strs); $i += 2) {
            $options[] = $strs[$i];
        }
        return $options;
    }

    public function __destruct() {
        if ($this->_mysqli) $this->_mysqli->close();
        parent::__destruct();
    }
}

class MysqliException extends \Exception {

}
