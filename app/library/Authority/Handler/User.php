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

        $now = date('Y-m-d H:i:s');
        $data = [
            'username' => $user->username,
            'nickname' => $user->nickname ? $user->nickname : '',
            'password' => $user->password ? $user->password : '',
            'email' => $user->email ? $user->email : '',
            'telephone' => $user->telephone ? $user->telephone : '',
            'ctime' => $now,
            'mtime' => $now,
        ];

        $model = new \User();
        try {
            $model->create()->set($data)->save();
            $ret->ret = \Constant::RET_OK;
            $ret->data = json_encode(['id' => $model->id()]);
        } catch (\Exception $e) {
            $ret->ret = \Constant::RET_DATA_CONFLICT;
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

        $user = (new \User())->find_one($user_id);
        if ($user) {
            if ($user->delete()) {
                (new \AuthAssignment)->where('user_id', $user_id)->delete_many();
                $ret->ret = \Constant::RET_OK;
            } else {
                $ret->ret = \Constant::RET_SYS_ERROR;
            }
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

        $item = (new \User())->find_one($user_id);
        if ($item) {
            $data = ['mtime' => date('Y-m-d H:i:s')];
            if ($user->username) {
                $data['username'] = $user->username;
            }
            if ($user->nickname) {
                $data['nickname'] = $user->nickname;
            }
            if ($user->password) {
                $data['password'] = $user->password;
            }
            if ($user->email) {
                $data['email'] = $user->email;
            }
            if ($user->telephone) {
                $data['telephone'] = $user->telephone;
            }
            try {
                $item->set($data)->save();
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

        $item = (new \User())->where('username', $username)->find_one();
        if ($item) {
            $user->id = $item->id;
            $user->username = $item->username;
            $user->nickname = $item->nickname;
            $user->password = $item->password;
            $user->email = $item->email;
            $user->telephone = $item->telephone;
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

        $model = new \User();
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
        $users = [];
        if ($result) {
            foreach ($result as $item) {
                $users[] = new User(
                    [
                        'id' => $item['id'],
                        'username' => $item['username'],
                        'nickname' => $item['nickname'],
                        'password' => $item['password'],
                        'email' => $item['email'],
                        'telephone' => $item['telephone'],
                    ]
                );
            }
            $ret->users = $users;
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
            $item = (new \User())->find_one($user_id);
            if ($item) {
                $ret->user = new User(
                    [
                        'id' => $item->id,
                        'username' => $item->username,
                        'nickname' => $item->nickname,
                        'password' => $item->password,
                        'email' => $item->email,
                        'telephone' => $item->telephone,
                    ]
                );
            }
        }

        if (in_array('auth', $rlat)) {
            $groups = $super_points = $points = [];
            $items = (new \AuthItem())->getItems();

            // get user groups
            $group_ids = (new \AuthAssignment())->getAssignmentByUserId($user_id);
            if ($group_ids) {
                foreach ($group_ids as $group_id) {
                    $groups[] = new Group(
                        [
                            'id' => $items[$group_id]->id,
                            'type' => $items[$group_id]->type,
                            'name' => $items[$group_id]->name,
                            'description' => $items[$group_id]->description,
                        ]
                    );
                }

                if (in_array(\Constant::ADMIN, $group_ids)) {
                    foreach ($items as $item) {
                        if ($item->type == \Constant::POINT) {
                            $super_points[] = $item->data;
                        }
                    }
                } else {
                    foreach ((new \AuthItemChild())->getChildren($group_ids) as $key => $value) {
                        foreach ($value as $v) {
                            if ($items[$v]->type == \Constant::POINT) {
                                if ($items[$key]->type == \Constant::ORG) {
                                    $super_points[] = $items[$v]->data;
                                }
                                if ($items[$key]->type == \Constant::GROUP) {
                                    $points[] = $items[$v]->data;
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
        $items = (new \AuthItem())->getItems();

        // 若 user_id = 0 则作超级管理员处理
        $group_ids = $user_id ? (new \AuthAssignment())->getAssignmentByUserId($user_id) : [\Constant::ADMIN];
        if (in_array(\Constant::ADMIN, $group_ids)) {
            // 超级管理员 ADMIN
            foreach ($items as $item) {
                if ($item->type == \Constant::ORG || $item->type == \Constant::GROUP) {
                    $groups[] = new Group(
                        [
                            'id' => $item->id,
                            'type' => $item->type,
                            'name' => $item->name,
                            'description' => $item->description,
                        ]
                    );
                }
            }
        } else {
            // 获取用户所在的权限组
            $orgs = [];
            foreach ($group_ids as $group_id) {
                if ($items[$group_id]->type == \Constant::ORG) {
                    $orgs[] = $group_id;
                }
            }

            // 如果用户拥有权限组，构建返回对象
            if ($orgs) {
                $children = (new \AuthItemChild())->getChildren($orgs);
                foreach ($children as $value) {
                    foreach ($value as $v) {
                        if ($items[$v]->type == \Constant::GROUP) {
                            $groups[] = new Group(
                                [
                                    'id' => $items[$v]->id,
                                    'type' => $items[$v]->type,
                                    'name' => $items[$v]->name,
                                    'description' => $items[$v]->description,
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
        // 获取之前的用户组
        $origin = $auth_assignment->getAssignmentByUserId($user_id);

        // 构建要删除及添加的用户组
        if ($origin) {
            $deleted = array_diff($origin, $group_ids);
            if ($deleted && !$auth_assignment->clean()->where('user_id', $user_id)->where_in('item_id', $deleted)->delete_many()) {
                return false;
            }
        }

        $added = array_diff($group_ids, $origin);
        if ($added == [0] || !$added) {
            return true;
        }
        $now = date('Y-m-d H:i:s');
        foreach ($added as &$item) {
            $item = "({$item}, {$user_id}, '{$now}')";
        }

        return \ORM::raw_execute('INSERT INTO auth_assignment (item_id, user_id, ctime) VALUES '.implode(',', $added).';');
    }

}