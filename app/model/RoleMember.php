<?php

class RoleMember extends Model
{
    const INSERT_SQL = 'INSERT INTO role_member (role_id,user_id) VALUES (?,?)';
    const DELETE_SQL = 'DELETE FROM role_member WHERE role_id=? AND user_id=?';
    const DELETE_BY_ROLE_SQL = 'DELETE FROM role_member WHERE role_id=?';

    public function add($role_id, $user_id)
    {
        $stmt = $this->getStatement(self::INSERT_SQL);
        $stmt->execute([$role_id, $user_id]);

        return $this->lastInsertId();
    }

    public function remove($role_id, $user_id)
    {
        $stmt = $this->getStatement(self::DELETE_SQL);
        $stmt->execute([$role_id, $user_id]);

        return $stmt->rowCount();
    }

    public function removeByRole($role_id)
    {
        $stmt = $this->getStatement(self::DELETE_BY_ROLE_SQL);
        $stmt->execute([$role_id]);

        return $stmt->rowCount();
    }
}
