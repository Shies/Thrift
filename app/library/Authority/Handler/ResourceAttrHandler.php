<?php

namespace Authority;

class ResourceAttrHandler
{
    /**
     * 新增资源权限属性.
     *
     * @param \Authority\ResourceAttr $resource_attr
     *
     * @return \Authority\CommonRet $ret
     */
    public static function add(ResourceAttr $resource_attr)
    {
        $ret = new CommonRet();

        $name = $resource_attr->name;
        $src_id = $resource_attr->src_id;
        $owner_id = $resource_attr->owner_id ? : 0;
        $role_id = $resource_attr->role_id ? : 0;
        $mode = $resource_attr->mode;
        $rule = $resource_attr->rule ? : '';

        $id = (new \ResourceAttr())->add($name, $src_id, $owner_id, $role_id, $mode, $rule);
        if ($id) {
            $ret->ret = \Constant::RET_OK;
        } else {
            $ret->ret = \Constant::RET_DATA_CONFLICT;
            $ret->data = 'ResourceAttr exists!';
        }

        return $ret;
    }

    /**
     * 删除资源权限属性.
     *
     * @param string $name
     * @param int    $src_id
     *
     * @return \Authority\CommonRet $ret
     */
    public static function remove($name, $src_id)
    {
        $ret = new CommonRet();

        $count = (new \ResourceAttr())->remove($name, $src_id);
        $ret->ret = $count ? \Constant::RET_OK : \Constant::RET_DATA_NO_FOUND;

        return $ret;
    }

    /**
     * 更新资源权限属性.
     *
     * @param string                  $name
     * @param int                     $src_id
     * @param \Authority\ResourceAttr $resource_attr
     *
     * @return \Authority\CommonRet $ret
     */
    public static function update($name, $src_id, ResourceAttr $resource_attr)
    {
        $ret = new CommonRet();

        $model = new \ResourceAttr();
        $item = $model->getById($name, $src_id);
        if ($item) {
            $owner_id = $resource_attr->owner_id ? : $item['owner_id'];
            $role_id = $resource_attr->role_id ? : $item['role_id'];
            $mode = $resource_attr->mode ? : $item['mode'];
            $rule = $resource_attr->rule ? : $item['rule'];
            
            $model->update($name, $src_id, $owner_id, $role_id, $mode, $rule);
            $ret->ret = \Constant::RET_OK;
        } else {
            $ret->ret = \Constant::RET_DATA_NO_FOUND;
        }

        return $ret;
    }

    /**
     * 获取资源权限属性.
     *
     * @param \Authority\Search $search
     *
     * @return \Authority\ResourceAttrRet $ret
     */
    public static function getList(Search $search)
    {
        $ret = new ResourceAttrRet();
        $ret->ret = \Constant::RET_OK;

        $model = new \ResourceAttr();
        $sql = 'SELECT * FROM resource_attr';
        $total_sql = 'SELECT COUNT(1) FROM resource_attr';
        if ($search->conditions) {
            $where = [];
            foreach ($search->conditions as $condition) {
                $expr = $condition->expr ? : '=';
                $where[] = "{$condition->field} {$expr} '{$condition->value}'";
            }
            $where = implode(' AND ', $where);
            $sql .= " WHERE {$where}";
            $total_sql .= " WHERE {$where}";
        }
        $ret->total = $model->query($total_sql)->fetch(\PDO::FETCH_COLUMN);
        if ($search->page) {
            $pagesize = $search->pagesize ? : 20;
            $offset = ($page - 1) * $pagesize;
            $sql .= " LIMIT {$offset},{$pagesize}";
        }

        $resource_attrs = $model->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        if ($resource_attrs) {
            $data = [];
            foreach ($resource_attrs as $item) {
                $data[] = new ResourceAttr(
                    [
                        'id' => $item['id'],
                        'name' => $item['name'],
                        'src_id' => $item['src_id'],
                        'owner_id' => $item['owner_id'],
                        'role_id' => $item['role_id'],
                        'mode' => $item['mode'],
                        'rule' => $item['rule'],
                    ]
                );
            }
            $ret->resource_attrs = $resource_attrs;
        }

        return $ret;
    }
}
