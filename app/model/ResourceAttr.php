<?php

class ResourceAttr extends Model
{
    const GET_ALL_SQL = 'SELECT * FROM resource_attr';
    const GET_BY_ID_SQL = 'SELECT * FROM resource_attr WHERE name=? AND src_id=?';
    const INSERT_SQL = 'INSERT INTO resource_attr (name,src_id,owner_id,role_id,mode,rule) VALUES (?,?,?,?,?,?)';
    const UPDATE_SQL = 'UPDATE resource_attr SET owner_id=?,role_id=?,mode=?,rule=? WHERE name=? AND src_id=?';
    const DELETE_BY_ID_SQL = 'DELETE FROM resource_attr WHERE name=? AND src_id=?';

    public function getById($name, $src_id)
    {
        $stmt = $this->getStatement(self::GET_BY_ID_SQL);
        $stmt->execute([$name, $src_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function add($name, $src_id, $owner_id, $role_id, $mode, $rule = '')
    {
        $stmt = $this->getStatement(self::INSERT_SQL);
        $stmt->execute([$name, $src_id, $owner_id, $role_id, $mode, $rule]);
        $count = $stmt->rowCount();

        return $count ? $this->lastInsertId() : $count;
    }

    public function remove($name, $src_id)
    {
        $stmt = $this->getStatement(self::DELETE_BY_ID_SQL);
        $stmt->execute([$name, $src_id]);

        return $stmt->rowCount();
    }

    public function update($name, $src_id, $owner_id, $role_id, $mode, $rule = '')
    {
        $stmt = $this->getStatement(self::UPDATE_SQL);
        $stmt->execute([$owner_id, $role_id, $mode, $rule, $name, $src_id]);

        return $stmt->rowCount();
    }
}
