<?php

define('ROOT', __DIR__.'/..');
require ROOT.'/vendor/autoload.php';

class UserTest extends PHPUnit_Framework_TestCase
{

    public function testAdd()
    {
        $user = new \Authority\User(['username' => 'Chopper']);
        $ret = (new \Authority\Handler())->addUser($user);
        $this->assertEquals(\Constant::RET_OK, $ret->ret);
        if ($ret->ret == \Constant::RET_OK) {
            echo PHP_EOL, 'Add user Chopper success!', PHP_EOL;
        }

        $data = json_decode($ret->data, true);
        return (int)$data['id'];
    }

    /**
     * @depends testAdd
     */
    public function testGetByName($id)
    {
        $ret = (new \Authority\Handler())->getUserByName('Chopper');
        $this->assertEquals($id, $ret->id);

        if ($ret->id == $id) {
            echo PHP_EOL, 'Get user Chopper by username success!', PHP_EOL;
        }
    }

    /**
     * @depends testAdd
     */
    public function testGetById($id)
    {
        $ret = (new \Authority\Handler())->getUserById($id, ['user']);
        $this->assertEquals('Chopper', $ret->user->username);

        if ($ret->user->username == 'Chopper') {
            echo PHP_EOL, 'Get user Chopper by id success!', PHP_EOL;
        }
    }

    public function testGetUsers()
    {
        $search = new \Authority\Search();
        $search->page = 0;
        $search->conditions = [
            new \Authority\Condition(['field' => 'username', 'expr' => '=', 'value' => 'Chopper']),
        ];
        $ret = (new \Authority\Handler())->getUsers($search);
        $this->assertGreaterThan(0, $ret->total);

        if ($ret->total > 0) {
            echo PHP_EOL, "Get {$ret->total} users success!", PHP_EOL;
        }
    }

    /**
     * @depends testAdd
     */
    public function testUpdate($id)
    {
        $ret = (new \Authority\Handler())->updateUser($id, new \Authority\User(['nickname' => '乔巴']));
        $this->assertEquals(\Constant::RET_OK, $ret->ret);

        if ($ret->ret == \Constant::RET_OK) {
            echo PHP_EOL, 'Update Chopper nickname 乔巴 success!', PHP_EOL;
        }
    }

    public function testAssignGroup2User()
    {
        $handler = new \Authority\Handler();
        $ret = $handler->getUserByName('Chopper');
        if ($ret->id) {
            $ret = $handler->assignGroup2User([\Constant::ADMIN], $ret->id);
            $code = $ret->ret;
        } else {
            $code = \Constant::RET_DATA_NO_FOUND;
        }
        $this->assertEquals(\Constant::RET_OK, $code);
        
        if ($code == \Constant::RET_OK) {
            echo PHP_EOL, 'Assign Chopper to ADMIN success!', PHP_EOL;
        }
        return $ret->id;
    }

    /**
     * @depends testAssignGroup2User
     */
    public function testGetAssignableGroup($id)
    {
        $ret = (new \Authority\Handler())->getAssignableGroup($id);
        $count = count($ret->groups);
        $this->assertGreaterThan(0, $count);

        if ($count > 0) {
            echo PHP_EOL, 'Chopper has at least one assignable group!', PHP_EOL;
        }
    }

    /**
     * @depends testAdd
     */
    public function testRemove($id)
    {
        $ret = (new \Authority\Handler())->rmUser($id);
        $this->assertEquals(\Constant::RET_OK, $ret->ret);

        if ($ret->ret == \Constant::RET_OK) {
            echo PHP_EOL, 'Remove user Chopper success!', PHP_EOL;
        }
    }

}

