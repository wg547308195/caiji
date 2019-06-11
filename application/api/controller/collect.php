<?php
namespace app\api\controller;
use app\api\controller\Init;

class collect extends Init
{

    public function initialize()
    {
        parent::initialize();
        $this->service = model('collect/collect','service');
   
    }
    
    public function get_message()
    {
        
        $url = 'http://hmcs.sidwit.com/mainPage/project_list';
        $result = model('collect/collect','service')->get_message($url);

        //echo('<div style="color:green">finish</div>');
         if (!$result){
            return $this->response($this->service->getError(), [], -400105);
        }
        return $this->response("success", $result);
    }

}
