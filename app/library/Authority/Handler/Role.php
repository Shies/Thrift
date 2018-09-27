<?php

namespace Authority;

class RoleHandler
{
    /**
     * 新增角色.
     *
     * @param \Authority\Role $role
     *
     * @return \Authority\CommonRet $ret
     */
    public static function add(Role $role)
    {
        $ret = new CommonRet();

        try {
            $now = date('Y-m-d H:i:s');
            $data = [
                'type' => $role->type,
                'name' => $role->name,
                'rule' => $role->rule ? : '',
                'ctime' => $now,
                'mtime' => $now,
            ];
            (new \Role())->create()->set($data)->save();
            $ret->ret = \Constant::RET_OK;
        } catch (\Exception $e) {
            $ret->ret = \Constant::RET_DATA_CONFLICT;
        }

        return $ret;
    }

    /**
     * 删除角色.
     *
     * @param int $role_id
     *
     * @return \Authority\CommonRet $ret
     */
    public static function remove($role_id)
    {
        $ret = new CommonRet();

        $item = (new \Role())->find_one($role_id);
        if ($item) {
            $ret->ret = $item->delete() ? \Constant::RET_OK : \Constant::RET_SYS_ERROR;
        } else {
            $ret->ret = \Constant::RET_DATA_NO_FOUND;
        }

        return $ret;
    }

    /**
     * 更新角色.
     *
     * @param int             $role_id
     * @param \Authority\Role $role
     *
     * @return \Authority\CommonRet $ret
     */
    public static function update($role_id, Role $role)
    {
        $ret = new CommonRet();

        $item = (new \Role())->find_one($role_id);
        if ($item) {
            $data = [
                'type' => $role->type,
                'name' => $role->name,
                'rule' => $role->rule ? : '',
                'mtime' => date('Y-m-d H:i:s'),
            ];
            $ret->ret = $item->set($data)->save() ? \Constant::RET_OK : \Constant::RET_SYS_ERROR;
        } else {
            $ret->ret = \Constant::RET_DATA_NO_FOUND;
        }

        return $ret;

    }

    /**
     * 获取角色列表.
     *
     * @param \Authority\Search $search
     *
     * @return \Authority\RoleRet $ret
     */
    public static function getList(Search $search)
    {
        $ret = new RoleRet();
        $ret->ret = \Constant::RET_OK;

        $model = new \Role();
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
        $roles = [];
        if ($result) {
            foreach ($result as $item) {
                $roles[] = new Role(
                    [
                        'id' => $item['id'],
                        'type' => $item['type'],
                        'name' => $item['name'],
                        'rule' => $item['rule'],
                    ]
                );
            }
            $ret->roles = $roles;
        }

        return $ret;
    }
}
