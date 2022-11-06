<?php

/*
    Copyright (c) 2021-2050 FatPlug, All rights reserved.
    This file is part of the FatPlug Framework (http://fatplug.cn).
    This is not free software.
    you can redistribute it and/or modify it under the
    terms of the License after purchased commercial license. 
    mail: sunkangchina@163.com
*/


namespace lib;

/**
 * 设置菜单
    add_action("admin.menu",function(&$menus){
 
    });
 */
class Menu
{

    /**
     * 取菜单
     */
    public static function get()
    { 
        if(!check_admin_login()){
            return [];
        }
        $menus = [
            'system' => [
                'icon' => 'layui-icon layui-icon layui-icon-windows',
                'label' => '系统',
                'level' => 5,
                'url' => "",
                'children' => [
                    [
                        'icon' => 'far fa-circle',
                        'label' => '员工',
                        'url' => ADMIN_DIR_NAME . '/user.php',
                        'acl' => 'system.user'
                    ],
                    [
                        'icon' => 'far fa-circle',
                        'label' => '部门',
                        'url' => ADMIN_DIR_NAME . '/group.php',
                        'acl' => 'system.group'
                    ],
                    [
                        'icon' => 'far fa-circle',
                        'label' => '插件',
                        'url' => ADMIN_DIR_NAME . '/plugin.php',
                        'acl' => 'system.plugin'
                    ],

                ]
            ],
        ];
        do_action("admin.menu", $menus); 
        if (!is_admin()) {
            $user_acl = get_user_acl() ?: [];
            foreach ($menus as $k => $v) {
                foreach ($v['children'] as $k1 => $v1) {
                    $acl_1 = $v1['acl'];
                    $flag = false;
                    if ($acl_1 && is_string($acl_1)) {
                        foreach ($user_acl as $acl_2) {
                            if (strpos($acl_2, $acl_1) !== false) {
                                $flag = true;
                                break;
                            }
                        }
                        if (!$flag) {
                            unset($menus[$k]['children'][$k1]);
                        }
                    } else {
                        unset($menus[$k]['children'][$k1]);
                    }
                }
            }
            foreach ($menus as $k => $v) {
                if (count($v['children']) == 0) {
                    unset($menus[$k]);
                }
            }
        }
        $menus = Arr::order_by($menus,'level',SORT_ASC); 
        do_action("admin.menu.end", $menus); 
        return $menus;
    }
}