<?php

/**
 * 
 * @license read license.txt
 * @author sun <sunkangchina@163.com>
 * @copyright (c) 2021 
 */

namespace lib;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**

生成多个worksheet事例：
$where = [
    'status'    => 1,
    'date[>=]' => $start,
    'date[<=]' => $end,
];
$title  = [
      'invoice_number'   => '发票号码',
      'customer_name'    => '供应商',
      'product_num'      => '规格型号',
      'num'              => '数量',
      'price'            => '单价',
      'total_price'      => '金额',
]; 
//专票
$where['invoice_type'] = 1;
$values = invoice_set_xls_data($where,$title);
//普票
$where['invoice_type'] = 2;
$new_data = invoice_set_xls_data($where,$title);
//第一个worksheet
Xls::$label = '专票';
Xls::$sheet_width = [
    'A' => "15",
    'B' => "36",
    'C' => "30",
    'D' => "10",
    'E' => "10",
    'F' => "10",
];
//更多worksheet，如果只是一个，可不用下面代码，直接跳到Xls::create处
Xls::$works = [
    [
        'title' => $title,
        'label' => '普票',
        'data'  => $new_data,
        'width' => Xls::$sheet_width,
    ]
];
Xls::create($title, $values, $name, true);
 */
class Xls
{

    public static $base_url = '/uploads/tmp/xls/';
    public static $sheet_width = [];
    public static $works = [];
    public static $label = "";
    //title是第几行的，默认第一行是标题信息
    public static $title_index = 1;
    //取格式化表格内容
    public static $_format_val = false;
    /**
     * 生成XLSX
     * $xls_titles 
     * [
     * 'name' => '产品名称',
     * 'sku_num' => '内部编号',
     * ] 
     * $data = [
     *      ['name'=>'test','sku_num'=>1001]
     * ]
     * $name = '测试'
     * @return string  xls url
     */
    public static function create($xls_titles, $data, $name, $is_output_to_brower = false)
    {
        $base_url   = self::$base_url;
        $save_path  = PATH . $base_url;
        if (!is_dir($save_path)) {
            mkdir($save_path, 0777, true);
        }
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        if (self::$label) {
            $first = self::$label;
            $sheet->setTitle(self::$label);
            $sheet = $spreadsheet->getSheetByName(self::$label);
        }
        $options = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            ],
        ];
        //设置一个worksheet
        self::_sheet($sheet, $xls_titles, $data, $options, self::$sheet_width);
        //追加更多worksheet
        if (self::$works) {
            foreach (self::$works as $vv) {
                $label = $vv['label'];
                $myWorkSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, $label);
                $spreadsheet->addSheet($myWorkSheet);
                $sheet = $spreadsheet->getSheetByName($label);
                self::_sheet($sheet, $vv['title'], $vv['data'], $options, $vv['width']);
            }
        }
        $sheet = $spreadsheet->setActiveSheetIndex(0);
        $writer = new Xlsx($spreadsheet);
        //向浏览器输入        
        if ($is_output_to_brower) {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $name . '.xlsx"');
            header('Cache-Control: max-age=0');
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
            return;
        }
        /**
         * 生成产品xls文件
         */
        $writer->save($save_path .  $name . '.xlsx');
        return $base_url . $name . '.xlsx';
    }
    /**
     * 内部使用
     * 处理每行值
     */
    public static function _sheet(&$sheet, $xls_titles, $data, $options, $sheet_width)
    {
        //设置固定宽度
        if ($sheet_width) {
            foreach ($sheet_width as $k => $v) {
                $sheet->getColumnDimension((string)$k)->setWidth((string)$v);
                $sheet->getStyle($k)->applyFromArray($options);
            }
        } else {
            foreach ($sheet->getColumnIterator() as $column) {
                $index =  $column->getColumnIndex();
                $index =  (string)$index;
                $sheet->getColumnDimension($index)->setAutoSize(true);
                $sheet->getStyle($column->getColumnIndex())->applyFromArray($options);
            }
        }
        //xls 第一行
        $k = 1;
        foreach ($xls_titles as $v) {
            $sheet->setCellValue(Xls::getCol(1, $k), $v);
            $k++;
        }
        $i = 2;
        foreach ($data as $v) {
            $j = 1;
            foreach ($xls_titles as $k1 => $v1) {
                if ($v[$k1]) {
                    $sheet->setCellValue(Xls::getCol($i, $j), $v[$k1]);
                    $j++;
                }
            }
            $i++;
        }
    }
    /**
     * 根据 列、行返回对应xls的key
     * @example 
     * <code>
     * $spreadsheet = new Spreadsheet();
     *   $sheet = $spreadsheet->getActiveSheet();
     *   //xls开始行
     *    $i = 1;
     *    foreach ($array as $k => $v) {
     *        //xls列
     *        $j = 1;
     *        foreach ($v as $v1) {
     *            $sheet->setCellValue(Xls::getCol($i, $j), $v1);
     *            $j++;
     *        }
     *        $i++;
     *    }
     *    $writer = new Xlsx($spreadsheet);
     *    $writer->save($save_path . '/product.xlsx');
     * </code>
     * @param int $start_row 开始行
     * @param int $start_col 开始列
     * @return void
     */
    public static function getCol($start_row, $start_col)
    {
        $array = [];
        /**
         * xls支持最大列数
         */
        $j = 1;
        for ($i = 'A'; $i <= 'Z'; $i++) {
            $array[$j++] = $i;
        }
        /**
         *     第一列  第二列
         * 1   A1      B1
         * 2   A2      B2
         * 3   A3      B3
         * 
         * 传入 2 1 对应 A2
         * 传入 2 2 对应 B2
         */
        return $array[$start_col] . $start_row;
    }
    /**
     * 解析xlsx文件
     * 
        $lists = lib\Xls::load($file, [
            '产品编号' => 'product_num',
            '产品规格' => 'name',
            '注册证号' => 'cert_num',
            '单位'     => 'unit',
        ]); 
     * @param string $file 本地文件 
     * @param array $xls_row_1 xlsx文件第一行配置数组
     * @return array
     */
    public static function load_all($file, $row, $date = [], $worksheet_arr = [])
    {
        $spreadsheet   = IOFactory::load($file);
        $count = $spreadsheet->getSheetCount();
        if ($worksheet_arr) {
            foreach ($worksheet_arr as $i) {
                if ($i >= 0 && $i < $count) {
                    $worksheet = $spreadsheet->getSheet($i);
                    $new[] = self::load($file, $row, $date = [], $worksheet, $spreadsheet);
                }
            }
        } else {
            for ($i = 0; $i < $count; $i++) {
                $worksheet = $spreadsheet->getSheet($i);
                $new[] = self::load($file, $row, $date = [], $worksheet, $spreadsheet);
            }
        }
        return $new;
    }
    /*
    * 取XLS sheet数
    */
    public static function load_count($file)
    {
        $spreadsheet   = IOFactory::load($file);
        $count = $spreadsheet->getSheetCount();
        $arr = [];
        if ($count > 1) {
            for ($i = 0; $i < $count; $i++) {
                $arr[$i] = $i + 1;
            }
            return $arr;
        }
    }
    public static function load($file, $xls_row_1 = [
        '产品编号' => 'product_num',
        '产品规格' => 'name',
    ], $column_use_date = [], $worksheet = '', $spreadsheet = '')
    {
        //application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
        //如果需要判断类型，请在这里加判断
        $type          = mime_content_type($file);
        if (!$spreadsheet) {
            $spreadsheet   = IOFactory::load($file);
            $worksheet     = $worksheet ?: $spreadsheet->getActiveSheet();
        }
        // 总行数
        $rows      = $worksheet->getHighestRow();
        // 总列数 A-F
        $columns   = $worksheet->getHighestColumn();
        $index     = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($columns);
        for ($row = 1; $row <= $rows; $row++) {
            for ($i = 0; $i <= $index; $i++) {

                if (self::$_format_val) {
                    $name = $worksheet->getCellByColumnAndRow($i, $row)->getFormattedValue();
                } else {
                    $name = $worksheet->getCellByColumnAndRow($i, $row)->getValue();
                }

                if ($row > 1 && $column_use_date && in_array($i, $column_use_date)) {
                    if (is_string($name)) {
                        $name = trim($name);
                    }
                    if (!$name) {
                        $name = '9999-12-31';
                    }
                    if ($name == '永久' || $name == '1970-01-01') {
                        $name = '9999-12-31';
                    } else {
                        $old_name = $name;
                        $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($name);
                        $name = date("Y-m-d", $date);
                        $n    = date("Y", $date);
                        if ($n && $n < 2020) {
                            $name = $old_name;
                        }
                    }
                }
                //修正解析xls出现的某个值解析出来是个对象的问题。
                if (is_object($name)) {
                    $name = $name->__toString();
                }
                $list[$row][]  = $name;
            }
        }

        /*foreach ($list as $key => $value) {
            //时间字段是第4个
            unset($value[4]);
            $v = implode(" ", $value);
            if (!trim($v)) {
                unset($list[$key]);
            }
        } */
        //头文件
        $row = $list[self::$title_index];
        //字段
        $kk  = [];
        foreach ($row as $k => $v) {
            if ($v) {
                //从xls解析的有可能有空格。
                $v = trim($v);
                $kk[$k] = $xls_row_1[$v];
            }
        }
        unset($list[self::$title_index]);
        $lists = [];
        foreach ($list as $k => $v) {
            $data = [];
            foreach ($kk as $j => $key) {
                if (!$data[$key]) {
                    $data[$key] = $v[$j];
                }
            }
            $lists[$k] = $data;
        }
        return $lists ?: [];
    }
}
