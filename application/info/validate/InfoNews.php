<?php 
namespace app\info\validate;
use think\Validate;

class InfoNews extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
      'title|标题' => 'require|max:255',
      'summary|摘要' => 'require',
      'content|内容' => 'require',
      'source|来源' => 'require|max:255',
      'nav_img|导航图' => 'require',
      'attachment_id|附件'  =>  'number|max:10',
      'user_id|发布人' => 'require|number|max:5',
      'user_name|发布人名称' => 'require|max:50',
      'update_user_id|更新人' => 'require|number|max:5',
      'update_user_name|更新人名称' => 'require|max:50',
      'type|类别' => 'require|in:1,2,3,4',
      'status|状态' => 'require|in:0,1',
      'release_time|发布时间' => 'require|number|max:10',
    ];

     /**
     * 提示消息
     */
    protected $message = [
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => ['title','summary','content','source','nav_img','attachment_id','user_id','user_name','type','release_time'],
        'edit' => ['title','summary','content','source','nav_img','attachment_id','type','update_user_id','update_user_name'],
        'release' => ['status','release_time']
    ];
}