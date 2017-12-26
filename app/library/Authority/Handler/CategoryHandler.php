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
        $ret = new CommonRet();

        $id = (new \AuthItem())->add($category->name, \Constant::CATEGORY, $category->rule_id, $category->description);
        if ($id) {
            $ret->ret = \Constant::RET_OK;
            $ret->data = json_encode(['id' => $id]);
        } else {
            $ret->ret = \Constant::RET_DATA_CONFLICT;
            $ret->data = 'Category exists!';
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
        $ret = new CommonRet();

        $count = (new \AuthItem())->remove(\Constant::CATEGORY, $cate_id);
        if ($count) {
            (new \AuthItemChild())->remove($cate_id);   // delete auth_item_child

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
        $ret = new CommonRet();

        $model = new \AuthItem();
        $item = $model->getById($cate_id);
        if ($item && $item['type'] == \Constant::CATEGORY) {
            $name = $category->name ?: $item['name'];
            $description = $category->description ?: $item['description'];
            $count = $model->update($cate_id, \Constant::CATEGORY, $name, $description);
            $ret->ret = $count ? \Constant::RET_OK : \Constant::RET_DATA_CONFLICT;
        } else {
            $ret->ret = \Constant::RET_DATA_NO_FOUND;
        }

        return $ret;
    }

    /**
     * 获取所有权限分类.
     *
     * @param \Authority\Search $search
     *
     * @return \Authority\CategoryRet $ret
     */
    public static function getList(Search $search)
    {
        $ret = new CategoryRet();
        $ret->ret = \Constant::RET_OK;

        $categories = [];
        $model = new \AuthItem();

        if (!$search->page && !$search->conditions) {
            $categories = (new \AuthItem())->getByType(\Constant::CATEGORY);
            $ret->total = count($items);
        } else {
            $sql = 'SELECT * FROM auth_item WHERE type='.\Constant::CATEGORY;
            $total_sql = 'SELECT COUNT(1) FROM auth_item WHERE type='.\Constant::CATEGORY;
            if ($search->conditions) {
                $where = [];
                foreach ($search->conditions as $condition) {
                    $expr = $condition->expr ?: '=';
                    $where[] = "{$condition->field} {$expr} '{$condition->value}'";
                }
                $where = implode(' AND ', $where);
                $sql .= " AND {$where}";
                $total_sql .= " AND {$where}";
            }
            $ret->total = $model->query($total_sql)->fetch(\PDO::FETCH_COLUMN);
            $sql .= ' ORDER BY id DESC';
            if ($page = $search->page) {
                $pagesize = $search->pagesize ?: 20;
                $offset = ($page - 1) * $pagesize;
                $sql .= " LIMIT {$offset},{$pagesize}";
            }
            $categories = $model->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        }
        if ($categories) {
            $data = [];
            foreach ($categories as $cate) {
                $data[] = new Category(
                    [
                        'id' => $cate['id'],
                        'name' => $cate['name'],
                        'description' => $cate['description'],
                    ]
                );
            }
            $ret->categories = $data;
        }

        return $ret;
    }
}
