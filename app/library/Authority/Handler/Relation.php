<?php

namespace Authority;

class RelationHandler
{
    /**
     * 新增关系.
     *
     * @param int $parent
     * @param int $child
     *
     * @return \Authority\CommonRet $ret
     */
    public static function add($parent, $child)
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
    public static function remove($parent, $child)
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
}