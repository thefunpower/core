<?php 
/*
    Copyright (c) 2021-2031, All rights reserved.
    This is NOT a freeware
    LICENSE: https://github.com/thefunpower/core/blob/main/LICENSE.md 
    Connect Email: sunkangchina@163.com 
*/
/**
 * 取本地插件
 */
function local_plugin()
{
    $list1 = _local_plugin('plugins');
    $list2 = _local_plugin('modules');
    return array_merge($list1?:[],$list2?:[]);
}
/**
* 获取插件列表
*/
function _local_plugin($dir_name = 'plugins')
{
    $dir = PATH . '/'.$dir_name.'/';
    $all = glob($dir . '*/info.php');
    $list = [];
    foreach ($all as $v) {
        $info  = include $v;
        $name  = str_replace($dir, '', $v);
        $name  = substr($name, 0, strpos($name, '/'));
        $base_dir = substr($v, 0, strrpos($v, '/'));
        $info['name'] = $name;
        $list[$name] = $info;
    }
    return $list;
}
/**
* 执行插件目录下SQL文件
*/
function run_plugin_sql($plugins_dir='plugins',$plugin_name = ''){
    //执行依赖包的SQL
    $dir = PATH.$plugins_dir.'/'.$plugin_name.'/sql/*.sql';
    $fs = glob($dir);
    if($fs){
        foreach($fs as $sql){
            $data = file_get_contents($sql);
            if($data){
                db_query($data);    
            }                    
        }
    } 
    //加载升级包
    $dir = PATH.$plugins_dir.'/'.$plugin_name.'/upgrade/*.sql';
    $fs = glob($dir);
    if($fs){
        foreach($fs as $sql){
            $data = file_get_contents($sql);
            if($data){
                db_query($data);    
            }                    
        }
    }
}
/**
* 安装插件
*/
function install_plugin($name){
    if(!is_array($name)){
        $arr = [$name];
    }else{
        $arr = $name;
    }
    $plug = has_actived_plugin(); 
    foreach($arr as $plugin_name){
        if(!$plug[$plugin_name]){
            do_install_plugin($plugin_name);
        }
    }
}
/**
* 安装或卸载插件
*/
function install_plugin_auto($plugin_name){
    return do_install_plugin($plugin_name,$support_remove=true,$has_status = null);
}
/**
* 卸载插件
*/
function remove_plugin($name){
    if(!is_array($name)){
        $arr = [$name];
    }else{
        $arr = $name;
    }
    $plug = has_actived_plugin(); 

    foreach($arr as $plugin_name){
        if($plug[$plugin_name]){ 
            do_remove_plugin($plugin_name);
        }
    }
}
/**
* 加载插件到数据库
*/
function load_plugin_to_db(){
    $all = local_plugin();  
    $not_in = [];
    $version = []; 
    foreach($all as $k=>$v){  
       $one = db_get_one("plugin","*",['name'=>$k]); 
       if(!$one){ 
          db_insert("plugin",$v);
       }else{
          if($v['level'] != $one['level']
            || $v['title'] != $one['title']
            ){
            db_update("plugin",$v,['id'=>$one['id']]);
          }
       }
       $version[$k] = $v['version'];
    }   
    $type = g("type")?:"lists"; 
    $where = [
        'ORDER'=>[
            'name'=>'ASC'
        ]
    ]; 
    $data = db_get("plugin","*",$where); 
    foreach($data as &$v){
        $file_version = $version[$v['name']];
        $v['can_upgrade'] = false;
        if($v['version'] != $file_version){
            $v['new_version'] = $file_version;
            $v['can_upgrade'] = true;
        }
        if(!$file_version){
            $v['can_upgrade'] = false;
            db_del("plugin",['name'=>$v['name']]);
        }
    }
    return $data;
}
/**
* 卸载插件
*/
function do_remove_plugin($plugin_name){
    return do_install_plugin($plugin_name,$support_remove=false,$has_status = -1);
}

/**
* 执行安装具体的插件
*/
function do_install_plugin($plugin_name,$support_remove=false,$has_status = null){
   //安装插件
   $one = db_get_one("plugin","*",['name'=>$plugin_name]); 
   if(!$one){
        load_plugin_to_db();
        $one = db_get_one("plugin","*",['name'=>$plugin_name]); 
   }
   $status = $one['status'];
   $d   = $one['data'];
   if($d['dependents']){
        $dependents = $d['dependents'];
        foreach($dependents as $need_active_plugin_name){ 
            db_update("plugin",['status'=>1],['name'=>$need_active_plugin_name]);
            run_plugin_sql($plugins_dir='plugins',$need_active_plugin_name);
            run_plugin_sql($plugins_dir='modules',$need_active_plugin_name);
        }
   }  

   if($has_status){
        $status = $has_status; 
        if($status == 1){
            $status = 1;
            $msg = "安装插件".$one['name']."成功！";
            //安装SQL
            run_plugin_sql($plugins_dir='plugins',$one['name']);
            run_plugin_sql($plugins_dir='modules',$one['name']); 
            $flag = 1; 
       }else{
            $status = 0; 
            $msg = "卸载插件".$one['name']."成功！";  
            $flag = -1;
       }  
       db_update("plugin",['status'=>$status],['id'=>$one['id']]);
       return $flag;
   }
   //已安装的变为卸载，已卸载的变成安装
   if($support_remove){
       if($status == 1){
            $status = 0; 
            $msg = "卸载插件".$one['name']."成功！";  
            $flag = -1;
       }else{
            $status = 1;
            $msg = "安装插件".$one['name']."成功！";
            //安装SQL
            run_plugin_sql($plugins_dir='plugins',$one['name']);
            run_plugin_sql($plugins_dir='modules',$one['name']); 
            $flag = 1;
       } 
   }else{
        $status = 1;
        $msg = "安装插件".$one['name']."成功！";
        //安装SQL
        run_plugin_sql($plugins_dir='plugins',$one['name']);
        run_plugin_sql($plugins_dir='modules',$one['name']); 
        $flag = 1;
   } 
   db_update("plugin",['status'=>$status],['id'=>$one['id']]);
   return $flag;
}
/**
 * 已安装插件
 */
function has_actived_plugin()
{
    global $config;
    static $_has_actived_plugin;
    if($_has_actived_plugin){
        return $_has_actived_plugin;
    }
    if (!file_exists(PATH . '/config.ini.php')) {
        return;
    }
    if (!$config['db_host']) {
        return;
    }
    /**
    * //无插件
    * $config['no_plugin']  = true;
    */
    if($config['no_plugin']){
        return;
    }
    $all  = db_get("plugin", "*", ['status' => 1, "ORDER" => ['level' => "DESC"]]);
    $list = [];
    foreach ($all as $v) {
        $list[$v['name']] = $v;
    }
    $_has_actived_plugin = $list;
    return $_has_actived_plugin;
}