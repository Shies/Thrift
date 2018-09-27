<?php

namespace Authority;

class GroupHandler
{
    /**
     * 新增权限组、角色.
     *
     * @param \Authority\Group $group
     * @param int              $parent
     *
     * @return \Authority\CommonRet $ret
     */
    public static function add(Group $group, $parent)
    {
        $ret = new CommonRet;

        $now = date('Y-m-d H:i:s');
        $data = [
            'name' => $group->name,
            'type' => $group->type,
            'rule_id' => $group->rule_id ? : null,
            'description' => $group->description,
            'ctime' => $now,
            'mtime' => $now,
        ];

        $model = new \AuthItem();
        try {
            $model->create()->set($data)->save();
            $ret->ret = \Constant::RET_OK;
            $ret->data = json_encode(['id' => $model->id()]);

            if ($parent && $group->type == \Constant::GROUP) {
                (new \AuthItemChild())->create()->set(['parent' => $parent, 'child' => $model->id()])->save();
            }
        } catch (\Exception $e) {
            $ret->ret = \Constant::RET_DATA_CONFLICT;
        }

        return $ret;
    }

    /**
     * 移除权限组/角色组.
     *
     * @param int $group_id
     *
     * @return \Authority\CommonRet $ret
     */
    public static function remove($group_id)
    {
        $ret = new CommonRet;

        $assignments = (new \AuthAssignment())->where('item_id', $group_id)->find_array();
        $user_ids = array_column($assignments, 'user_id');

        $item = (new \AuthItem())->where('type', [\Constant::GROUP, \Constant::ORG])->find_one($id);
        if ($item) {
            $item->delete();
            $ret->ret = \Constant::RET_OK;
            $ret->data = implode(',', $user_ids);
        } else {
            $ret->ret = \Constant::RET_DATA_NO_FOUND;
        }

        return $ret;
    }

    /**
     * 更新权限组/角色组.
     *
     * @param int $group_id
     *
     * @return \Authority\CommonRet $ret
     */
    public static function update($group_id, Group $group)
    {
        $ret = new CommonRet;

        $model = (new \AuthItem())->where_in('type', [\Constant::GROUP, \Constant::ORG])->find_one($group_id);
        if ($model) {
            $data = ['mtime' => date('Y-m-d H:i:s')];
            if ($group->name) {
                $data['name'] = $group->name;
            }
            if ($group->type) {
                $data['type'] = $group->type;
            }
            if ($group->description) {
                $data['description'] = $group->description;
            }
            try {
                $model->set($data)->save();
                $ret->ret = \Constant::RET_OK;
            } catch (\Exception $e) {
                $ret->ret = \Constant::RET_DATA_CONFLICT;
            }
        } else {
            $ret->ret = \Constant::RET_DATA_NO_FOUND;
        }

        return $ret;
    }

    /**
     * 获取权限组/角色组列表.
     *
     * @param \Authority\Search $search
     *
     * @return \Authority\GroupRet $ret
     */
    public static function getList(Search $search)
    {
        $ret = new GroupRet();

        $ret->ret = \Constant::RET_OK;
        $model = new \AuthItem();

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

        $result = $model->find_array();
        if ($result) {
            $groups = [];
            foreach ($result as $group) {
                $groups[] = new Group(
                    [
                        'id' => $group['id'],
                        'type' => $group['type'],
                        'name' => $group['name'],
                        'description' => $group['description'],
                    ]
                );
            }
            $ret->groups = $groups;
        }

        return $ret;
    }

    /**
     * 根据ID获取组信息.
     *
     * @param int   $group_id
     * @param array $rlat
     *
     * @return \Authority\GroupRlatRet $ret
     */
    public static function getById($group_id, array $rlat)
    {
        $ret = new GroupRlatRet();
        $ret->ret = \Constant::RET_OK;

        if (in_array('group', $rlat)) {
            // 获取组信息
            $item = (new \AuthItem())->where_in('type', [\Constant::ORG, \Constant::GROUP])->find_one($group_id);
            if ($item) {
                $ret->group = new Group(
                    [
                        'id' => $item->id,
                        'name' => $item->name,
                        'type' => $item->type,
                        'description' => $item->description,
                    ]
                );
            }
        }

        if (in_array('parent', $rlat)) {
            // 获取父级组信息
            $relation = (new \AuthItemChild())->where('child', $group_id)->select('parent')->find_one();
            if ($relation) {
                $item = (new \AuthItem())->where('type', \Constant::ORG)->find_one($relation->parent);
                if ($item) {
                    $ret->parent = new Group(
                        [
                            'id' => $item->id,
                            'name' => $item->name,
                            'type' => $item->type,
                            'description' => $item->description,
                        ]
                    );
                }
            }
        }

        if (in_array('users', $rlat)) {
            // 获取拥有该组权限的用户名
            $uids = (new \AuthAssignment())->where('item_id', $group_id)->select('user_id')->find_array();
            if ($uids) {
                $uids = array_column($uids, 'user_id');
                $users = (new \User())->where_in('id', $uids)->select('username')->find_array();
                $ret->users = array_column($users, 'username');
            }
        }

        if (in_array('points', $rlat)) {
            // 获取该组拥有的权限点
            $items = (new \AuthItem())->getItems();
            $children = (new \AuthItemChild())->getChildren($group_id);
            $points = [];
            foreach ($children[$group_id] as $child) {
                if ($items[$child]->type == \Constant::POINT) {
                    $points[] = $items[$child]->id;
                }
            }
            $ret->points = $points;
        }

        return $ret;
    }

