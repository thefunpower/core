<?php

class Jquery
{
}

/**
生成echatsjs图表
https://echarts.apache.org/examples/zh/editor.html?c=line-stack

<div id="main" style="width:100%;height: 400px;"></div>
echo (new Jquery_Echats)->set([
    'id'=>'main',
    'url'=>'/plugins/invoice/api/statistics.php',
    'call'=>" 
        let month = res.month; 
    ",
    'option'=>[
        'title'=>[
            'text'=>'最近12个月开票金额及数量统计',
        ],
        'xAxis'=>[
            'data'=>'js:month[0]',
            'name'=>'月份'
        ],
        'yAxis'=>[ 
            'name'=>'金额及数量'
        ],
    ],
    'series'=>"[
        {
          data: month[1],
          type: 'line', 
          name: '数量',
        },
        {
          data: month[2],
          type: 'line', 
          name: '金额',
        }
    ]"
])->run();
 */
class Jquery_Echats
{
	public $url = "";
	public $chartDom = 'chartDom';
	public $myChart  = 'myChart';
	public $option = [];
	public $id     = "";
	public $series = "";
	public $call   = "";

	public function set($config = [])
	{
		$this->chartDom = "chartDom" . mt_rand(1, 9999);
		$this->myChart  = "myChart" . mt_rand(1, 9999);
		$this->url      = $config['url'];
		$defaults = [
			'title' => [
				'text' => '',
			],
			'tooltip' => [
				'trigger' => 'axis'
			],
			'xAxis' => [
				'type' => 'category',
				'data' => "",
				'name' => "",
			],
			'yAxis' => [
				'type' => 'value',
				'name' => "",
			],
			//'series'=>[]
		];
		$option = $config['option'];
		foreach ($defaults as $k => $v) {
			foreach ($v as $k1 => $v1) {
				if ($option[$k][$k1]) {
					$defaults[$k][$k1] = $option[$k][$k1];
				}
			}
		}
		$this->id     = $config['id'];
		$this->option = $defaults;
		$this->series = $config['series'];
		$this->call   = $config['call'];
		return $this;
	}


	public function run()
	{
		return
			"var " . $this->chartDom . " = document.getElementById('" . $this->id . "');
        var " . $this->myChart . " = echarts.init(" . $this->chartDom . "); 
        ajax('" . $this->url . "', {}, function(res) { 
        	" . $this->call . "
        	let option = " . array_encode_to_js($this->option) . ";
        	option.series = " . $this->series . ";
            " . $this->myChart . ".setOption(option);
        }); ";
	}
}
