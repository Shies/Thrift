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
            (new \AuthItemChild())->add($parent, $child);
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

        $count = (new \AuthItemChild())->remove($parent, $child);
        $ret->ret = $count ? \Constant::RET_OK : \Constant::RET_DATA_NO_FOUND;

        return $ret;
    }
}
