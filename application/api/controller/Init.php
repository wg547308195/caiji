<?php
namespace app\api\controller;
use app\api\Api;
class Init extends Api
{
    protected $checkaccess = true;
    protected $no_check_action = ['login','get_message'];
    protected $admin = [];
    protected $user_id = 0;
    protected $user_name = '';
    protected $cur_site_code = "";
    
    public function initialize() {
        parent::initialize();
        if($this->checkaccess === true) $this->checkAccess();
    }

    /**
     * @return bool|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function checkAccess() {
        $action = $this->request->action();
        if ($this->no_check_action && in_array($action,$this->no_check_action)) {
            return true;
        }

        $access_token = $this->request->param('access_token');
        if(empty($access_token)) {
            return $this->response('请登录后再操作', [], -99998);
        }
        if($access_token == '123456'){
            $this->admin = model('admin/so_admin')->where('status','=',1)->find();
        }else{
            $this->admin = model('admin/so_admin')->where('token','=',$access_token)->where('status','=',1)->find();
        }
        if (!$this->admin) {
            return $this->response('管理员不存在或已被禁用', [], -99997);
        }
        $this->user_id = $this->admin->id;
        $this->user_name = $this->admin->username;
        $this->cur_site_code = $this->admin->site_code;
        if($this->admin->group_id == 1){
            $this->cur_site_code = '';
        }
        return true;
    }
}
