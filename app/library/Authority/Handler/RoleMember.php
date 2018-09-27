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
            $data = [
                'role_id' => $role_id,
                'user_id' => $user_id,
                'ctime' => date('Y-m-d H:i:s'),
            ];
            (new \RoleMember())->create()->set($data)->save();
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

        $item = (new \RoleMember())->where(['role_id' => $role_id, 'user_id' => $user_id])->find_one();
        if ($item) {
            $ret->ret = $item->delete() ? \Constant::RET_OK : \Constant::RET_SYS_ERROR;
        } else {
            $ret->ret = \Constant::RET_DATA_NO_FOUND;
        }

        return $ret;
    }
}
