<?php 
/**
 * 
 * @license read license.txt
 * @author sun <sunkangchina@163.com>
 * @copyright (c) 2021 
 */
/**
* PDF 
* 

ubuntu:
sudo apt install pdftk-java 
sudo apt install poppler-utils 
sudo apt install texlive-extra-utils
sudo apt install libimage-exiftool-perl

centos:
yum install pdftk 
yum install pdftk-java 
yum install poppler-utils
yum install perl-Image-ExifTool.noarch

$file = PATH.'1.pdf';  
$pdf = new \app\printer\Pdf($file); 
print_r($pdf->getInfo());
print_r($pdf->getPages());

//设置标题 作者 关键词
$pdf = new \app\printer\Pdf($file,$output); 
$pdf->setTitle('标题')
    ->setAuthor('作者')
    ->setKeyWords('关键词1，关键词2')
    ->runCmd();

*/
namespace lib; 
use iio\libmergepdf\Merger;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
class Pdf{

	public $data;
    public $file;
    public static $_key;  
    public $output;
    public $cmd = "pdfjam ";
    
    public function __construct($file = null,$output = null){
        $this->file   = $file;
        $this->output = $output;
    }
    /**
     * 设置标题
     *  pdfjam --pdftitle 我是题目 --pdfauthor 我是作者 --pdfkeywords "关键词1，关键词2" input.pdf -o output.pdf
     */
    public function setTitle($title){
        $this->cmd = " --pdftitle $title "; 
        return $this;
    }
    /**
     * 设置作者
     */
    public function setAuthor($author){
       $this->cmd .= " --pdfauthor $author ";
       return $this;
    }
    /**
     * 设置关键词如 关键词1，关键词2
     */
    public function setKeyWords($keywords){
       $this->cmd .= " --pdfkeywords $keywords ";
       return $this;
    }
    /**
    * 执行cmd
    */
    public function runCmd()
    {
        $output = $this->output;
        if(!$output){
            $output = $this->file;
        }
        $this->cmd .= " ".$this->file."  -o  ".$output;
        exec($this->cmd);
    }
    
    /**
     * 取PDF是横排还是竖排
     Array
    (
        [header] => Array
            (
                [ModDate] => D
                [Creator] => Microsoft® PowerPoint® 2019
                [CreationDate] => D
                [Producer] => Microsoft® PowerPoint® 2019
                [Author] => Microsoft Office User
                [Title] => PowerPoint 演示文稿
            )
        文档长宽
        [dimensions] => Array
            (
                [0] => 960
                [1] => 540
            )
        2是横版，1是竖版
        [dimensions_type] => 2
    )

     */
    public function getInfo(){
         $md5 = "pdftk:getinfo:".md5(file_get_contents($this->file)); 
         $cmd = "pdftk ".$this->file." dump_data ";
         exec($cmd,$out);
         $output  = [];
         $new_arr = [];
         $j = -1;
         foreach($out as  $i=>$v){ 
             if(strpos($v,':') !== false){
                 $arr = explode(':',$v);
                 $a = $arr[0];
                 $b = $arr[1]; 
                 if($a && $b){
                     $a = trim($a);
                     $b = trim($b); 
                     $new_arr[$key][$j][$a] = $b;  
                 }
             }else{
                 $key = trim($v);  
                 $j++;
             }
         }
         $lists = [];
         foreach ($new_arr as $k=>$v){  
             $output[$k] = array_values($v);
         }
         foreach($output['InfoBegin'] as $v){
             $header[$v['InfoKey']] = $v['InfoValue'];
         }
         $PageMediaDimensions = $output['PageMediaBegin'][0]['PageMediaDimensions'];
         $output = [];
         $output['header'] = $header;
         $output['dimensions'] = explode(" ",$PageMediaDimensions);
         //2是横版，1是竖版
         $output['dimensions_type'] = $output['dimensions'][0] > $output['dimensions'][1]?2:1;  
         return $output;
    } 
    /**
     * 取pdf页数
     */
    public function getPages(){
         $cmd = "pdftk ".$this->file." dump_data | grep NumberOfPages";
         exec($cmd,$out);
         if($out[0]){
            return trim(str_replace("NumberOfPages:","",$out[0]));   
         }
    }

	 /**
     * ::合并pdf
     * 输入的数组必须是.pdf格式
     */
    public static function merger($data = [], $save_name = null)
    {
        $uni_key = "pdf-merger-" . md5(json_encode($data));
        if (!$save_name) {
            $save_name = $uni_key . '.pdf';
        } else {
            $save_name = $save_name . '.pdf';
        }
        $path = '/uploads/pdfmerger/' . date('Ymd') . '/';
        $dir  = PATH . $path;
        create_dir([$dir]);
        $new_file = $dir . $save_name;
        
        foreach ($data as $k => $file) {
            if (!file_exists($file)) {
                unset($data[$k]);
            }
        } 
        $merger = new Merger;
        $merger->addIterator($data);
        $pdf    = $merger->merge();
        file_put_contents($new_file, $pdf);
        return $path . $save_name;
    }
	/**
	 * ::加载pdf，自动判断是否使用了背景文字水印 
	 * @param string $text
	 * @return void
	 */
	public static function pdf_auto($text = null)
	{
	    if ($text) {
	        $pdf = self::watermark($text);
	    } else {
	        $pdf = self::init();
	    }
	    return $pdf;
	}

	/**
	 * ::PDF操作
	 * https://mpdf.github.io/
	 * @return void
	 */
	public static function init($font_size = 9)
	{
	    $tempDir = PATH . '/data/runtime';
	    if (!is_dir($tempDir)) {
	        mkdir($tempDir, 0777, true);
	    }
	    $defaultConfig = (new  ConfigVariables())->getDefaults();
	    $fontDirs = $defaultConfig['fontDir'];
	    $defaultFontConfig = (new  FontVariables())->getDefaults();
	    $fontData = $defaultFontConfig['fontdata'];
	    $pdf = new Mpdf([
	        'tempDir' => $tempDir,
	        'default_font_size' => $font_size,
	        'fontDir' => array_merge($fontDirs, [
	            PATH . '/data/font',
	        ]),
	        'fontdata' => $fontData + [
	            'simfang' => [
	                'R' => 'simfang.ttf',
	                'I' => 'simfang.ttf',
	            ],
	            'arial' => [
	                'R' => 'arial.ttf',
	                'I' => 'arial.ttf',
	            ],
	        ],
	        'default_font' => 'simfang'
	    ]);
	    return $pdf;
	}

	/**
	 * ::PDF文字水印
	 *
	 * @param string $text
	 * @return void
	 */
	public static function watermark($text = null)
	{
	    $pdf = self::pdf();
	    $pdf->SetWatermarkText($text);
	    $pdf->showWatermarkText = true;
	    $pdf->watermark_font = 'simfang';
	    $pdf->watermarkTextAlpha = 0.1;
	    $pdf->watermarkImageAlpha = 0.5;
	    return $pdf;
	}
}
