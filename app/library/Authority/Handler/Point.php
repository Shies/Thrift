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
        $ret = new CommonRet;

        $now = date('Y-m-d H:i:s');
        $data = [
            'name' => $point->name,
            'type' => \Constant::POINT,
            'data' => $point->data,
            'rule_id' => $point->rule_id ? : null,
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
    public static function remove($point_id)
    {
        $ret = new CommonRet;

        $item = (new \AuthItem())->where('type', \Constant::POINT)->find_one($id);
        if ($item) {
            $item->delete();
            $ret->ret = \Constant::RET_OK;
        } else {
            $ret->ret = \Constant::RET_DATA_NO_FOUND;
        }

        return $ret;
    }
}