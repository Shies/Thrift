<?php

define('ROOT', __DIR__.'/..');
require ROOT.'/vendor/autoload.php';

class PointTest extends PHPUnit_Framework_TestCase
{
    public function testAdd()
    {
        $point = new \Authority\Point(['name' => '手术刀', 'data' => 'SCALPEL', 'description' => '手术果实技能']);
        $ret = (new \Authority\Handler())->addPoint($point);
        $this->assertEquals(\Constant::RET_OK, $ret->ret);
        if ($ret->ret == \Constant::RET_OK) {
            echo PHP_EOL, 'Add point SCALPEL (手术刀) success!', PHP_EOL;
        }

        $data = json_decode($ret->data, true);
        return (int)$data['id'];
    }

    /**
     * @depends testAdd
     */
    public function testUpdate($id)
    {
        $ret = (new \Authority\Handler())->updatePoint($id, new \Authority\Point(['name' => '屠宰场', 'data' => 'SLAUGHTER_HOUSE', 'description' => '手术果实技能']));
        $this->assertEquals(\Constant::RET_OK, $ret->ret);

        if ($ret->ret == \Constant::RET_OK) {
            echo PHP_EOL, 'Update point SCALPEL (手术刀) to SLAUGHTER_HOUSE (屠宰场) success!', PHP_EOL;
        }
    }

    /**
     * @depends testAdd
     */
    public function testRemove($id)
    {
        $ret = (new \Authority\Handler())->rmPoint($id);
        $this->assertEquals(\Constant::RET_OK, $ret->ret);

        if ($ret->ret == \Constant::RET_OK) {
            echo PHP_EOL, "Remove point {$id} success!", PHP_EOL;
        }
    }

}
