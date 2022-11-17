<?php
/*
    Copyright (c) 2021-2050 FatPlug, All rights reserved.
    This file is part of the FatPlug Framework (http://fatplug.cn).
    This is not free software.
    you can redistribute it and/or modify it under the
    terms of the License after purchased commercial license. 
    mail: sunkangchina@163.com
*/

/**
 * 该功能有点复杂，配置table插件将可实现少量PHP代码生成table带搜索、添加、编辑、删除等操作
 * 新增方法202211
 * $vue->afterSave("_this.main_sql_1 = false;");  
 *  
 */
class Vue
{
    public $opt = [
        'is_editor' => false,
        'is_page'  => false,
        'is_reset' => false,
        'is_add'   => false,
        'is_edit'  => false,
        'is_tree'  => false,

    ];
    public $after_save = [];
    public $editor_timeout = 600;
    public $opt_method = [
        'is_page' => 'page_method',
        'is_reset' => 'reset_method',
        'is_add'  => 'add_method',
        'is_edit' => 'edit_method',
        'is_tree' => 'tree_method',
        'is_editor' => 'editor_method',
    ];

    public $opt_data = [
        'is_page' => 'page_data',
    ];

    public $page_url;
    public $add_url;
    public $edit_url;
    public $id   = "#app";
    public $load_name   = "load";
    public $data = [
        "is_show" => false,
        'where' => "js:{per_page:20}",
        'lists' => "js:[]",
        'page' => "1",
        'total' => 0,
        'form' => "js:{}",
        'node' => "js:{}",
        'row' => "js:{}",
        'loading'=>true,
    ];
    public $page_data = [
        "is_show" => false,
        'where' => "js:{per_page:20}",
        'lists' => "js:[]",
        'page' => "1",
        'total' => 0,
        'form' => "js:{}",
        'node' => "js:{}",
        'res' => "js:{}",
        'loading'=>true,
    ];
    public $watch = [];
    public $mounted = [];
    public $created_js = [];
    public $methods    = [];

    public $create_update_load = [];

    public $page_method = [
        'page_size_change(val)' => "js:this.where.page= 1;this.where.per_page = val;this.load();",
        'page_change(val)' => "js:this.where.page = val;this.load();",
    ];

    public $reset_method = [
        'reload()' => "js:this.where.page = 1;this.loading=true;this.load();",
        'reset()' => "js:this.where = {};this.loading=true;this.load();",
    ];

