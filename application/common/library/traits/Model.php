<?php
namespace app\common\library\traits;

trait Model
{

    public function __call($method, $args) {
        return call_user_func_array([$this->model, $method], $args);
    }


    /**
     * 列表数据查询
     * @param mixed $maps   查询条件
     * @param int $limit    返回数量
     * @param int $page     当前分页
     * @param string $order 排序方式
     * @param bool $field   指定字段
     * @param array $extra  关联查询
     * @param array $chains 链式操作
     * @return mixed
     */
    public function lists($maps = '', $limit = 12, $page = 0, $order = '', $field = true, $extra = [], $funcs = [], $attrs =[]) {
        $result = $this->model;

        foreach ($funcs as $func => $val) {
            $result = $this->model->$func(...$val);
        }
        $result = $result->where($maps)->order($order)->field($field);

        $limit = isset($limit) ? $limit : 12;
        if($limit !== false) {
            $result->limit($limit);
        }
        if($page > 0) {
            $result = $result->paginate($limit, '', ['page' => $page]);
        } else {
            $result = $result->select();
        }

        // 获取关联数据
        if (is_array($extra) && !empty($extra)) {
            foreach ($result as $key => $value) {
                array_map(function($e) use (&$value) {
                    $e = trim($e);
                    $value->$e = $value->$e ?: new \stdClass();
                }, $extra);
            }
        }

        // 获取附加获取器
        if (is_array($attrs) && !empty($attrs)) {
            foreach ($result as $key => $value) {
                array_map(function($attr) use (&$value) {
                    $attr = trim($attr);
                    return $value->$attr = $value->getAttr($attr);
                }, $attrs);
            }
        }

        return $result;
    }
}
