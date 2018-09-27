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

        try {
            $now = date('Y-m-d H:i:s');
            $data = [
                'name' => $resource_attr->name,
                'src_id' => $resource_attr->src_id,
                'owner_id' => $resource_attr->owner_id ? : 0,
                'role_id' => $resource_attr->role_id ? : 0,
                'mode' => $resource_attr->mode,
                'rule' => $resource_attr->rule ? : '',
                'ctime' => $now,
                'mtime' => $now,
            ];
            (new \ResourceAttr())->create()->set($data)->save();
            $ret->ret = \Constant::RET_OK;
        } catch (\Exception $e) {
            $ret->ret = \Constant::RET_DATA_CONFLICT;
        }

        return $ret;
    }

    /**
     * 删除资源权限属性.
     *
     * @param int $resource_attr_id
     *
     * @return \Authority\CommonRet $ret
     */
    public static function remove($resource_attr_id)
    {
        $ret = new CommonRet();

        $item = (new \ResourceAttr())->find_one($resource_attr_id);
        if ($item) {
            $ret->ret = $item->delete() ? \Constant::RET_OK : \Constant::RET_SYS_ERROR;
        } else {
            $ret->ret = \Constant::RET_DATA_NO_FOUND;
        }

        return $ret;
    }

    /**
     * 更新资源权限属性.
     *
     * @param int                     $resource_attr_id
     * @param \Authority\ResourceAttr $resource_attr
     *
     * @return \Authority\CommonRet $ret
     */
    public static function update($resource_attr_id, ResourceAttr $resource_attr)
    {
        $ret = new CommonRet();

        $item = (new \ResourceAttr())->find_one($resource_attr_id);
        if ($item) {
            $data = [
                'owner_id' => $resource_attr->owner_id ? : 0,
                'role_id' => $resource_attr->role_id ? : 0,
                'mode' => $resource_attr->mode,
                'rule' => $resource_attr->rule ? : '',
                'mtime' => date('Y-m-d H:i:s'),
            ];
            $ret->ret = $item->set($data)->save() ? \Constant::RET_OK : \Constant::RET_SYS_ERROR;
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
        if ($conditions = $search->conditions) {
            foreach ($conditions as $condition) {
                $expr = $condition->expr ? : 'where';
                $model->$expr($condition->field, $condition->value);
            }
        }
        $ret->total = $model->count();

        $model->clean();
        if ($conditions = $search->conditions) {
            foreach ($conditions as $condition) {
                $expr = $condition->expr ? : 'where';
                $model->$expr($condition->field, $condition->value);
            }
        }
        if ($page = $search->page) {
            $pagesize = $search->pagesize ? : 20;
            $model->offset(($page - 1) * $pagesize)->limit($pagesize);
        }

        $result = $model->order_by_desc('id')->find_array();
        $resource_attrs = [];
        if ($result) {
            foreach ($result as $item) {
                $resource_attrs[] = new ResourceAttr(
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
