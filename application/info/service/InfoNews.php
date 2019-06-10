<?php
namespace app\info\service;
use app\common\library\Service;

class InfoNews extends Service 
{
    use \app\common\library\traits\Model;

    protected function _initialize() {
        parent::_initialize();
    }

    /**
	 * 列表
	 * @param [array] 	$maps 		[条件信息]
	 * @param [int]   	$limit 		[分页条数]
	 * @param [int] 	$page 		[分页页码]
	 * @param [string]  $order 		[排序]
	 * @param [string]  $field 		[查询字段]
	 * @param [array]   $relations 	[关联数据]
	 * @param [array]   $attrs 		[获取器数据]
	 * return mix
	 */
    public function lists($maps = '',$limit = 12,$page = 1,$order = 'id DESC',$field = true,$relations = [],$attrs = []){
    	$model = model('info/info_news');

        if (isset($maps['status'])) {
            $model = $model->where('status', '=', $maps['status']);
        }
        if (!empty($maps['type'])) {
            $model = $model->where('type', '=', $maps['type']);
        }
        if (isset($maps['keywords'])) {
            $model = $model->where('title|summary','like','%'.$maps['keywords']."%");
        }
        if (isset($maps['start_time']) || isset($maps['end_time'])) {
            $model = $model->whereTime('create_time', 'between',[$maps['start_time'],$maps['end_time']]);
        }
        if (!empty($maps['site_code'])) {
            $model = $model->where('site_code','like',$maps['site_code']."%");
        }

        $model = $model->order($order)->field($field);

        if($page !== false) {
            $result = $model->paginate($limit, '', ['page' => $page]);
        } else {
            $result = $model->limit($limit)->select();
        }

        /* 关联数据获取 */
        if(!empty($relations)) {
            foreach ($result as $key => $value) {
                array_map(function($e) use (&$value) {
                    $e = trim($e);
                    $value->$e = $value->$e ?: new \stdClass(); // 注意没有数据不要返回默认的 NULL
                }, $relations);
            }
        }

        /* 获取器数据 */
        if(!empty($attrs)) {
            foreach ($result as $key => $value) {
                array_map(function($attr) use (&$value) {
                    $attr = trim($attr);
                    return $value->$attr = $value->getAttr($attr);
                }, $attrs);
            }
        }

        return $result;
    }

    /**
	 * 详情
	 * @param [array] 	$maps 		[查询条件]
	 * @param [string] 	$field 		[查询字段]
	 * @param [array] 	$relations  [关联数据]
	 * @param [array] 	$attrs      [获取器数据]
	 * return mix
	 */
    public function detail($maps = '',$field = true,$relations = [],$attrs = []){
    	$model = model('info/info_news');

    	if (!empty($maps['id'])){
    		$model = $model->where('id', '=', $maps['id']);
    	}
        if (!empty($maps['status'])){
            $model = $model->where('status', '=', $maps['status']);
        }

    	$result = $model->field($field)->relation($relations)->find();

        /* 获取器数据 */
        if(!empty($attrs)) {
            array_map(function($attr) use (&$result) {
                $attr = trim($attr);
                return $result->$attr = $result->getAttr($attr);
            }, $attrs);
        }
        return $result;
    }

    /**
	 * 创建
	 * @param [array] $data [信息]
	 * return mix
	 */
	public function create($data = []){
        $model = model('info/info_news');
        \Db::startTrans();
        try {
        	$validate = new \app\info\validate\InfoNews;
			if (!$validate->scene('add')->check($data)){
				\Db::rollback();
				$this->error = $validate->getError();
				return false;
			}

        	$model->isUpdate(false)->save($data);
        } catch (\Exception $e) {
        	\Db::rollback();
        	\Log::write('创建失败：'.$e);
            $this->error = $e->getMessage();
            return false;
        }
		\Db::commit();
		return $model;
	}

	/**
	 * 编辑
	 * @param [array] $data [信息]
	 * return mix
	 */
	public function save($data = []){
		$model = model('info/info_news');
		\Db::startTrans();
        try {
            $result = $model->getOrFail($data['id']);
        } catch (\Exception $e) {
            $this->error = '信息不存在';
            return false;
        }
        try{
        	$validate = new \app\info\validate\InfoNews;
			if (!$validate->scene('edit')->check($data)){
				\Db::rollback();
				$this->error = $validate->getError();
				return false;
			}
			
			$result->isUpdate(true)->save($data);
        } catch (\Exception $e) {
            \Db::rollback();
            \Log::write('编辑失败：'.$e);
            $this->error = $e->getMessage();
            return false;
        }
		\Db::commit();
		return $result;
	}

    /**
     * 发布
     * @param [array] $data [信息]
     * return mix
     */
    public function release($data = []){
        $model = model('info/info_news');
        \Db::startTrans();
        try {
            $result = $model->getOrFail($data['id']);
        } catch (\Exception $e) {
            $this->error = '信息不存在';
            return false;
        }
        try{
            $validate = new \app\info\validate\InfoNews;
            if (!$validate->scene('release')->check($data)){
                \Db::rollback();
                $this->error = $validate->getError();
                return false;
            }
            
            $result->isUpdate(true)->save($data);
        } catch (\Exception $e) {
            \Db::rollback();
            \Log::write('发布失败：'.$e);
            $this->error = $e->getMessage();
            return false;
        }
        \Db::commit();
        return $result;
    }

	/**
	 * 删除
	 * @param [int] $id [主键id]
	 * return mix
	 */
	public function delete($id = 0){
		$model = model('info/info_news');
		\Db::startTrans();
        try {
            $result = $model->getOrFail($id);
        } catch (\Exception $e) {
            $this->error = '信息不存在';
            return false;
        }
		try {
			$result->destroy($id);
		} catch (\Exception $e) {
			\Db::rollback();
			\Log::write('删除失败：'.$e);
            $this->error = $e->getMessage();
            return false;
		}
		\Db::commit();
		return $result;
	}

    //页面统计
    public function statistics(){
        $result = model('info/info_news')->field('type,count(*) as count')->group('type')->select();
        $type = [
            1 => '导航新闻',
            2 => '轮播新闻',
            3 => '头条新闻',
            4 => '最新动态'
        ];
        $return = [];
        foreach ($type as $key => $value) {
            $return[$key]['type'] = $value;
            $return[$key]['count'] = 0;
            foreach ($result as $k => $v) {
                if ($key == $v['type']){
                   $return[$key]['count'] = $v['count'];
                }
            }
        }
        return $return;
    }
}