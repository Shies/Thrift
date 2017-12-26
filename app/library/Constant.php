<?php

class Constant
{
    const RET_OK = 0;                   // ok
    const RET_SYS_ERROR = 65535;        // 系统错误
    const RET_DATA_CONFLICT = 65534;    // 数据冲突
    const RET_DATA_NO_FOUND = 65533;    // 找不到数据


    const POINT = 1;                    // 权限点
    const CATEGORY = 2;                 // 权限点分类
    const GROUP = 3;                    // 权限组
    const ORG = 4;                      // 组织、管理组

    const ADMIN = 1;                    // ADMIN Group ID
}