    /**
     * 获取用户组可分配的权限点.
     *
     * @param int $group_id
     *
     * @return \Authority\AssignablePointRet $ret
     */
    public static function getAssignablePoint($group_id)
    {
        $ret = new AssignablePointRet();
        $ret->ret = \Constant::RET_OK;

        $items = (new \AuthItem())->getItems();

        if ($items[$group_id]->type == \Constant::ORG) {
            // 获取权限组的权限点
            $points = [];
            $auth_item_child = new \AuthItemChild();
            if ($group_id == \Constant::ADMIN) {
                foreach ($items as $item) {
                    if ($item->type == \Constant::POINT) {
                        $points[] = $item->id;
                    }
                }
            } else {
                $children = $auth_item_child->getChildren($group_id);
                foreach ($children[$group_id] as $id) {
                    if ($items[$id]->type == \Constant::POINT) {
                        $points[] = $id;
                    }
                }
            }

            // 获取权限点及其分类的关系
            $parents = $auth_item_child->getParent($points);
            $cate_map = [];
            foreach ($parents as $child => $parent) {
                foreach ($parent as $p) {
                    if ($items[$p]->type == \Constant::CATEGORY) {
                        if (!isset($cate_map[$p])) {
                            $cate_map[$p] = [];
                        }
                        $cate_map[$p][] = $child;
                    }
                }
            }

            // 构建返回对象
            $catepoints = [];
            foreach ($cate_map as $cate => $value) {
                $catepoint = new CategoryPoint();
                $catepoint->id = $cate;
                $catepoint->name = $items[$cate]->name;
                $children = [];
                foreach ($value as $v) {
                    $children[] = new Point(
                        [
                            'id' => $v,
                            'name' => $items[$v]->name,
                            'data' => $items[$v]->data,
                        ]
                    );
                }
                $catepoint->children = $children;
                $catepoints[] = $catepoint;
            }
            $ret->points = $catepoints;
        }

        return $ret;
    }

    /**
     * 给组分配权限点.
     *
     * @param int[] $points
     * @param int   $group_id
     *
     * @return CommonRet $ret
     */
    public static function assignPoint(array $points, $group_id)
    {
        $ret = new CommonRet();
        $ret->ret = \Constant::RET_OK;

        $auth_item_child = new \AuthItemChild();
        // 获取组之前的权限点
        $children = $auth_item_child->where('parent', $group_id)
            ->join('auth_item', 'auth_item.id = auth_item_child.child')
            ->select(['child','type'])
            ->find_array();
        $group_ids = $origin = $child_group = [];
        $group_ids[] = $group_id;
        foreach ($children as $item) {
            if ($item['type'] == \Constant::POINT) {
                $origin[] = $item['child'];
            }
            if ($item['type'] == \Constant::GROUP) {
                $child_group[] = $item['child'];
            }
        }

        // 构建要删除及添加的权限点
        if ($origin) {
            $deleted = array_diff($origin, $points);
            if ($deleted) {
                if ($child_group) {
                    $group_ids = array_merge($group_ids, $child_group);
                }
                if (!$auth_item_child->clean()->where_in('parent', $group_ids)->where_in('child', $deleted)->delete_many()) {
                    $ret->ret = \Constant::RET_SYS_ERROR;
                    return $ret;
                }
            }
        }

        $user_ids = (new \AuthAssignment())->where_in('item_id', $group_ids)->select('user_id')->find_array();
        $user_ids = array_column($user_ids, 'user_id');
        $data = implode(',', $user_ids);

        $added = array_diff($points, $origin);
        if ($added == [0] || !$added) {
            $ret->data = $data;
            return $ret;
        }

        foreach ($added as &$item) {
            $item = "({$group_id}, {$item})";
        }

        if (!\ORM::raw_execute('INSERT INTO auth_item_child (parent, child) VALUES '.implode(',', $added).';')) {
            $ret->ret = \Constant::RET_SYS_ERROR;
        }
        $ret->data = $data;

        return $ret;
    }
}