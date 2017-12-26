<?php

namespace Authority;

class RuleHandler
{
    /**
     * 新增规则.
     *
     * @param \Authority\Rule $rule
     *
     * @return \Authority\CommonRet $ret
     */
    public static function add(Rule $rule)
    {
        $ret = new CommonRet();

        $id = (new \AuthRule())->add($rule->name, $rule->data ?: '');
        $ret->ret = \Constant::RET_OK;
        $ret->data = json_encode(['id' => $id]);

        return $ret;
    }

    /**
     * 删除规则.
     *
     * @param int $rule_id
     *
     * @return \Authority\CommonRet $ret
     */
    public static function remove($rule_id)
    {
        $ret = new CommonRet();

        $count = (new \AuthRule())->remove($rule_id);
        if ($count) {
            /*** make some clean work ***/
            $auth_item = new \AuthItem();
            $ids = $auth_item->getIdsByRule($rule_id);
            $auth_item->removeByRuleId($rule_id);                   // delete auth_item
            (new \AuthItemChild())->removeMulti($ids, $ids, 'OR');  // delete auth_item_child
            (new \AuthAssignment())->removeByItemIds($ids);         // delete auth_assignment

            $ret->ret = \Constant::RET_OK;
        } else {
            $ret->ret = \Constant::RET_DATA_NO_FOUND;
        }

        return $ret;
    }
}
