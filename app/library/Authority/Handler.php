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

    /******************************************************************/
    /**************************** Rule ********************************/
    /******************************************************************/

    /**
     * 新增规则.
     *
     * @param \Authority\Rule $rule
     *
     * @return \Authority\CommonRet $ret
     */
    public function addRule(Rule $rule)
    {
        return RuleHandler::add($rule);
    }

    /**
     * 删除规则.
     *
     * @param int $rule_id
     *
     * @return \Authority\CommonRet $ret
     */
    public function rmRule($rule_id)
    {
        return RuleHandler::remove($rule_id);
    }

    /******************************************************************/
    /**************************** User ********************************/
    /******************************************************************/

    /**
     * 新增用户.
     *
     * @param \Authority\User $user
     *
     * @return \Authority\CommonRet $ret
     */
    public function addUser(User $user)
    {
        return UserHandler::add($user);
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
        return UserHandler::remove($user_id);
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
        return UserHandler::update($user_id, $user);
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
        return UserHandler::getByName($username);
    }

    /**
     * 获取用户列表.
     *
     * @param \Authority\Search $search
     *
     * @return \Authority\UserRet $ret
     */
    public function getUsers(Search $search)
    {
        return UserHandler::getList($search);
    }

    /**
     * 获取用户及其权限相关信息.
     *
     * @param int   $user_id
     * @param array $rlat
     *
     * @return \Authority\UserRlatRet $ret
     */
    public function getUserById($user_id, array $rlat)
    {
        return UserHandler::getById($user_id, $rlat);
    }

    /**
     * 获取用户可分配的组.
     *
     * @param int $user_id
     *
     * @return \Authority\AssignableGroupRet $ret
     */
    public function getAssignableGroup($user_id)
    {
        return UserHandler::getAssignableGroup($user_id);
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
        return UserHandler::assignGroup($group_ids, $user_id);
    }

    /******************************************************************/
    /**************************** Point *******************************/
    /******************************************************************/

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
        return PointHandler::add($point, $cate_id);
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
        return PointHandler::update($point_id, $point);
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
        return PointHandler::remove($point_id);
    }

    /******************************************************************/
    /************************** Category ******************************/
    /******************************************************************/

    /**
     * 新增权限点分类.
     *
     * @param \Authority\Category $category
     *
     * @return \Authority\CommonRet $ret
     */
    public function addCategory(Category $category)
    {
        return CategoryHandler::add($category);
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
        return CategoryHandler::remove($cate_id);
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
        return CategoryHandler::update($cate_id, $category);
    }

    /**
     * 获取所有权限分类.
     *
     * @return \Authority\CategoryRet $ret
     */
    public function getCategories()
    {
        return CategoryHandler::getList();
    }

    /******************************************************************/
    /**************************** Group *******************************/
    /******************************************************************/

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
        return GroupHandler::add($group, $parent);
    }

    /**
     * 移除权限组/角色组.
     *
     * @param int $group_id
     *
     * @return \Authority\CommonRet $ret
     */
    public function rmGroup($group_id)
    {
        return GroupHandler::remove($group_id);
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
        return GroupHandler::update($group_id, $group);
    }

    /**
     * 获取权限组/角色组列表.
     *
     * @param \Authority\Search $search
     *
     * @return \Authority\GroupRet $ret
     */
    public function getGroups(Search $search)
    {
        return GroupHandler::getList($search);
    }

    /**
     * 根据ID获取组信息.
     *
     * @param int   $group_id
     * @param array $rlat
     *
     * @return \Authority\GroupRlatRet $ret
     */
    public function getGroupById($group_id, array $rlat)
    {
        return GroupHandler::getById($group_id, $rlat);
    }

    /**
     * 获取用户组可分配的权限点.
     *
     * @param int $group_id
     *
     * @return \Authority\AssignablePointRet $ret
     */
    public function getAssignablePoint($group_id)
    {
        return GroupHandler::getAssignablePoint($group_id);
    }

    /**
     * 给组分配权限点.
     *
     * @param int[] $points
     * @param int   $group_id
     *
     * @return CommonRet $ret
     */
    public function assignPoint2Group(array $points, $group_id)
    {
        return GroupHandler::assignPoint($points, $group_id);
    }

    /******************************************************************/
    /**************************** Relation ****************************/
    /******************************************************************/

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
        return RelationHandler::add($parent, $child);
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
        return RelationHandler::remove($parent, $child);
    }


    /******************************************************************/
    /************************** ResourceAttr **************************/
    /******************************************************************/

    /**
     * 新增资源权限属性.
     *
     * @param \Authority\ResourceAttr $resource_attr
     *
     * @return \Authority\CommonRet $ret
     */
    public function addResourceAttr(ResourceAttr $resource_attr)
    {
        return ResourceAttrHandler::add($resource_attr);
    }

    /**
     * 删除资源权限属性.
     *
     * @param int $resource_attr_id
     *
     * @return \Authority\CommonRet $ret
     */
    public function rmResourceAttr($resource_attr_id)
    {
        return ResourceAttrHandler::remove($resource_attr_id);
    }

    /**
     * 更新资源权限属性.
     *
     * @param int                     $resource_attr_id
     * @param \Authority\ResourceAttr $resource_attr
     *
     * @return \Authority\CommonRet $ret
     */
    public function updateResourceAttr($resource_attr_id, ResourceAttr $resource_attr)
    {
        return ResourceAttrHandler::update($resource_attr_id, $resource_attr);
    }

    /**
     * 获取资源权限属性.
     *
     * @param \Authority\Search $search
     *
     * @return \Authority\ResourceAttrRet $ret
     */
    public function getResourceAttrs(Search $search)
    {
        return ResourceAttrHandler::getList($search);
    }

    /******************************************************************/
    /****************************** Role ******************************/
    /******************************************************************/

    /**
     * 新增角色.
     *
     * @param \Authority\Role $role
     *
     * @return \Authority\CommonRet $ret
     */
    public function addRole(Role $role)
    {
        return RoleHandler::add($role);
    }

    /**
     * 删除角色.
     *
     * @param int $role_id
     *
     * @return \Authority\CommonRet $ret
     */
    public function rmRole($role_id)
    {
        return RoleHandler::remove($role_id);
    }

    /**
     * 更新角色.
     *
     * @param int             $role_id
     * @param \Authority\Role $role
     *
     * @return \Authority\CommonRet $ret
     */
    public function updateRole($role_id, Role $role)
    {
        return RoleHandler::update($role_id, $role);
    }

    /**
     * 获取角色列表.
     *
     * @param \Authority\Search $search
     *
     * @return \Authority\RoleRet $ret
     */
    public function getRoles(Search $search)
    {
        return RoleHandler::getList($search);
    }

    /******************************************************************/
    /*************************** RoleMember ***************************/
    /******************************************************************/

    /**
     * 新增角色成员.
     *
     * @param int $role_id
     * @param int $user_id
     *
     * @return \Authority\CommonRet $ret
     */
    public function addRoleMember($role_id, $user_id)
    {
        return RoleMemberHandler::add($role_id, $user_id);
    }

    /**
     * 删除角色成员.
     *
     * @param int $role_id
     * @param int $user_id
     *
     * @return \Authority\CommonRet $ret
     */
    public function rmRoleMember($role_id, $user_id)
    {
        return RoleMemberHandler::remove($role_id, $user_id);
    }
}
