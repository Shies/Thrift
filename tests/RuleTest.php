<?php

define('ROOT', __DIR__.'/..');
require ROOT.'/vendor/autoload.php';

class RuleTest extends PHPUnit_Framework_TestCase
{

    public function testAdd()
    {
        $user = new \Authority\Rule(['name' => 'READ_ONLY']);
        $ret = (new \Authority\Handler())->addRule($user);
        $this->assertEquals(\Constant::RET_OK, $ret->ret);
        if ($ret->ret == \Constant::RET_OK) {
            echo PHP_EOL, 'Add rule READ_ONLY success!', PHP_EOL;
        }

        $data = json_decode($ret->data, true);
        return (int)$data['id'];
    }

    /**
     * @depends testAdd
     */
    public function testRemove($id)
    {
        $ret = (new \Authority\Handler())->rmRule($id);
        $this->assertEquals(\Constant::RET_OK, $ret->ret);

        if ($ret->ret == \Constant::RET_OK) {
            echo PHP_EOL, 'Remove rule READ_ONLY success!', PHP_EOL;
        }
    }

}
