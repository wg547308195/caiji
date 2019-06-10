<?php
namespace app\api\controller;
use think\exception\HttpResponseException;
use think\facade\Request;
use think\facade\Response;

class Api
{

    protected $request;
    protected $params;

    protected $code = 200;
    protected $format = 'json';
    protected $options = [];

    protected $limit;
    protected $page;

    public function __construct() {
        $this->request = Request::instance();
        $this->params = $this->request->param();
        $this->limit = isset($this->params['limit']) ? $this->params['limit'] : 12;
        $this->page = (isset($this->params['page']) && is_numeric($this->params['page'])) ? max(1, $this->params['page']) : false;
        $this->order = $this->request->post("order", "");
        // 动态设置返回的日期格式
        config('database.datetime_format', 'Y-m-d H:i:s');

        if($this->request->method() == 'OPTIONS') {
            header("Access-Control-Allow-Origin:*");
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
            header("Access-Control-Allow-Headers: platform,version,timestamp,format, Origin, X-Requested-With, Content-Type, Accept");
            return $this->response('success');
        }

        $this->initialize();
    }


    protected function initialize()
    {
        $formats = ['xml', 'json', 'jsonp'];
        $this->params['format'] = strtolower($this->request->get('format', 'json'));
        $this->format = in_array($this->params['format'], $formats) ? $this->params['format'] : $this->format;

        $request_time = $this->request->server('REQUEST_TIME');
        if(!$request_time || time() - $request_time > 30) {
            return $this->response('请求已过期', [], -99999);
        }
    }

    /**
     * 输出返回数据
     * @param string $message 提示内容
     * @param array $result 返回数据
     * @param int $code HTTP状态码
     */
    protected function response($message, $result = [], $code = 200)
    {
        header("Access-Control-Allow-Origin:*");
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
        header("Access-Control-Allow-Headers: platform,version,timestamp,format, Origin, X-Requested-With, Content-Type, Accept");
        
        $data = [
            'code'    => $code,
            'message' => $message,
        ];


        if(!empty($result) || (is_array($result) || empty($result))) {
            $data['result'] = $result;
            if(is_numeric($result)){
                if($result == 99999){
                    unset($data['result']);
                }
            }
            
        }

        switch ($this->format) {
            case 'xml':
                $this->options['root_node'] = 'root';
                break;
            default:
                # code...
                break;
        }
        $response = Response::create($data, $this->format)
            ->options($this->options)
            ->code($this->code);
        throw new HttpResponseException($response);
    }



}
