<?php

class Role extends Model
{
    const GET_ALL_SQL = 'SELECT * FROM role ORDER BY id DESC';
    const GET_BY_ID_SQL = 'SELECT * FROM role WHERE id=?';
    const INSERT_SQL = 'INSERT INTO role (type,name,rule) VALUES (?,?,?)';
    const UPDATE_SQL = 'UPDATE role SET name=?,rule=? WHERE id=?';
    const DELETE_BY_ID_SQL = 'DELETE FROM role WHERE id=?';

    public function getAll()
    {
        $roles = $this->_db->query(self::GET_ALL_SQL)->fetchAll(PDO::FETCH_ASSOC);

        return array_column($roles, null, 'id');
    }

    public function add($type, $name, $rule)
    {
        $stmt = $this->getStatement(self::INSERT_SQL);
        $stmt->execute([$type, $name, $rule]);

        return $this->lastInsertId();
    }

    public function update($id, $name, $rule = '')
    {
        $stmt = $this->getStatement(self::UPDATE_SQL);
        $stmt->execute([$name, $rule, $id]);

        return true;
    }

    public function getById($id)
    {
        $stmt = $this->getStatement(self::GET_BY_ID_SQL);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function remove($id)
    {
        $stmt = $this->getStatement(self::DELETE_BY_ID_SQL);
        $stmt->execute([$id]);

        return $stmt->rowCount();
    }
}
