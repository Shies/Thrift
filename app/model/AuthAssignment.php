<?php

class AuthAssignment extends Model
{
    protected $_table = 'auth_assignment';

    // fetch data
    public function getAssignments()
    {
        return $this->clean()->order_by_desc('user_id')->find_array();
    }

    public function addAssignments($user_id, $item_ids)
    {
        foreach ($item_ids as $item_id) {
            $assignment = $this->create();
            $assignment->set(['user_id' => $user_id, 'item_id' => $item_id, 'ctime' => date('Y-m-d H:i:s')]);
            $assignment->save();
        }
    }

    public function updateAssignments($user_id, $item_ids, $remove_ids)
    {
        if ($remove_ids) {
            $this->clean()->where('user_id', $user_id)->where_in('item_id', $remove_ids)->delete_many();
        }
        $this->addAssignments($user_id, $item_ids);

        return true;
    }

    public function getAssignmentByUserId($user_id)
    {
        $assignments = [];
        $result = $this->getAssignments();
        if ($result) {
            foreach ($result as $assignment) {
                if ($assignment['user_id'] == $user_id) {
                    $assignments[] = $assignment['item_id'];
                }
            }
        }

        return $assignments;
    }

    public function getUserIdByAssignment($item_id)
    {
        $user_ids = [];
        $result = $this->getAssignments();
        if ($result) {
            foreach ($result as $assignment) {
                if ($assignment['item_id'] == $item_id) {
                    $user_ids[] = $assignment['user_id'];
                }
            }
        }

        return $user_ids;
    }
}
