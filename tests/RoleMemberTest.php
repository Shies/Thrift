<?php

define('ROOT', __DIR__.'/..');
require ROOT.'/vendor/autoload.php';

class RoleMemberTest extends PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider additionProvider
     */
    public function testAdd($role_id, $user_id)
    {
        $ret = (new \Authority\Handler())->addRoleMember($role_id, $user_id);
        $this->assertEquals(\Constant::RET_OK, $ret->ret);
        if ($ret->ret == \Constant::RET_OK) {
            echo PHP_EOL, "Add role_member (role_id - {$role_id}, user_id - {$user_id}) success!", PHP_EOL;
        }

    }

    /**
     * @dataProvider additionProvider
     */
    public function testRemove($role_id, $user_id)
    {
        $ret = (new \Authority\Handler())->rmRoleMember($role_id, $user_id);
        $this->assertEquals(\Constant::RET_OK, $ret->ret);
        if ($ret->ret == \Constant::RET_OK) {
            echo PHP_EOL, "Remove role_member (role_id - {$role_id}, user_id - {$user_id}) success!", PHP_EOL;
        }
    }

    public function additionProvider()
    {
        $data = [[], []];

        $handler = new \Authority\Handler();
        $ret = $handler->getRoles(new \Authority\Search());
        if ($ret->ret != \Constant::RET_OK) {
            return false;
        }
        $roles = $ret->roles;

        $ret = $handler->getUsers(new \Authority\Search());
        if ($ret->ret != \Constant::RET_OK) {
            return false;
        }
        $users = $ret->users;

        foreach ($data as $key => &$item) {
            if (isset($roles[$key]) && isset($users[$key])) {
                $item = [$roles[$key]->id, $users[$key]->id];
            }
        }
        return $data;
    }

}
