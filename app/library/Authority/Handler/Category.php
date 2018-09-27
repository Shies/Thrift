<?php

namespace Authority;

class CategoryHandler
{
    /**
     * 新增权限点分类.
     *
     * @param \Authority\Category $category
     *
     * @return \Authority\CommonRet $ret
     */
    public static function add(Category $category)
    {
        $ret = new CommonRet;

        $now = date('Y-m-d H:i:s');
        $data = [
            'name' => $category->name,
            'type' => \Constant::CATEGORY,
            'rule_id' => $category->rule_id ? : null,
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
    public static function remove($cate_id)
    {
        $ret = new CommonRet;

        $item = (new \AuthItem())->where('type', \Constant::CATEGORY)->find_one($id);
        if ($item) {
            $item->delete();
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
    public static function update($cate_id, Category $category)
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
    public static function getList()
    {
        $ret = new CategoryRet();

        $ret->ret = \Constant::RET_OK;
        $model = new \AuthItem();

        if ($conditions = $search->conditions) {
            foreach ($conditions as $condition) {
                $expr = $condition->expr ? : 'where';
                $model->$expr($condition->field, $condition->value);
            }
        }
        $ret->total = $model->where('type', \Constant::CATEGORY)->count();

        $model->clean()->where('type', \Constant::CATEGORY);
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

        $result = $model->find_array();
        if ($result) {
            $categories = [];
            foreach ($result as $cate) {
                $categories[] = new Category(
                    [
                        'id' => $cate['id'],
                        'name' => $cate['name'],
                        'description' => $cate['description'],
                    ]
                );
            }
            $ret->categories = $categories;
        }

        return $ret;
    }

}
