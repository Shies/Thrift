<?php

namespace Authority;

class Handler implements AuthorityServiceIf
{
    public function __construct()
    {
        $db = include ROOT.'/config/database.php';
        foreach ($db as $name => $option) {
            \ORM::configure($option, null, $name);
        }
    }

    /**
     * 获取用户的所在组及权限点信息.
     *
     * @param int $uid
     *
     * @return \Authority\UserAuthRet
     */
    public function getAuth($uid)
    {
        $userAuth = new UserAuthRet();
        $userAuth->ret = \Constant::RET_OK;

        $groups = $super_points = $points = [];
        $items = (new \AuthItem())->getItems();

        // get user groups
        $group_ids = (new \AuthAssignment())->getAssignmentByUserId($uid);
        if ($group_ids) {
            foreach ($group_ids as $group_id) {
                $groups[] = new Group(
                    [
                        'id' => $items[$group_id]->id,
                        'type' => $items[$group_id]->type,
                        'name' => $items[$group_id]->name,
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
        $userAuth->groups = $groups;
        $userAuth->super_points = $super_points;
        $userAuth->points = $points;

        return $userAuth;
    }

    /**
     * 新增用户.
     *
     * @param \Authority\User $user
     *
     * @return \Authority\CommonRet $ret
     */
    public function addUser(User $user)
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
    public function rmUser($user_id)
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
    public function updateUser($user_id, User $user)
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
     * 根据ID获取用户信息.
     *
     * @param int $user_id
     *
     * @return \Authority\User $user
     */
    public function getUserById($user_id)
    {
        $user = new User();

        $item = (new \User())->find_one($user_id);
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
     * 根据用户名获取用户信息.
     *
     * @param string $username
     *
     * @return \Authority\User $user
     */
    public function getUserByName($username)
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
     * @return \Authority\UserRet
     */
    public function getUsers($page, $pagesize)
    {
        $ret = new UserRet();
        $ret->ret = \Constant::RET_OK;

        $model = new \User();
        $ret->total = $model->count();
        $model->clean();
        if ($page) {
            $pagesize = $pagesize ? $pagesize : 20;
            $model->offset(($page - 1) * $pagesize)->limit($pagesize);
        }
        $result = $model->order_by_desc('id')->find_many();
        $users = [];
        if ($result) {
            foreach ($result as $item) {
                $users[] = new User(
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
            $ret->users = $users;
        }

        return $ret;
    }

    /**
     * 新增权限点.
     *
     * @param \Authority\Point $point
     * @param int              $cate_id
     *
     * @return \Authority\CommonRet $ret
     */
    public function addPoint(Point $point, $cate_id)
    {
        $ret = new CommonRet;

        $now = date('Y-m-d H:i:s');
        $data = [
            'name' => $point->name,
            'type' => \Constant::POINT,
            'data' => $point->data,
            'description' => $point->description,
            'ctime' => $now,
            'mtime' => $now,
        ];

        $model = new \AuthItem();

        try {
            $model->create()->set($data)->save();
            if ($cate_id) {
                (new \AuthItemChild())->create()->set(['parent' => $cate_id, 'child' => $model->id()])->save();
                $ret->ret = \Constant::RET_OK;
                $ret->data = json_encode(['id' => $model->id()]);
            }
        } catch (\Exception $e) {
            $ret->ret = \Constant::RET_DATA_CONFLICT;
            $ret->data = 'Point data conflict';
        }

        return $ret;
    }

    /**
     * 编辑权限点信息.
     *
     * @param int              $point_id
     * @param \Authority\Point $point
     *
     * @return \Authority\CommonRet $ret
     */
    public function updatePoint($point_id, Point $point)
    {
        $ret = new CommonRet;

        $model = (new \AuthItem())->where('type', \Constant::POINT)->find_one($point_id);
        if ($model) {
            $data = ['mtime' => date('Y-m-d H:i:s')];
            if ($point->name) {
                $data['name'] = $point->name;
            }
            if ($point->data) {
                $data['data'] = $point->data;
            }
            if ($point->description) {
                $data['description'] = $point->description;
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
     * 移除权限点.
     *
     * @param int $point_id
     *
     * @return \Authority\CommonRet $ret
     */
    public function rmPoint($point_id)
    {
        $ret = new CommonRet;

        if ($this->removeItem($point_id, \Constant::POINT)) {
            (new \AuthItemChild())->where('child', $point_id)->delete_many();
            $ret->ret = \Constant::RET_OK;
        } else {
            $ret->ret = \Constant::RET_DATA_NO_FOUND;
        }

        return $ret;
    }

    /**
     * 新增权限点分类.
     *
     * @param \Authority\Category $category
     *
     * @return \Authority\CommonRet $ret
     */
    public function addCategory(Category $category)
    {
        $ret = new CommonRet;

        $now = date('Y-m-d H:i:s');
        $data = [
            'name' => $category->name,
            'type' => \Constant::CATEGORY,
            'description' => $category->description,
            'ctime' => $now,
            'mtime' => $now,
        ];

        $model = new \AuthItem();
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
     * 移除权限点分类.
     *
     * @param int $cate_id
     *
     * @return \Authority\CommonRet $ret
     */
    public function rmCategory($cate_id)
    {
        $ret = new CommonRet;

        if ($this->removeItem($cate_id, \Constant::CATEGORY)) {
            (new \AuthItemChild())->where('parent', $cate_id)->delete_many();
            $ret->ret = \Constant::RET_OK;
        } else {
            $ret->ret = \Constant::RET_DATA_NO_FOUND;
        }

        return $ret;
    }

    /**
     * 编辑权限点分类信息.
     *
     * @param int                 $cate_id
     * @param \Authority\Category $category
     *
     * @return \Authority\CommonRet $ret
     */
    public function updateCategory($cate_id, Category $category)
    {
        $ret = new CommonRet;

        $model = (new \AuthItem())->where('type', \Constant::CATEGORY)->find_one($cate_id);
        if ($model) {
            $data = ['mtime' => date('Y-m-d H:i:s')];
            if ($category->name) {
                $data['name'] = $category->name;
            }
            if ($category->description) {
                $data['description'] = $category->description;
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
     * 获取所有权限分类.
     *
     * @return \Authority\CategoryRet $ret
     */
    public function getCategories()
    {
        $ret = new CategoryRet();

        $ret->ret = \Constant::RET_OK;
        $model = new \AuthItem();
        $ret->total = $model->where('type', \Constant::CATEGORY)->count();
        $result = $model->clean()->where('type', \Constant::CATEGORY)->find_many();
        if ($result) {
            $categories = [];
            foreach ($result as $cate) {
                $categories[] = new Category(
                    [
                        'id' => $cate->id,
                        'name' => $cate->name,
                        'description' => $cate->description
                    ]
                );
            }
            $ret->categories = $categories;
        }

        return $ret;
    }

    /**
     * 新增权限组、角色.
     *
     * @param \Authority\Group $group
     * @param int              $parent
     *
     * @return \Authority\CommonRet $ret
     */
    public function addGroup(Group $group, $parent)
    {
        $ret = new CommonRet;

        $now = date('Y-m-d H:i:s');
        $data = [
            'name' => $group->name,
            'type' => $group->type,
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
     * @return bool
     */
    public function rmGroup($group_id)
    {
        $ret = new CommonRet;

        if ($this->removeItem($group_id)) {
            (new \AuthItemChild())->where_any_is([['parent' => $group_id], ['child' => $group_id]])->delete_many();
            $ret->ret = \Constant::RET_OK;
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
    public function updateGroup($group_id, Group $group)
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
     * @param int $type
     * @param int $page
     * @param int $page
     *
     * @return \Authority\GroupRet $ret
     */
    public function getGroups($type, $page, $pagesize)
    {
        $ret = new GroupRet();

        $ret->ret = \Constant::RET_OK;
        $model = new \AuthItem();
        if ($type) {
            $model->where('type', $type);
        }
        $ret->total = $model->count();

        $model->clean();
        if ($type) {
            $model->where('type', $type);
        }
        if ($page) {
            $pagesize = $pagesize ? $pagesize : 20;
            $model->offset(($page - 1) * $pagesize)->limit($pagesize);
        }
        $result = $model->find_many();
        if ($result) {
            $groups = [];
            foreach ($result as $group) {
                $groups[] = new Group(
                    [
                        'id' => $group->id,
                        'type' => $group->type,
                        'name' => $group->name,
                        'description' => $group->description,
                    ]
                );
            }
            $ret->groups = $groups;
        }

        return $ret;
    }

    /**
     * 新增关系.
     *
     * @param int $parent
     * @param int $child
     *
     * @return \Authority\CommonRet $ret
     */
    public function addRelation($parent, $child)
    {
        $ret = new CommonRet();

        try {
            (new \AuthItemChild())->create()->set(['parent' => $parent, 'child' => $child])->save();
            $ret->ret = \Constant::RET_OK;
        } catch (\Exception $e) {
            $ret->ret = \Constant::RET_DATA_CONFLICT;
        }

        return $ret;
    }

    /**
     * 移除关系.
     *
     * @param int $parent
     * @param int $child
     *
     * @return \Authority\CommonRet $ret
     */
    public function rmRelation($parent, $child)
    {
        $ret = new CommonRet();

        $relation = (new \AuthItemChild())
            ->where(['parent' => $parent, 'child' => $child])
            ->find_one();

        if ($relation) {
            $ret->ret = $relation->delete() ? \Constant::RET_OK : \Constant::RET_SYS_ERROR;
        } else {
            $ret->ret = \Constant::RET_DATA_NO_FOUND;
        }

        return $ret;
    }

    /**
     * 获取用户可分配的组.
     *
     * @param int $uid
     *
     * @return \Authority\AssignableGroupRet
     */
    public function getAssignableGroup($uid)
    {
        $ret = new AssignableGroupRet();
        $ret->ret = \Constant::RET_OK;

        $groups = [];
        $items = (new \AuthItem())->getItems();

        // 若uid = 0则作超级管理员处理
        $group_ids = $uid ? (new \AuthAssignment())->getAssignmentByUserId($uid) : [\Constant::ADMIN];
        if (in_array(\Constant::ADMIN, $group_ids)) {
            // 超级管理员 ADMIN
            foreach ($items as $item) {
                if ($item->type == \Constant::ORG || $item->type == \Constant::GROUP) {
                    $groups[] = new Group(
                        [
                            'id' => $item->id,
                            'type' => $item->type,
                            'name' => $item->name,
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
     * 获取用户组当前权限点
     *
     * @param int $group_id
     *
     * @return \Authority\GroupPointRet
     */
    public function getGroupPointById($group_id)
    {
        $ret = new GroupPointRet();
        $ret->ret = \Constant::RET_OK;

        $items = (new \AuthItem())->getItems();

        $auth_item_child = new \AuthItemChild();
        $children = $auth_item_child->getChildren($group_id);
        $points = [];
        foreach ($children[$group_id] as $child) {
            if ($items[$child]->type == \Constant::POINT) {
                $points[] = $items[$child]->id;
            }
        }
        $ret->points = $points;

        $parents = $auth_item_child->getParent($group_id);
        if ($parents) {
            foreach ($parents[$group_id] as $parent) {
                if ($items[$parent]->type == \Constant::ORG) {
                    $ret->parent = new Group(
                        [
                            'id' => $parent,
                            'name' => $items[$parent]->name,
                            'type' => \Constant::ORG,
                            'description' => $items[$parent]->description,
                        ]
                    );
                    break;
                }
            }
        }

        return $ret;
    }

    /**
     * 获取用户组可分配的权限点.
     *
     * @param int $group_id
     *
     * @return \Authority\AssignablePointRet
     */
    public function getAssignablePoint($group_id)
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
     * @return bool
     */
    public function assignPoint2Group(array $points, $group_id)
    {
        $auth_item_child = new \AuthItemChild();
        // 获取组之前的权限点
        $children = $auth_item_child->where('parent', $group_id)
            ->join('auth_item', 'auth_item.id = auth_item_child.child and auth_item.type ='.\Constant::POINT)
            ->select('child')
            ->find_array();
        $origin = array_column($children, 'child');

        // 构建要删除及添加的权限点
        if ($origin) {
            $deleted = array_diff($origin, $points);
            if ($deleted && !$auth_item_child->clean()->where('parent', $group_id)->where_in('child', $deleted)->delete_many()) {
                return false;
            }
        }

        $added = array_diff($points, $origin);
        if ($added == [0] || !$added) {
            return true;
        }
        foreach ($added as &$item) {
            $item = "({$group_id}, {$item})";
        }

        return \ORM::raw_execute('INSERT INTO auth_item_child (parent, child) VALUES '.implode(',', $added).';');
    }

    /**
     * 给用户分配组.
     *
     * @param int[] $group_ids
     * @param int   $user_id
     *
     * @return bool
     */
    public function assignGroup2User(array $group_ids, $user_id)
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

    /**
     * 移除AuthItem.
     *
     * @return bool
     */
    protected function removeItem($id, $type = false)
    {
        $model = new \AuthItem();
        if ($type) {
            $model->where('type', $type);
        }
        $item = $model->find_one($id);

        return $item ? $item->delete() : false;
    }
}
