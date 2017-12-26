<?php

define('ROOT', __DIR__.'/..');
require ROOT.'/vendor/autoload.php';

class GroupTest extends PHPUnit_Framework_TestCase
{

    public function testAdd()
    {
        $org = new \Authority\Group(['name' => '海军本部', 'type' => \Constant::ORG, 'description' => '屌']);
        $group = new \Authority\Group(['name' => '本部大将', 'type' => \Constant::GROUP, 'description' => '3大Boss']);
        
        $ret = (new \Authority\Handler())->addGroup($org, 0);
        $this->assertEquals(\Constant::RET_OK, $ret->ret);
        $data = json_decode($ret->data, true);
        $org_id = (int)$data['id'];
        if ($ret->ret == \Constant::RET_OK) {
            echo PHP_EOL, "Add org 海军本部 {$org_id} success!", PHP_EOL;
        }


        $ret = (new \Authority\Handler())->addGroup($group, $org_id);
        $this->assertEquals(\Constant::RET_OK, $ret->ret);
        $data = json_decode($ret->data, true);
        $group_id = (int)$data['id'];
        if ($ret->ret == \Constant::RET_OK) {
            echo PHP_EOL, "Add group 本部大将 {$group_id} success!", PHP_EOL;
        }

        return ['org' => $org_id, 'group' => $group_id];
    }

    /**
     * @depends testAdd
     */
    public function testUpdate($groups)
    {
        $ret = (new \Authority\Handler())->updateGroup($groups['org'], new \Authority\Group(['description' => '66666']));
        $this->assertEquals(\Constant::RET_OK, $ret->ret);

        if ($ret->ret == \Constant::RET_OK) {
            echo PHP_EOL, 'Update org 海军本部 description 屌 to 66666!', PHP_EOL;
        }
    }

    /**
     * @depends testAdd
     */
    public function testAssignPoint($groups)
    {
        $handler = new \Authority\Handler();

        // get some points
        $points = (new \AuthItem())->getByType(\Constant::POINT);
        $assign_1 = array_slice(array_keys($points), 0, 5); // [0, 1, 2, 3, 4]
        $assign_2 = array_slice($assign_1, 2); // [2, 3, 4]
        $assign_3 = array_slice($assign_1, 0, 4); // [0, 1, 2, 3]

        // assign org
        $ret = $handler->assignPoint2Group($assign_1, $groups['org']);
        $this->assertEquals(\Constant::RET_OK, $ret->ret);
        if ($ret->ret == \Constant::RET_OK) {
            echo PHP_EOL, 'Assign points to org 海军本部!', PHP_EOL;
        }

        // assign group
        $ret = $handler->assignPoint2Group($assign_2, $groups['group']);
        $this->assertEquals(\Constant::RET_OK, $ret->ret);
        if ($ret->ret == \Constant::RET_OK) {
            echo PHP_EOL, 'Assign points to group 本部大将', PHP_EOL;
        }

        // update org
        $ret = $handler->assignPoint2Group($assign_3, $groups['org']);
        $this->assertEquals(\Constant::RET_OK, $ret->ret);
        if ($ret->ret == \Constant::RET_OK) {
            echo PHP_EOL, 'Update points to org 海军本部', PHP_EOL;
        }
    }

    /**
     * @depends testAdd
     */
    public function testGetAssignablePoint($groups)
    {
        $ret = (new \Authority\Handler())->getAssignablePoint($groups['org']);
        $count = count($ret->points);
        $this->assertGreaterThan(0, $count);

        if ($count) {
            echo PHP_EOL;
            foreach ($ret->points as $cate) {
                echo $cate->name, ': ', implode(',', array_column($cate->children, 'data')), PHP_EOL;
            }
        }
    }

    /**
     * @depends testAdd
     */
    public function testGetById($groups)
    {
        $ret = (new \Authority\Handler())->getGroupById($groups['group'], ['group', 'parent', 'users', 'points']);
        $this->assertEquals(\Constant::RET_OK, $ret->ret);

        if ($ret->ret == \Constant::RET_OK) {
            echo PHP_EOL, '名称-', $ret->group->name, ', 父级-', $ret->parent->name, ', 用户-', implode(',', $ret->users), ', 权限点-', implode(',', $ret->points), PHP_EOL;
        }
    }

    /**
     * @depends testAdd
     */
    public function testRemove($groups)
    {
        $ret = (new \Authority\Handler())->rmGroup($groups['org']);
        $this->assertEquals(\Constant::RET_OK, $ret->ret);

        if ($ret->ret == \Constant::RET_OK) {
            echo PHP_EOL, 'Remove org 海军本部 success!', PHP_EOL;
        }

        $ret = (new \Authority\Handler())->rmGroup($groups['group']);
        $this->assertEquals(\Constant::RET_OK, $ret->ret);

        if ($ret->ret == \Constant::RET_OK) {
            echo PHP_EOL, 'Remove group 本部大将 success!', PHP_EOL;
        }
    }
}
