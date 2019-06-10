<?php
namespace app\api\controller\v1\info;
use app\api\controller\v1\Init;

class Index extends Init
{

    public function initialize()
    {
        parent::initialize();
        $this->service = model('info/InfoNews','service');
    }
    
    /**
     * 列表
     * @param  string   $order       [排序]
     * @param  string   $field       [查询字段]
     * @param  array    $relations   [关联数据]
     * @param  array    $attrs       [获取器数据]
     * @return array
     */
    public function lists(){
        $maps = [];
        $maps['site_code'] = $this->cur_site_code;
        $order = $this->request->param('order/s','id DESC');
        $field = $this->request->param('field/s',true);
        $relations = array_filter($this->request->param('relations/a',[]));
        $attrs = array_filter($this->request->param('attrs/a',[]));

        $result = $this->service->lists($maps,$this->limit,$this->page,$order,$field,$relations,$attrs);
        return $this->response("success", $result);
    }


    /**
     * 详情
     * @param  int      $id          [id]
     * @param  string   $field       [查询字段]
     * @param  array    $relations   [关联数组]
     * @param  array    $attrs       [获取器数据]
     * @return array
     */
    public function info(){
        $maps = [];
        $maps['id'] = $this->request->param('id/d',0);
        $result = $this->service->detail($maps);
        return $this->response("success", $result);
    }
    
    /**
     * 创建
     * @param  array $data   [信息]
     * @return mix
     */
    public function create(){
        $data = $this->request->post();
        $result = $this->service->create($data);
        if (!$result){
            return $this->response($this->service->getError(), [], -400104);
        }
        return $this->response("success", $result);
    }

    /**
     * 编辑
     * @param  array $data   [信息]
     * @return mix
     */
    public function save(){
        $data = $this->request->post();
        $result = $this->service->save($data);
        if (!$result){
            return $this->response($this->service->getError(), [], -400105);
        }
        return $this->response("success", $result);
    }
    /**
     * 删除
     * @param  int $id   [ID]
     * @return mix
     */
    public function delete(){
        $id = $this->request->post('id',0);
        $result = $this->service->delete($id);
        if (!$result){
            return $this->response($this->service->getError(), [], -400106);
        }
        return $this->response("success", $result);
    }
}
