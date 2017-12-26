<?php

class AuthItemChild extends Model
{
    protected $_table = 'auth_item_child';

    public function getRelations()
    {
        return $this->clean()->find_array();
    }

    public function getChildren($id)
    {
        $parents = is_array($id) ? $id : [$id];
        $children = [];
        foreach ($this->getRelations() as $relation) {
            $current = $relation['parent'];
            if (in_array($current, $parents)) {
                if (!isset($children[$current])) {
                    $children[$current] = [];
                }
                $children[$current][] = $relation['child'];
            }
        }

        return $children;
    }

    public function getParent($id)
    {
        $children = is_array($id) ? $id : [$id];
        $parents = [];
        foreach ($this->getRelations() as $relation) {
            $current = $relation['child'];  // 当前遍历 child id
            if (in_array($current, $children)) {
                if (!isset($parents[$current])) {
                    $parents[$current] = [];
                }
                $parents[$current][] = $relation['parent'];
            }
        }

        return $parents;
    }

    public function addRelation($parent, $child)
    {
        return $this->create()->set(['parent' => $parent, 'child' => $child])->save();
    }

    public function removeRelation($parent = '', $child = '')
    {
        $where = [];
        if ($parent) {
            $where['parent'] = $parent;
        }
        if ($child) {
            $where['child'] = $child;
        }
        if ($where) {
            $this->clean()->where($where)->delete_many();
        }
    }

    public function updateRelation($role_id, $item_ids)
    {
        $this->clean()->where('parent', $role_id)->delete_many();
        foreach ($item_ids as $item_id) {
            $this->create()->set(['parent' => $role_id, 'child' => $item_id])->save();
        }

        return true;
    }
}
