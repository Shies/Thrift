<?php

namespace Authority;

class RoleMemberHandler
{
    /**
     * 新增角色成员.
     *
     * @param int $role_id
     * @param int $user_id
     *
     * @return \Authority\CommonRet $ret
     */
    public static function add($role_id, $user_id)
    {
        $ret = new CommonRet();

        try {
            (new \RoleMember())->add($role_id, $user_id);
            $ret->ret = \Constant::RET_OK;
        } catch (\Exception $e) {
            $ret->ret = \Constant::RET_DATA_CONFLICT;
        }

        return $ret;
    }

    /**
     * 删除角色成员.
     *
     * @param int $role_id
     * @param int $user_id
     *
     * @return \Authority\CommonRet $ret
     */
    public static function remove($role_id, $user_id)
    {
        $ret = new CommonRet();

        $count = (new \RoleMember())->remove($role_id, $user_id);
        $ret->ret = $count ? \Constant::RET_OK : \Constant::RET_DATA_NO_FOUND;

        return $ret;
    }
}
