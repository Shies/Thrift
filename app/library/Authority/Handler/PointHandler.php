<?php

namespace Authority;

class PointHandler
{
    /**
     * 新增权限点.
     *
     * @param \Authority\Point $point
     * @param int              $cate_id
     *
     * @return \Authority\CommonRet $ret
     */
    public static function add(Point $point, $cate_id)
    {
        $ret = new CommonRet();

        $id = (new \AuthItem())->add($point->name, \Constant::POINT, $point->rule_id, $point->description, $point->data);
        if ($id) {
            $ret->ret = \Constant::RET_OK;
            $ret->data = json_encode(['id' => $id]);
            if ($cate_id) {
                (new \AuthItemChild())->add($cate_id, $id);
            }
        } else {
            $ret->ret = \Constant::RET_DATA_CONFLICT;
            $ret->data = 'Point exists!';
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
    public static function update($point_id, Point $point)
    {
        $ret = new CommonRet();

        $model = new \AuthItem();
        $item = $model->getById($point_id);
        if ($item['type'] == \Constant::POINT) {
            $name = $point->name ?: $item['name'];
            $data = $point->data ?: $item['data'];
            $description = $point->description ?: $item['description'];
            try {
                $model->update($point_id, \Constant::POINT, $name, $description, $data);
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
    public static function remove($point_id)
    {
        $ret = new CommonRet();

        $count = (new \AuthItem())->remove(\Constant::POINT, $point_id);
        if ($count) {
            (new \AuthItemChild())->remove(null, $point_id);    // delete auth_item_child
            $ret->ret = \Constant::RET_OK;
        } else {
            $ret->ret = \Constant::RET_DATA_NO_FOUND;
        }

        return $ret;
    }
}
