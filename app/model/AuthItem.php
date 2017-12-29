<?php

class AuthItem extends Model
{
    protected $_table = 'auth_item';

    public function getItems()
    {
        $result = $this->clean()->order_by_desc('mtime')->find_many();
        $items = [];
        foreach ($result as $item) {
            $items[$item->id] = $item;
        }

        return $items;
    }

    public function getItemByType($type, $is_array = true)
    {
        $result = [];
        foreach ($this->getItems() as $id => $item) {
            if ($item->type == $type) {
                $result[$id] = $is_array ? $item->as_array() : $item;
            }
        }

        return $result;
    }

    public function addItem($name, $type, $description = '', $data = '')
    {
        $now = date('Y-m-d H:i:s');
        $info = ['name' => $name, 'type' => $type, 'ctime' => $now, 'mtime' => $now];
        if ($description) {
            $info['description'] = $description;
        }
        if ($data) {
            $info['data'] = $data;
        }
        $item = $this->create();
        $item->set($info);

        return $item->save() ? $item->id() : false;
    }

    public function updateItem($item_id, $name, $type, $description)
    {
        $items = $this->getItems();
        if (!isset($items[$item_id])) {
            return false;
        }
        $item = $items[$item_id];

        return $item->set(['name' => $name, 'type' => $type, 'description' => $description, 'mtime' => date('Y-m-d H:i:s')])->save();
    }

    public function getItemById($id, $is_array = true)
    {
        $items = $this->getItems();
        if (!is_array($id)) {
            if (!isset($items[$id])) {
                return [];
            }

            return $is_array ? $items[$id]->as_array() : $items[$id];
        } else {
            $result = [];
            foreach ($id as $i) {
                if (isset($items[$i])) {
                    $result[$i] = $is_array ? $items[$i]->as_array() : $items[$i];
                }
            }

            return $result;
        }
    }

    public function getItemByName($name, $is_array = true)
    {
        foreach ($this->getItems() as $item) {
            if ($item['name'] == $name) {
                return $is_array ? $item->as_array() : $item;
            }
        }

        return;
    }

    public function deleteItem($item_id)
    {
        $items = $this->getItems();
        if (!isset($items[$item_id])) {
            return false;
        }
        $item = $items[$item_id];

        return $item->delete();
    }
}
