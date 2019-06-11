<?php
namespace app\common\service;
use app\common\library\Service;

class A extends Service
{
	public function _initialize()
	{
		parent::_initialize();
	}

	/**
	 * 获取数据
	 */
	public function fetch(){
		$result = model('notify/SysNotifyMessage','service')->where(['status'=>0])->order('id','asc')->limit(10)->select();
		if(empty($result)){
			return '';
		}
		return $result->toArray();
	}

	/**
	 * 处理数据
	 */
	public function task($arrs){
		foreach($arrs as $k=>$v){
			$notify_config = config('notify.'.$v['type']);
			$content = $v['content'];
			//短信
			if(isset($notify_config['sms'])){
				if($notify_config['sms']['enabled'] == true){
					//短信
					foreach($notify_config['sms'] as $key=>&$sms){
						if(is_array($sms)){
							foreach($sms as $k2=>&$v2){
								$is_match = preg_match_all("/{{(.+?)}}/",$v2,$match);
								$replaces = [];
								if($is_match >= 1){
									foreach($match[1] as $i=>$value){
										if(strpos($value,".") !== false){
											$str = explode('.',$value);
											if(isset($content[$str[0]][$str[1]])){
												$replaces["{{".$value."}}"] = $content[$str[0]][$str[1]];
											}
										}else{
											if(isset($content[$value])){
												$replaces["{{".$value."}}"] = $content[$value];
											}
										}
									}
									$v2 = str_replace(array_keys($replaces), $replaces, $v2);
								}
							}
						}else{
							$is_match = preg_match_all("/{{(.+?)}}/",$sms,$match);
							$replaces = [];
							if($is_match >= 1){
								foreach($match[1] as $i=>$value){
									if(strpos($value,".") !== false){
										$str = explode('.',$value);
										if(isset($content[$str[0]][$str[1]])){
											$replaces["{{".$value."}}"] = $content[$str[0]][$str[1]];
										}
									}else{
										if(isset($content[$value])){
											$replaces["{{".$value."}}"] = $content[$value];
										}
									}
								}
								$sms = str_replace(array_keys($replaces), $replaces, $sms);
							}
						}
					}

					$config = config('service.sms');
					$notifyFactory = new \app\notify\library\Notify('sms',$config);
					$notifyFactory->send($notify_config['sms']);
				}
			}

			//站内消息
			if(isset($notify_config['message'])){
				if($notify_config['message']['enabled'] == true){
					foreach($notify_config['message'] as $key=>&$station){
						$is_match = preg_match_all("/{{(.+?)}}/",$station,$match);
						$replaces = [];
						if($is_match >= 1){
							foreach($match[1] as $i=>$value){
								if(strpos($value,".") !== false){
									$str = explode('.',$value);
									if(isset($content[$str[0]][$str[1]])){
										$replaces["{{".$value."}}"] = $content[$str[0]][$str[1]];
									}
								}else{
									if(isset($content[$value])){
										$replaces["{{".$value."}}"] = $content[$value];
									}
								}
							}
							$station = str_replace(array_keys($replaces), $replaces, $station);
						}
					}
					$send_data = $notify_config['message'];
					$send_data['notify_id'] = $v['id'];
					$send_data['type'] = $v['type'];
					$send_data['model'] = $v['model'];
					$send_data['params'] = $v['content'];
					$config = ['1'];
					$notifyFactory = new \app\notify\library\Notify('message',$config);
					$notifyFactory->send($send_data);
				}
			}

			//手机push
			if(isset($notify_config['cps'])){
				if($notify_config['cps']['enabled'] == true){
					//手机push
					foreach($notify_config['cps'] as $key=>&$push){
						if(is_array($push)){
							foreach($push as $k2=>&$v2){
								$is_match = preg_match_all("/{{(.+?)}}/",$v2,$match);
								$replaces = [];
								if($is_match >= 1){
									foreach($match[1] as $i=>$value){
										if(strpos($value,".") !== false){
											$str = explode('.',$value);
											if(isset($content[$str[0]][$str[1]])){
												$replaces["{{".$value."}}"] = $content[$str[0]][$str[1]];
											}
										}else{
											if(isset($content[$value])){
												$replaces["{{".$value."}}"] = $content[$value];
											}
										}
									}
									$v2 = str_replace(array_keys($replaces), $replaces, $v2);
								}
							}
						}else{
							$is_match = preg_match_all("/{{(.+?)}}/",$push,$match);
							$replaces = [];
							if($is_match >= 1){
								foreach($match[1] as $i=>$value){
									if(strpos($value,".") !== false){
										$str = explode('.',$value);
										if(isset($content[$str[0]][$str[1]])){
											$replaces["{{".$value."}}"] = $content[$str[0]][$str[1]];
										}
									}else{
										if(isset($content[$value])){
											$replaces["{{".$value."}}"] = $content[$value];
										}
									}
								}

								$push = str_replace(array_keys($replaces), $replaces, $push);
							}
						}
					}

					$config = config('service.cps');
					$notifyFactory = new \app\notify\library\Notify('cps',$config);
					$notifyFactory->send($notify_config['cps']);
				}
			}
		}
	}

}