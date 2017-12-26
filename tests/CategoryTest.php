<?php

define('ROOT', __DIR__.'/..');
require ROOT.'/vendor/autoload.php';

class CategoryTest extends PHPUnit_Framework_TestCase
{

    public function testAdd() 
    {
        $category = new \Authority\Category(['name' => 'NATURAL_CURSED_FRUIT', 'description' => '自然系恶魔果实']);
        $ret = (new \Authority\Handler())->addCategory($category);
        $this->assertEquals(\Constant::RET_OK, $ret->ret);
        if ($ret->ret == \Constant::RET_OK) {
            echo PHP_EOL, 'Add category NATURAL_CURSED_FRUIT (自然系恶魔果实) success!', PHP_EOL;
        }

        $data = json_decode($ret->data, true);
        return (int)$data['id'];
    }

    /**
     * @depends testAdd
     */
    public function testUpdate($id)
    {
        $ret = (new \Authority\Handler())->updateCategory($id, new \Authority\Category(['name' => 'ANIMAL_CURSED_FRUIT', 'description' => '动物系恶魔果实']));
        $this->assertEquals(\Constant::RET_OK, $ret->ret);

        if ($ret->ret == \Constant::RET_OK) {
            echo PHP_EOL, 'Update NATURAL_CURSED_FRUIT (自然系恶魔果实) to ANIMAL_CURSED_FRUIT (动物系恶魔果实) success!', PHP_EOL;
        }
    }

    public function testGetCategories()
    {
        $search = new \Authority\Search();
        $search->page = 0;
        $search->conditions = [
            new \Authority\Condition(['field' => 'name', 'expr' => '=', 'value' => 'ANIMAL_CURSED_FRUIT']),
        ];
        $ret = (new \Authority\Handler())->getCategories($search);
        $this->assertGreaterThan(0, $ret->total);

        if ($ret->total > 0) {
            echo PHP_EOL, "Get {$ret->total} categories success!", PHP_EOL;
        }
    }

    /**
     * @depends testAdd
     */
    public function testRemove($id) 
    {
        $ret = (new \Authority\Handler())->rmCategory($id);
        $this->assertEquals(\Constant::RET_OK, $ret->ret);

        if ($ret->ret == \Constant::RET_OK) {
            echo PHP_EOL, "Remove category {$id} success!", PHP_EOL;
        }
    }

}
