<?php
/**
 * jQuery DataTables Helper
 */

namespace App\Traits;

use Illuminate\Http\Request;

trait DataTables {

    /**
     * 拼接 WHERE 时间范围
     * @param Request $req
     * @param string $prefix
     * @return string
     */
    protected function dtTimeRange(Request $req, $prefix = '') {

        $where = [];

        $daterange = $req->input('search.daterange');

        if (!empty($daterange)) {

            $from = addslashes($daterange['from'] ?? '');
            $to = addslashes($daterange['to'] ?? '');

            if (!empty($from)) $where[] = "$prefix created_at >= '$from 00:00:00'";
            if (!empty($to)) $where[] = "$prefix created_at <= '$to 23:59:59'";
        }

        return implode(' AND ', $where);
    }

    /**
     * 拼接 WHERE 语句
     * @param Request $req
     * @param array $fuzzyFields 模糊匹配字段
     * @param $andsMap array WHERE AND
     * @param $insMap array WHERE IN
     * @return string
     */
    protected function dtWhere(Request $req, array $fuzzyFields, array $andsMap, array $insMap = []) {

        $ands = $req->input('search.ands', []);
        $ins = $req->input('search.ins', []);

        $timeRange = $this->dtTimeRange($req, 't1.');
        $where = empty($timeRange) ? [] : [$timeRange];

        foreach ($ins as $field => $arr) {

            if (empty($insMap[$field])) continue;
            if (empty($arr)) continue;

            if (0 === strpos($insMap[$field], 's:')) {

                $ors = [];
                $field = substr($insMap[$field], 2);
                foreach ($arr as $value) {
                    $value = addslashes($value);
                    $ors[] = "FIND_IN_SET('$value', $field)";
                }

                $where[] = '(' . implode(' OR ', $ors) . ')';

            } else {

                $values = [];
                foreach ($arr as $value) {
                    $values[] = '"' . addslashes($value) . '"';
                }
                $values = implode(',', $values);
                $where[] = "{$insMap[$field]} IN ({$values})";

            }
        }

        foreach ($ands as $field => $value) {
            if (empty($andsMap[$field])) continue;
            if (empty($value)) continue;
            $value = addslashes($value);
            $where[] = "{$andsMap[$field]}='{$value}'";
        }

        if (!empty($req->input('search.value'))) {
            $word = addslashes($req->input('search.value'));
            $ors = [];
            foreach ($fuzzyFields as $field) {
                if (0 === strpos($field, 'f:')) {
                    $field = substr($field, 2);
                    $ors[] = "MATCH({$field}) AGAINST('{$word}')";
                } else {
                    // 表中数据多到一定程度以后，% LIKE 会很慢
                    // todo 使用全文索引，XunSearch、ElasticSearch
                    // 或者就用 MySQL 自带的全文索引，把所有需要搜索的字段，拼成一个用于搜索的 FullText 字段
                    $ors[] = "{$field} LIKE '%{$word}%'";
                }
            }
            $where[] = '(' . implode(' OR ', $ors) . ')';
        }

        return empty($where) ? 1 : implode(' AND ', $where);
    }

    /**
     * 拼接 LIMIT 语句
     * @param Request $req
     * @return string
     */
    protected function dtLimit(Request $req) {

        $start = (int)$req->input('start', 0);
        $length = (int)$req->input('length', 10);

        return "{$start},{$length}";
    }

    /**
     * 拼接 ORDER BY 语句
     * @param Request $req
     * @return string
     * @throws DataTablesException
     */
    protected function dtOrderBy(Request $req) {

        $order = $req->input('order');
        $columns = $req->input('columns');

        $orderby = [];

        foreach ($order as $obj) {

            $field = empty($obj['field']) ? $columns[$obj['column']]['name'] : $obj['field'];

            if (!preg_match('#^\w+$#', $field) || !preg_match('#^asc|desc$#i', $obj['dir'])) {
                throw new DataTablesException("INPUT ERROR: ORDER BY {$field} {$obj['dir']}", 101);
            }

            $orderby[] = "{$field} {$obj['dir']}";
        }

        return implode(',', $orderby);
    }
}

class DataTablesException extends \Exception {

}
