<?php

/**
$vue = new Vue;  
$vue->created(['start()']);
$vue->data('name','100');
$vue->method('start()',"js:alert(1);");
$js  = $vue->run();
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
    public $data = [
        "is_show" => false,
        'where' => "js:{per_page:20}",
        'lists' => "js:[]",
        'page' => "1",
        'total' => 0,
        'form' => "js:{}",
        'node' => "js:{}",
        'row' => "js:{}",
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
        'reload()' => "js:this.where.page = 1;this.load();",
        'reset()' => "js:this.where = {};this.load();",
    ];

    public $add_method = [
        "show()" => "js:{
             this.is_show = true;
             this.form = {};
             this.load_common();
        }"
    ];

    public $edit_method = [
        "update(row)" => "js:{
            this.is_show = true;
            let row_cp = JSON.parse(JSON.stringify(row));
            this.form = row_cp;
            this.load_common();
        }"
    ];
    public $tree_field = 'pid';
    public $tree_method = [
        'select_click(data)' => "js:{ 
            this.node = data;
            this.form.pid = data.id;
            this.form._pid_name = data.label;
            this.\$refs['pid'].\$el.click();
        }"
    ];

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
        $data    = array_encode_to_js($this->data);
        $created = "";
        foreach ($this->created_js as $v) {
            $created .= "this." . $v . ";";
        }
        $methods_str = "";
        $br = "\n\t\t\t\t\t";
        $br2 = "\n\t\t\t\t";
        if (!$this->methods["load_common()"]) {
            $this->methods["load_common()"] = "js:{}";
        }
        foreach ($this->methods as $k => $v) {
            $methods_str .= $br . $k . "{" . array_encode_to_js($v) . "},";
        }
        foreach ($this->watch as $k => $v) {
            $watch_str .= $br . $k . array_encode_to_js($v) . ",";
        }
        foreach ($this->mounted as $k => $v) {
            $mounted_str .= $br . $k . array_encode_to_js($v) . "";
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
        return $js;
    }


    public function init()
    {
        $opt = $this->opt;
        if ($opt['is_page']) {
            $this->created(['load()']);
        }
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
                    setTimeout(function(){
                        _this.loading = false;
                    },300);
                }
            });");
        }else{
            $this->method('load()', "js:");
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
                }); 
            ");
        }else{
            
        }
        
    }

    public function editor_method()
    {
        $this->data("editor", "js:{}");
        $this->add_method = [
            "show()" => "js:{
                 this.is_show = true;
                 this.form = {};
                 setTimeout(function () { 
                     editor.setHtml('');
                 },600);
            }",
        ];

        $this->edit_method = [
            "update(row)" => "js:{ 
                this.is_show = true;
                this.form = row; 
                setTimeout(function () { 
                      editor.setHtml(row.body); 
                },600);
            }"
        ];
        $this->method("weditor()", "js: 
            if(JSON.stringify(this.editor) == '{}'){ 
                this.editor = {load:1};
                const editorConfig = {
                    placeholder: '请输入...',
                    MENU_CONF: {
                      uploadImage: {
                        fieldName: 'file',server: '/api/admin/upload.php?is_editor=1'
                      }
                    }, 
                    onChange(editor) {
                      const html = editor.getHtml()
                      console.log('editor content', html)
                      // 也可以同步到 <textarea>
                    }
                }; 
                editor = E.createEditor({
                    selector: '#editor-container', 
                    config: editorConfig,
                    mode: 'simple', // or 'simple'
                }); 
                const toolbarConfig = {}; 
                const toolbar = E.createToolbar({
                    editor,
                    selector: '#toolbar-container',
                    config: toolbarConfig,
                    mode: 'simple', // or 'simple'
                }); 
          }");
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
}
