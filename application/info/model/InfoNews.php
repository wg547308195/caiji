<?php

namespace app\Info\model;

use app\common\library\Model;
/**
 * 新闻中心
 */
class InfoNews extends Model
{
    protected $table = 'so_info_news';

    protected $pk = 'id';

    protected $append = ['type_text','status_text'];
    
    protected $type = [
        'release_time' => 'timestamp:Y-m-d H:i:s'
    ];

    //文件
    public function attachment(){
        return $this->hasOne("app\\attachment\\model\\Attachment",'id', 'attachment_id')->field('url');
    }

    //类别：1:导航新闻；2：轮播新闻；3：头条新闻；4：最新动态
    public function getTypeTextAttr($value,$data){
        $type = [1=>'导航新闻', 2=>'轮播新闻', 3=>'头条新闻', 4=>'最新动态'];
        return $type[$data['type']];
    }

    //状态：0:未处理，1：已发布
    public function getStatusTextAttr($value,$data){
        $status = [0=>'未处理',1=>'已发布'];
        return $status[$data['status']];
    }
}
