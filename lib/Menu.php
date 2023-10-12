<?php

/*
    Copyright (c) 2021-2031, All rights reserved.  
    Connect Email: sunkangchina@163.com 
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
                'level' => 0,
                'url' => "",
                'children' => [
                    [
                        'icon' => 'far fa-circle',
                        'label' => '员工',
                        'url' => ADMIN_DIR_NAME . '/user.php',
                        'acl' => 'system.user',
                        'level'=>10,
                    ],
                    [
                        'icon' => 'far fa-circle',
                        'label' => '部门',
                        'url' => ADMIN_DIR_NAME . '/group.php',
                        'acl' => 'system.group',
                        'level'=>20,
                    ],
                    [
                        'icon' => 'far fa-circle',
                        'label' => '插件',
                        'url' => ADMIN_DIR_NAME . '/plugin.php',
                        'acl' => 'system.plugin',
                        'level'=>1000,
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
                if (!$v['children']) {
                    unset($menus[$k]);
                }
            }
        }
        $menus = Arr::order_by($menus,'level',SORT_DESC); 
        foreach($menus as $kk=>$vv){
            if($vv['children']){
                $menus[$kk]['children'] = Arr::order_by($vv['children'],'level',SORT_ASC);
            }
        } 
        do_action("admin.menu.end", $menus); 
        return $menus;
    }
}