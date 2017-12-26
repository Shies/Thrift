<?php

namespace Authority;

class UserHandler
{
    /**
     * 新增用户.
     *
     * @param \Authority\User $user
     *
     * @return \Authority\CommonRet $ret
     */
    public static function add(User $user)
    {
        $ret = new CommonRet;

        $id = (new \User())->add($user->username, $user->nickname ? : '', $user->email ? : '', $user->telephone ? : '');
        if ($id) {
            $ret->ret = \Constant::RET_OK;
            $ret->data = json_encode(['id' => $id]);
        } else {
            $ret->ret = \Constant::RET_DATA_CONFLICT;
            $ret->data = 'User exists!';
        }

        return $ret;
    }

    /**
     * 删除用户
     *
     * @param int $user_id
     *
     * @return \Authority\CommonRet $ret
     */
    public static function remove($user_id)
    {
        $ret = new CommonRet;

        $count = (new \User())->remove($user_id);
        if ($count) {
            // 删除用户所在的组关系
            (new \AuthAssignment)->removeByUserId($user_id);
            $ret->ret = \Constant::RET_OK;
        } else {
            $ret->ret = \Constant::RET_DATA_NO_FOUND;
        }

        return $ret;
    }

    /**
     * 编辑用户.
     *
     * @param int             $user_id
     * @param \Authority\User $user
     *
     * @return \Authority\CommonRet $ret
     */
    public static function update($user_id, User $user)
    {
        $ret = new CommonRet;

        $model = new \User();
        $item = $model->getById($user_id);
        if ($item) {
            $nickname = $user->nickname ? : $item['nickname'];
            $email = $user->email ? : $item['email'];
            $telephone = $user->telephone ? : $item['telephone'];
            try {
                $model->update($user_id, $nickname, $email, $telephone);
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
     * 根据用户名获取用户信息.
     *
     * @param string $username
     *
     * @return \Authority\User $user
     */
    public static function getByName($username)
    {
        $user = new User();

        $item = (new \User())->getByName($username);
        if ($item) {
            $user->id = $item['id'];
            $user->username = $item['username'];
            $user->nickname = $item['nickname'];
            $user->email = $item['email'];
            $user->telephone = $item['telephone'];
        }

        return $user;
    }

    /**
     * 获取用户列表.
     *
     * @param \Authority\Search $search
     *
     * @return \Authority\UserRet $ret
     */
    public static function getList(Search $search)
    {
        $ret = new UserRet();
        $ret->ret = \Constant::RET_OK;

        $users = [];
        $model = new \User();

        if (!$search->page && !$search->conditions) {
            // 无搜索条件 ^_^
            $users = $model->getAll();
            $ret->total = count($users);
        } else {
            // 有搜索条件 @_@
            $sql = 'SELECT * FROM user';
            $total_sql = 'SELECT COUNT(1) FROM user';
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
            $sql .= ' ORDER BY id DESC';
            if ($page = $search->page) {
                $pagesize = $search->pagesize ? : 20;
                $offset = ($page - 1) * $pagesize;
                $sql .= " LIMIT {$offset},{$pagesize}";
            }
            $users = $model->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        }

        if ($users) {
            $data = [];
            foreach ($users as $user) {
                $data[] = new User(
                    [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'nickname' => $user['nickname'],
                        'email' => $user['email'],
                        'telephone' => $user['telephone'],
                    ]
                );
            }
            $ret->users = $data;
        }

        return $ret;
    }

    /**
     * 获取用户及其权限相关信息.
     *
     * @param int   $user_id
     * @param array $rlat
     *
     * @return \Authority\UserRlatRet $ret
     */
    public static function getById($user_id, array $rlat)
    {
        $ret = new UserRlatRet();
        $ret->ret = \Constant::RET_OK;

        if (in_array('user', $rlat)) {
            $item = (new \User())->getById($user_id);
            if ($item) {
                $ret->user = new User(
                    [
                        'id' => $item['id'],
                        'username' => $item['username'],
                        'nickname' => $item['nickname'],
                        'email' => $item['email'],
                        'telephone' => $item['telephone'],
                    ]
                );
            }
        }

        if (in_array('auth', $rlat)) {
            $groups = $super_points = $points = [];
            $items = (new \AuthItem())->getAll();

            // get user groups
            $group_ids = (new \AuthAssignment())->getItemIdsByUserId($user_id);
            if ($group_ids) {
                foreach ($group_ids as $group_id) {
                    $groups[] = new Group(
                        [
                            'id' => $items[$group_id]['id'],
                            'type' => $items[$group_id]['type'],
                            'name' => $items[$group_id]['name'],
                            'description' => $items[$group_id]['description'],
                        ]
                    );
                }

                if (in_array(\Constant::ADMIN, $group_ids)) {
                    foreach ($items as $item) {
                        if ($item['type'] == \Constant::POINT) {
                            $super_points[] = $item['data'];
                        }
                    }
                } else {
                    $auth_item_child = new \AuthItemChild();
                    foreach ($group_ids as $group_id) {
                        foreach ($auth_item_child->getChildren($group_id) as $child) {
                            if ($items[$child]['type'] == \Constant::POINT) {
                                if ($items[$group_id]['type'] == \Constant::ORG) {
                                    $super_points[] = $items[$child]['data'];
                                }
                                if ($items[$group_id]['type'] == \Constant::GROUP) {
                                    $points[] = $items[$child]['data'];
                                }
                            }
                        }
                    }
                    $super_points = array_unique($super_points);
                    $points = array_diff(array_unique($points), $super_points);
                }
            }
            $ret->groups = $groups;
            $ret->super_points = $super_points;
            $ret->points = $points;
        }

        return $ret;
    }

    /**
     * 获取用户可分配的组.
     *
     * @param int $user_id
     *
     * @return \Authority\AssignableGroupRet $ret
     */
    public static function getAssignableGroup($user_id)
    {
        $ret = new AssignableGroupRet();
        $ret->ret = \Constant::RET_OK;

        $groups = [];
        $items = (new \AuthItem())->getAll();

        // 若 user_id = 0 则作超级管理员处理
        $group_ids = $user_id ? (new \AuthAssignment())->getItemIdsByUserId($user_id) : [\Constant::ADMIN];
        if (in_array(\Constant::ADMIN, $group_ids)) {
            // 超级管理员 ADMIN
            foreach ($items as $item) {
                if ($item['type'] == \Constant::ORG || $item['type'] == \Constant::GROUP) {
                    $groups[] = new Group(
                        [
                            'id' => $item['id'],
                            'type' => $item['type'],
                            'name' => $item['name'],
                            'description' => $item['description'],
                        ]
                    );
                }
            }
        } else {
            // 获取用户所在的权限组
            $auth_item_child = new \AuthItemChild();
            foreach ($group_ids as $group_id) {
                if ($items[$group_id]['type'] == \Constant::ORG) {
                    foreach ($auth_item_child->getChildren($group_id) as $child) {
                        if ($items[$child]['type'] == \Constant::GROUP) {
                            $groups[] = new Group(
                                [
                                    'id' => $items[$child]['id'],
                                    'type' => $items[$child]['type'],
                                    'name' => $items[$child]['name'],
                                    'description' => $items[$child]['description'],
                                ]
                            );
                        }
                    }
                }
            }
        }

        if ($groups) {
            $ret->groups = $groups;
        }

        return $ret;
    }

    /**
     * 给用户分配组.
     *
     * @param int[] $group_ids
     * @param int   $user_id
     *
     * @return bool
     */
    public static function assignGroup(array $group_ids, $user_id)
    {
        $auth_assignment = new \AuthAssignment();

        // 构建要删除及添加的用户组
        $origin = $auth_assignment->getItemIdsByUserId($user_id);
        $deleted = $origin ? array_diff($origin, $group_ids) : [];
        $added = ($group_ids == [0]) ? [] : array_diff($group_ids, $origin);
        
        return $auth_assignment->updateMulti($user_id, $added, $deleted);
    }
}
