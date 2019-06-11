<?php
namespace app\collect\service;
use app\common\library\Service;

class collect extends Service 
{

    protected  function _initialize() {
        parent::_initialize();
       
    }

    public function get_message($file = '')
    {
      try {
          $url = 'http://mfhmcs.sidwit.com/';
          $opts = array(
            'http'=>array(
              'method'=>"GET",
              'header'=>"Accept-language: en\r\n" .
              "Cookie: foo=bar\r\n"
            )
          );
            $context = stream_context_create($opts);
            $fp = fopen($url, 'r', false, $context);
            $result = '';
            while(!feof($fp)) {
            $result.= fgets($fp, 1024);
            }
            fpassthru($fp);
            fclose($fp);
            //<img > 标签的正则
            $reg = '/<img((?!src).)*src[\s]*=[\s]*[\'"](?<src>[^\'"]*)[\'"]/i';
            $res = preg_match_all($reg,$result,$matchAll);
            //print_r($matchAll);die;
            $result = $this->w_file('../public/images/w/','',$matchAll['src'], $url);

      } catch (\Exception $e) {
            $this->error = $e->get_message();
            return false;
      }
        return $result;
    }


    /**
    * 文件写入
    **/
    public function w_file($dir = '', $file_name = '',$data = [],$url = '')
    {
        //创建目录
        if(!is_dir($dir)){
            mkdir($dir);
        }
        if($file_name == ''){
            $file_name = date('Y-m-d H:i:s',time()).'.txt';
        }
        //写文件
        $file = fopen($dir.'/'.$file_name, 'w');
        foreach ($data as $key => $value) {
            if(empty($value)){
                continue;
            }
            if(!strstr($value, 'www') && !strstr($value, 'http')){
                $value = $url.$value;
            }
            //图片的后缀名需要的在添加
            fwrite($file, $value."\r\n");
        }
        fclose($file);
        return $_SERVER['SERVER_NAME'].'/'. str_replace('../public', '', $dir) .'/'.$file_name;
    }

}

