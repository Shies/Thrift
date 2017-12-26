<?php

define('ROOT', __DIR__.'/..');
require ROOT.'/vendor/autoload.php';

class RoleTest extends PHPUnit_Framework_TestCase
{

    public function testAdd()
    {
        $role = new \Authority\Role(['type' => 1, 'name' => '七武海']);
        $ret = (new \Authority\Handler())->addRole($role);
        $this->assertEquals(\Constant::RET_OK, $ret->ret);
        if ($ret->ret == \Constant::RET_OK) {
            echo PHP_EOL, 'Add role 四皇 success!', PHP_EOL;
        }

        $data = json_decode($ret->data, true);
        return (int)$data['id'];
    }

    /**
     * @depends testAdd
     */
    public function testUpdate($id)
    {
        $role = new \Authority\Role(['name' => '四皇']);
        $ret = (new \Authority\Handler())->updateRole($id, $role);
        $this->assertEquals(\Constant::RET_OK, $ret->ret);
        if ($ret->ret == \Constant::RET_OK) {
            echo PHP_EOL, 'Update role 七武海 to 四皇 success!', PHP_EOL;
        }
    }

    public function testGetRoles()
    {
        $search = new \Authority\Search();
        $search->page = 0;
        $search->conditions = [
            new \Authority\Condition(['field' => 'type', 'expr' => '=', 'value' => '1']),
        ];
        $ret = (new \Authority\Handler())->getRoles($search);
        $this->assertGreaterThan(0, $ret->total);

        if ($ret->total > 0) {
            echo PHP_EOL, "Get {$ret->total} roles success!", PHP_EOL;
        }
    }

    /**
     * @depends testAdd
     */
    public function testRemove($id)
    {
        $ret = (new \Authority\Handler())->rmRole($id);
        $this->assertEquals(\Constant::RET_OK, $ret->ret);

        if ($ret->ret == \Constant::RET_OK) {
            echo PHP_EOL, 'Remove role 四皇 success!', PHP_EOL;
        }
    }

}
