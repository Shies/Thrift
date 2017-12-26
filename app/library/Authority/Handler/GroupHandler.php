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
        $ret = new CommonRet();

        $model = new \AuthItem();
        try {
            $id = $model->add($group->name, $group->type, $group->rule_id ? : 0, $group->description, '');
            $ret->ret = \Constant::RET_OK;
            $ret->data = json_encode(['id' => $id]);

            if ($parent && $group->type == \Constant::GROUP) {
                (new \AuthItemChild())->add($parent, $id);
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
        $ret = new CommonRet();

        $model = new \AuthItem();
        $item = $model->getById($group_id);
        if ($item && in_array($item['type'], [\Constant::GROUP, \Constant::ORG])) {
            $count = $model->remove($item['type'], $group_id);
            if (!$count) {
                (new \AuthItemChild())->remove($group_id, $group_id);   // delete auth_item_child
                $ret->ret = \Constant::RET_DATA_NO_FOUND;
            } else {
                $ret->ret = \Constant::RET_OK;
            }
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
        $ret = new CommonRet();

        $model = new \AuthItem();
        $item = $model->getById($group_id);
        if (in_array($item['type'], [\Constant::GROUP, \Constant::ORG])) {
            $name = $group->name ?: $item['name'];
            $description = $group->description ?: $item['description'];
            try {
                $model->update($group_id, $item['type'], $name, $description);
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

        $auth_item = new \AuthItem();

        if (in_array('group', $rlat)) {
            // 获取组信息
            $item = $auth_item->getById($group_id);
            if ($item && in_array($item['type'], [\Constant::ORG, \Constant::GROUP])) {
                $ret->group = new Group(
                    [
                        'id' => $item['id'],
                        'name' => $item['name'],
                        'type' => $item['type'],
                        'description' => $item['description'],
                    ]
                );
            }
        }

        if (in_array('parent', $rlat)) {
            // 获取父级组信息
            $parent_ids = (new \AuthItemChild())->getParent($group_id);
            if ($parent_ids) {
                $parent = $auth_item->getById(current($parent_ids));
                if ($parent && $parent['type'] == \Constant::ORG) {
                    $ret->parent = new Group(
                        [
                            'id' => $parent['id'],
                            'name' => $parent['name'],
                            'type' => $parent['type'],
                            'description' => $parent['description'],
                        ]
                    );
                }
            }
        }

        if (in_array('users', $rlat)) {
            // 获取拥有该组权限的用户名
            $uids = (new \AuthAssignment())->getUserIdsByItemId($group_id);
            if ($uids) {
                $users = (new \User())->getMulti($uids);
                $ret->users = array_column($users, 'username');
            }
        }

        if (in_array('points', $rlat)) {
            // 获取该组拥有的权限点
            $items = (new \AuthItem())->getAll();
            $children = (new \AuthItemChild())->getChildren($group_id);
            $points = [];
            foreach ($children as $child) {
                if ($items[$child]['type'] == \Constant::POINT) {
                    $points[] = $items[$child]['id'];
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

        $items = (new \AuthItem())->getAll();
        $relations = (new \AuthItemChild())->getRelations();

        if ($items[$group_id]['type'] == \Constant::ORG) {
            // 获取权限组的权限点
            $points = [];
            if ($group_id == \Constant::ADMIN) {
                foreach ($items as $item) {
                    if ($item['type'] == \Constant::POINT) {
                        $points[] = $item['id'];
                    }
                }
            } else {
                foreach ($relations['parents'][$group_id] as $id) {
                    if ($items[$id]['type'] == \Constant::POINT) {
                        $points[] = $id;
                    }
                }
            }

            // 获取权限点及其分类的关系
            $cate_point_ids = [];
            foreach ($points as $point) {
                foreach ($relations['children'][$point] as $parent) {
                    if ($items[$parent]['type'] == \Constant::CATEGORY) {
                        if (!isset($cate_point_ids[$parent])) {
                            $cate_point_ids[$parent] = [];
                        }
                        $cate_point_ids[$parent][] = $point;
                    }
                }
            }

            $catepoints = [];
            foreach ($cate_point_ids as $parent => $points) {
                $catepoint = new CategoryPoint();
                $catepoint->id = $parent;
                $catepoint->name = $items[$parent]['name'];
                $children = [];
                foreach ($points as $point) {
                    $children[] = new Point(
                        [
                            'id' => $point,
                            'name' => $items[$point]['name'],
                            'data' => $items[$point]['data'],
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

        $items = (new \AuthItem())->getAll();
        $auth_item_child = new \AuthItemChild();
        $children = $auth_item_child->getChildren($group_id);

        // group_ids - 受影响到的组 origin_points - 原始权限点
        $group_ids = $origin_points = [];
        $group_ids[] = $group_id;
        foreach ($children as $child) {
            if ($items[$child]['type'] == \Constant::POINT) {
                $origin_points[] = $child;
            }
            if ($items[$child]['type'] == \Constant::GROUP) {
                $group_ids[] = $child;
            }
        }
        // var_dump($group_ids, $origin_points);

        // 构建要删除的权限点
        if ($origin_points) {
            $deleted = array_diff($origin_points, $points);
            if ($deleted) {
                // 删除受影响到的权限组的权限点
                $auth_item_child->removeMulti($group_ids, $deleted);
            }
        }

        // 构建要添加的权限点，如果要清空所有权限点 $points = [0]
        $added = array_diff($points, $origin_points);
        if ($points == [0] || !$added) {
            // 没有要添加的权限点，直接返回
            return $ret;
        }

        $auth_item_child->add($group_id, $added);

        return $ret;
    }
}
