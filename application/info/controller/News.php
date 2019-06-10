<?php

namespace app\info\controller;

use think\Request;

class News extends Init
{

    public function initialize()
    {
        parent::initialize();
        $this->service = model('info/info_news', 'service');
    }

    public function list(Request $request)
    {
    	//头条新闻
    	$recommend = model('info/info_news')->where('status','=',1)->where('type','=',3)->limit(1)->find();
    	$list = $this->service->lists(['status'=>1],$this->limit,$this->page,'release_time DESC');
        return $this->fetch('list',[
        	'list' => $list,
        	'recommend' => $recommend,
        	'page' => $list->render()
        ]);
    }

    public function detail(Request $request)
    {
    	$id = $request->get('id/d',0);
    	//详情
    	$info = $this->service->detail(['id'=>$id]);
    	//热点推荐
    	$hot = $this->service->lists(['status'=>1],6,1);
    	//上一篇
    	$prev = model('info/info_news')->where('id','<',$id)->order('id DESC')->limit(1)->find();
    	//下一篇
    	$next = model('info/info_news')->where('id','>',$id)->limit(1)->find();
    	$this->assign('prev',$prev);
    	$this->assign('next',$next);
    	$this->assign('hot',$hot);
    	$this->assign('info',$info);
        return $this->fetch();
    }


}
