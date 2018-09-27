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

        try {
            $now = date('Y-m-d H:i:s');
            $model = new \AuthRule();
            $model->create()->set(
                [
                    'name' => $rule->name,
                    'data' => $rule->data ? : null,
                    'ctime' => $now,
                    'mtime' => $now,
                ]
            )->save();
            $ret->ret = \Constant::RET_OK;
            $ret->data = json_encode(['id' => $model->id()]);
        } catch (\Exception $e) {
            $ret->ret = \Constant::RET_DATA_CONFLICT;
        }

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

        $rule = (new \AuthRule())->find_one($rule_id);

        if ($rule) {
            $ret->ret = $rule->delete() ? \Constant::RET_OK : \Constant::RET_SYS_ERROR;
        } else {
            $ret->ret = \Constant::RET_DATA_NO_FOUND;
        }

        return $ret;
    }
}