    public $add_method = ''; 
    public $edit_method = '';
    public $tree_field = 'pid';
    public $tree_method = [
        'select_click(data)' => "js:{ 
            this.node = data;
            this.form.pid = data.id;
            this.form._pid_name = data.label;
            this.\$refs['pid'].\$el.click();
        }"
    ];
    public $data_form;

    public function data_form($key,$val){
        $this->data_form[$key] = $val;
    }
    public  function data($key, $val)
    {
        $this->data[$key] = $val;
    }

    public  function method($name, $val)
    {
        $this->methods[$name] = $val;
    }

    public  function watch($name, $val)
    {
        $this->watch[$name] = $val;
    }

    public function afterSave($val)
    {
        $this->after_save[] = $val;
    }
    
    /**
     $vue->mounted('',"js:
         const that = this
         window.onresize = () => {
          return (() => {
            that.height = that.\$refs.tableCot.offsetHeight;
          })()
        }
    ");
     */
    public  function mounted($name, $val)
    {
        $this->mounted[$name] = $val;
    }


    public  function created($load_metheds = [])
    {
        foreach ($load_metheds as $v) {
            $this->created_js[] = $v;
        }
    }

    public  function run()
    {
        $this->init();
        $data    = php_to_js($this->data);
        $created = "";
        foreach ($this->created_js as $v) {
            $created .= "this." . $v . ";";
        }
        $methods_str = "";
        $watch_str = "";
        $mounted_str = "";
        $br = "\n\t\t\t\t\t";
        $br2 = "\n\t\t\t\t";
        if (!$this->methods["load_common()"]) {
            $this->methods["load_common()"] = "js:{}";
        }
        foreach ($this->methods as $k => $v) {
            $methods_str .= $br . $k . "{" . php_to_js($v) . "},";
        }
        foreach ($this->watch as $k => $v) {
            $watch_str .= $br . $k . php_to_js($v) . ",";
        }
        foreach ($this->mounted as $k => $v) {
            $mounted_str .= $br . $k . php_to_js($v) . "";
        }

        $js = "
            var _this;
            var app = new Vue({
                el:'" . $this->id . "',
                data:" . $data . ",
                created(){
                    _this = this;
                    " . $created . "
                },
                mounted(){
                    " . $mounted_str . "
                },
                watch: {
                    " . $watch_str . "
                },
                methods:{" . $methods_str . "$br2}
            });
        ";
        $vars = '';
        $e = self::$_editor; 
        if($e){
            foreach($e as $name){
                $vars .=" var editor".$name.";\n";
            }    
        }       
        $code = $vars . $js; 
        $name = $this->load_name;
        if($name != 'load'){
            $code = str_replace("this.load()","this.".$name."()",$code);    
        } 
        return $code;
    }


    public function init()
    {
        $opt = $this->opt;
        if ($opt['is_page']) {
            $this->created(['load()']);
        }
        $data_form_add = '';
        $data_form_update = '';
        if($this->data_form){
            $form = [];
            foreach($this->data_form as $k=>$v){ 
                $v  = php_to_js($v); 
                $data_form_add.=" 
                     this.\$set(this.form,'".$k."',$v);\n   
                ";
                $data_form_update.="
                    if(!row.$k){
                        this.\$set(this.form,'".$k."',$v);\n    
                    }                    
                ";
                $form[$k] = $v; 
            }
            $this->data['form'] = "js:".json_encode($form);
        }
        $this->add_method = $this->add_method?:[
            "show()" => "js:
                 this.is_show = true;
                 this.form = {};".$data_form_add."
                 ".$this->loadEditorAdd()."
            ",
        ];

        $this->edit_method = $this->edit_method?:[
            "update(row)" => "js:{ 
                this.is_show = true;
                this.form = row;  ".$data_form_update."
                ".$this->loadEditorUpdate()."
            }"
        ];
        
        foreach ($opt as $k => $v) {
            if ($v) {
                if ($this->opt_method[$k]) {
                    $method = $this->opt_method[$k];
                    if (method_exists($this, $method)) {
                        $this->$method();
                    }
                    if ($this->$method) {
                        $this->methods = array_merge($this->methods, $this->$method);
                    }
                }
                if ($this->opt_data[$k]) {
                    $data_name = $this->opt_data[$k];
                    $this->data = array_merge($this->$data_name, $this->data);
                }
            }
        }
        $this->crud();
    }



    public function crud()
    {
        if($this->page_url){
            $this->method('load()', "js:ajax('" . $this->page_url . "',this.where,function(res) { 
                _this.page   = res.current_page;
                _this.total  = res.total;
                _this.lists  = res.data;
                _this.res  = res;
                if(_this.loading){ 
                   _this.loading = false; 
                }
            });");
        }else{
            $this->method('load()', "js:");
        }
        $after_save = $this->after_save;
        $after_save_str = '';
        if($this->after_save){
            foreach($this->after_save as $v){
                if($v){
                    $v = trim($v); 
                    $after_save_str .= $v;    
                }                
            }
        } 
        if($this->add_url || $this->edit_url){
            $this->method("save()", "js:let url = '" . $this->add_url . "';
                if(this.form.id){
                    url = '" . $this->edit_url . "';
                } 
                ajax(url,this.form,function(res){ 
                        console.log(res);
                        _this.\$message({
                          message: res.msg,
                          type: res.type
                        }); 
                        if(res.code == 0){
                            _this.is_show    = false; 
                            _this.load();
                        }
                        ".$after_save_str."
                }); 
            ");
        }else{
            
        }
        
    }

    public function editor_method()
    { 
        $this->data("editor", "js:{}");
        
        $this->method("weditor()", "js:   
              ".$this->loadEditor()."  
        ");
    }
    /**
    * 生成编辑器HTML
    */
    public static $_editor;
    public function editor($name = 'body'){
        self::$_editor[] = $name; 
        return '<div id="'.$name.'editor—wrapper" class="editor—wrapper">
            <div id="'.$name.'weditor-tool" class="toolbar-container"></div>
            <div id="'.$name.'weditor" class="editor-container" ></div>
        </div> ';
    }
    /**
    * 添加
    */
    public function loadEditorAdd(){
        $e = self::$_editor; 
        if(!$e){
            return;
        }
        $js = '';
        foreach($e as $name){
            $js .="
                setTimeout(function(){
                    editor".$name.".setHtml('');
                },".$this->editor_timeout.");                
            ";
        }
        return $js;
    }
    /**
    * 更新
    */
    public function loadEditorUpdate(){
        $e = self::$_editor; 
        if(!$e){
            return;
        }
        $js = ''; 
        foreach($e as $name){
            $js .=" 
                let dd_editor".$name." = row.".$name."; 
                setTimeout(function(){
                    editor".$name.".setHtml(dd_editor".$name."); 
                },".$this->editor_timeout."); 
            ";
        }
        return $js;
    }

    /**
    * 加载wangeditor
    */
    public function loadEditor(){
            $e = self::$_editor; 
            if(!$e){
                return;
            }
            $js = '';
            foreach($e as $name){
                $js .= " 
                if(editor".$name."){ 
                    editor".$name.".destroy();
                }
                var editorConfig".$name." = {
                    placeholder: '',
                    MENU_CONF: {
                      uploadImage: {
                        fieldName: 'file',server: '/api/admin/upload.php?is_editor=1'
                      }
                    }, 
                    onChange(editor) {  
                      _this.form.".$name." = editor.getHtml(); 
                    }
                }; 
                editor = E.createEditor({
                    selector: '#".$name."weditor', 
                    config: editorConfig".$name.",
                    mode: 'simple',  
                }); 
                editor".$name." = editor; 
                var toolbarConfig".$name." = {}; 
                var toolbar".$name." = E.createToolbar({
                    editor,
                    selector: '#".$name."weditor-tool',
                    config: toolbarConfig".$name.",
                    mode: 'simple',  
                });   
                ";    
            }
            
            return $js;
    }
    /**
    日期区间：
    <el-date-picker @change="reload" v-model="where.date" value-format="yyyy-MM-dd" :picker-options="pickerOptions" size="medium" type="daterange" range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期">
    </el-date-picker>

    $date    = g('date'); 
    if ($date[0]) {
        $where['created_at[>]'] = date("Y-m-d 00:00:00", strtotime($date[0]));
    }
    if ($date[1]) {
        $where['created_at[<=]'] =  date("Y-m-d 23:59:59", strtotime($date[1]));
    }  

     */
    public  function addDateTimeSelect()
    {
        $this->data['pickerOptions'] = "js: {
                shortcuts: [{
                    text: '今天',
                    onClick(picker) {
                        const end = new Date();
                        const start = new Date();
                        start.setTime(start.getTime() - 3600 * 1000 * 24 * 1);
                        picker.\$emit('pick', [end, end]);
                    }
                },{
                    text: '昨天',
                    onClick(picker) {
                        const end = new Date();
                        const start = new Date();
                        start.setTime(start.getTime() - 3600 * 1000 * 24 * 1);
                        end.setTime(start.getTime() - 3600 * 1000 * 24 * 1);
                        picker.\$emit('pick', [start, start]);
                    }
                },{
                    text: '最近一周',
                    onClick(picker) {
                        const end = new Date();
                        const start = new Date();
                        start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
                        picker.\$emit('pick', [start, end]);
                    }
                }, {
                    text: '最近一个月',
                    onClick(picker) {
                        const end = new Date();
                        const start = new Date();
                        start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
                        picker.\$emit('pick', [start, end]);
                    }
                }, {
                    text: '最近三个月',
                    onClick(picker) {
                        const end = new Date();
                        const start = new Date();
                        start.setTime(start.getTime() - 3600 * 1000 * 24 * 90);
                        picker.\$emit('pick', [start, end]);
                    }
                }]
            }";
    }

    /**
    * 排序
    * misc('sortable'); 
    * $vue->sort(".sortable1 tbody","_this.form.xcx_banner");
    */
    public function sort($element,$change_obj){
        $sortable = "sortable".mt_rand(1000,9999);
        $this->mounted('',"js:this.".$sortable."();");
        $this->method($sortable."()","js: 
          Sortable.create(document.querySelector('".$element."'),{
            onEnd(eve) { 
                  let a = eve.newIndex;
                  let b = eve.oldIndex;
                  //把b换成a,a换成b
                  let a1 = ".$change_obj."[a];
                  let b1 = ".$change_obj."[b]; 
                  ".$change_obj."[a] = b1;
                  ".$change_obj."[b] = a1; 
            }
          });
        ");
    }

}


/**
* vue message
*/
function vue_message(){
    return "_this.\$message({type:res.type,message:res.msg});\n";
}
/**
* loading效果
*/
function vue_loading($name,$txt){
    return "const ".$name." = _this.\$loading({
          lock: true,
          text: '".$txt."',
          spinner: 'el-icon-loading',
          background: 'rgba(0, 0, 0, 0.7)'
    }); \n";
}