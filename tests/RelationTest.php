<?php

define('ROOT', __DIR__.'/..');
require ROOT.'/vendor/autoload.php';

class RelationTest extends PHPUnit_Framework_TestCase
{
    public function testAdd()
    {
        $handler = new \Authority\Handler();

        // add a category
        $category = new \Authority\Category(['name' => '霸气', 'description' => '一种技能']);
        $ret = $handler->addCategory($category);
        if ($ret->ret == \Constant::RET_OK) {
            echo PHP_EOL, 'Add category (霸气) success!', PHP_EOL;
        }
        $data = json_decode($ret->data, true);
        $cate_id = (int)$data['id'];

        // add a point
        $point = new \Authority\Point(['name' => '武装色霸气', 'data' => 'ARMED_ARBITRARINESS', 'description' => '霸气的一种']);
        $ret = $handler->addPoint($point);
        if ($ret->ret == \Constant::RET_OK) {
            echo PHP_EOL, 'Add point ARMED_ARBITRARINESS (武装色霸气) success!', PHP_EOL;
        }
        $data = json_decode($ret->data, true);
        $point_id = (int)$data['id'];

        // add relation
        $ret = $handler->addRelation($cate_id, $point_id);
        $this->assertEquals(\Constant::RET_OK, $ret->ret);
        if ($ret->ret == \Constant::RET_OK) {
            echo PHP_EOL, "Add relation (parent - {$cate_id}, child - {$point_id}) success!", PHP_EOL;
        }
        return [$cate_id, $point_id];
    }

    /**
     * @depends testAdd
     */
    public function testRemove($relation)
    {
        $handler = new \Authority\Handler();
        $ret = $handler->rmRelation($relation[0], $relation[1]);
        $this->assertEquals(\Constant::RET_OK, $ret->ret);
        if ($ret->ret == \Constant::RET_OK) {
            echo PHP_EOL, "Remove relation (parent - {$relation[0]}, child - {$relation[1]}) success!", PHP_EOL;
        }
        $handler->rmCategory($relation[0]);
        $handler->rmPoint($relation[1]);
    }

}
