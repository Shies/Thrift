<?php

define('ROOT', __DIR__.'/..');
require ROOT.'/vendor/autoload.php';

class ResourceAttrTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider additionProvider
     */
    public function testAdd($name, $src_id)
    {
        $resource_attr = new \Authority\ResourceAttr(
            [
                'name' => $name,
                'src_id' => $src_id,
                'owner_id' => 1,
                'role_id' => 80,
                'mode' => '0',
            ]
        );
        $ret = (new \Authority\Handler())->addResourceAttr($resource_attr);
        $this->assertEquals(\Constant::RET_OK, $ret->ret);
        if ($ret->ret == \Constant::RET_OK) {
            echo PHP_EOL, "Add resource_attr {$name} - {$src_id} (owner_id - 1 & role_id - 80 & mode - 0) success!", PHP_EOL;
        }

        return ['name' => 'CURSED_FRUIT', 'src_id' => 1];
    }

    /**
     * @dataProvider additionProvider
     */
    public function testUpdate($name, $src_id)
    {
        $resource_attr = new \Authority\ResourceAttr(['owner_id' => 5, 'mode' => '33']);
        $ret = (new \Authority\Handler())->updateResourceAttr($name, $src_id, $resource_attr);
        $this->assertEquals(\Constant::RET_OK, $ret->ret);
        if ($ret->ret == \Constant::RET_OK) {
            echo PHP_EOL, 'Update resource_attr {$name} - {$src_id} (owner_id - 1 & role_id - 80 & mode - 0) to (owner_id - 5 & mode - 33) success!', PHP_EOL;
        }
    }

    public function testGetResourceAttrs()
    {
        $search = new \Authority\Search();
        $search->page = 0;
        $search->conditions = [
            new \Authority\Condition(['field' => 'name', 'expr' => '=', 'value' => 'CURSED_FRUIT']),
        ];
        $ret = (new \Authority\Handler())->getResourceAttrs($search);
        $this->assertGreaterThan(0, $ret->total);

        if ($ret->total > 0) {
            echo PHP_EOL, "Get {$ret->total} resource_attr success!", PHP_EOL;
        }
    }

    /**
     * @dataProvider additionProvider
     */
    public function testRemove($name, $src_id)
    {
        $ret = (new \Authority\Handler())->rmResourceAttr($name, $src_id);
        $this->assertEquals(\Constant::RET_OK, $ret->ret);

        if ($ret->ret == \Constant::RET_OK) {
            echo PHP_EOL, 'Remove resource_attr {$name} - {$src_id} success!', PHP_EOL;
        }
    }

    public function additionProvider()
    {
        return [
            ['CURSED_FRUIT', 3]
        ];
    }

}
