<?php
/**
 * Created by PhpStorm.
 * User: xuewl
 * Date: 2018/1/2
 * Time: 15:02
 */

namespace app\common\library;

use think\model\concern\SoftDelete;

class Model extends \think\Model
{
	use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected $type = [
        "delete_time" => "integer",
    ];

    protected $modelValidate = false;
    protected $sceneValidate = false;


    public static function init()
    {
        self::event('before_write', function ($model) {
            if($model->modelValidate) {
                // 定义验证类
                $validate = validate($model->modelValidate);
                // 定义验证场景
                if($model->sceneValidate) {
                    $validate->scene($model->sceneValidate);
                }
                // 定义验证逻辑
                if (!$validate->check($model->getdata())) {
                    $model->error = $validate->getError();
                    return false;
                }
            }
        });
    }

    public function validate($class = null, $scene = null) {
        $this->modelValidate = $class;
        $this->sceneValidate = $scene;
        return $this;
    }

}
