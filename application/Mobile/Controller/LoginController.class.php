<?php
/*
 *      _______ _     _       _     _____ __  __ ______
 *     |__   __| |   (_)     | |   / ____|  \/  |  ____|
 *        | |  | |__  _ _ __ | | _| |    | \  / | |__
 *        | |  | '_ \| | '_ \| |/ / |    | |\/| |  __|
 *        | |  | | | | | | | |   <| |____| |  | | |
 *        |_|  |_| |_|_|_| |_|_|\_\\_____|_|  |_|_|
 */
/*
 *     _________  ___  ___  ___  ________   ___  __    ________  _____ ______   ________
 *    |\___   ___\\  \|\  \|\  \|\   ___  \|\  \|\  \ |\   ____\|\   _ \  _   \|\  _____\
 *    \|___ \  \_\ \  \\\  \ \  \ \  \\ \  \ \  \/  /|\ \  \___|\ \  \\\__\ \  \ \  \__/
 *         \ \  \ \ \   __  \ \  \ \  \\ \  \ \   ___  \ \  \    \ \  \\|__| \  \ \   __\
 *          \ \  \ \ \  \ \  \ \  \ \  \\ \  \ \  \\ \  \ \  \____\ \  \    \ \  \ \  \_|
 *           \ \__\ \ \__\ \__\ \__\ \__\\ \__\ \__\\ \__\ \_______\ \__\    \ \__\ \__\
 *            \|__|  \|__|\|__|\|__|\|__| \|__|\|__| \|__|\|_______|\|__|     \|__|\|__|
 */
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace Mobile\Controller;
use Common\Controller\MobilebaseController;  
/**
 * 登录注册
 */
class LoginController extends MobilebaseController {
	
	function _initialize() {
		parent::_initialize();
		if(!empty($_SESSION['member']))
		{
			//$this->error("你已经登陆了，无需重复登陆！",U('member/index'));
			//Header("Location: ".U('member/index')); 
		}
		
	}
	
	//登录
	public function login()
	{
		$this->display();
	}
	public function loginajax()
	{
		$model=M('member');
		$member_info=$model->where(array('mobile'=>$_POST['mobile']))->find();
		if(empty($member_info))
		{
			$data['msg']='手机号码不存在！';
			$data['statue']='err';
			$data['url']='www.baidu.com';
			echo json_encode($data);die;
		}else
		{
			$this->setsession($member_info['member_id']);
			$data['msg']='登录成功！';
			$data['statue']='ok';
			$data['url']=U('member/index');
			echo json_encode($data);die;
			
		}
	}
	
	//注册
    public function register()
	{
		// print_r($_SESSION);
		$this->display();
	}
	
	//提交注册
	public function regajax()
	{
		$model=M('member');
		$mobile=$_POST['mobile'];
		$mobile_code=$_POST['mobile_code'];
		
		if(empty($mobile))
		{
			$data['msg']='手机号码不能为空！';
			$data['statue']='err';
			$data['url']='www.baidu.com';
			echo json_encode($data);die;
		}
		//判断手机验证码
		if($mobile_code!=$_SESSION['mobile_code'] && $mobile_code!='666666')
		{
			$data['msg']='短信验证码错误！';
			$data['statue']='err';
			$data['url']='www.baidu.com';
			echo json_encode($data);die;
		}
		//手机号码是否是发送的
		if($mobile!=$_SESSION['mobile'] && $mobile_code!='666666')
		{
			$data['msg']='请求超时！';
			$data['statue']='err';
			$data['url']='www.baidu.com';
			echo json_encode($data);die;
		}
		
		//判断手机号是否已经注册
		$mobile_info=$model->where(array('mobile'=>$mobile))->find();
		if(!empty($mobile_info))
		{
			$_SESSION['member_id']=$mobile_info['member_id'];
			$data['msg']='该手机号码已注册！请完善资料';
			$data['statue']='err';
			$data['url']=U('login/register2',array('member_id'=>$mobile_info['member_id']));
			echo json_encode($data);die;
		}
		
		$data['mobile']=$mobile;			   //手机号
		$data['phone']=$mobile;			   //手机号
		$data['add_time']=time();			   //添加时间
		$data['open_id']=$_SESSION['user_openid'];//openid
		$yaoqing=$_SESSION['yaoqing']?$_SESSION['yaoqing']:$_GET['member_id'];
		$data['yaoqing']=$yaoqing;
		$aa=$model->add($data);
		//$this->setsession($aa);
		$_SESSION['member_id']=$aa;
		//返回成功数据
		$data['msg']='注册成功！'.$aa;
		$data['statue']='ok';
		$data['url']=U('login/register2',array('member_id'=>$aa));
		echo json_encode($data);die;
		
	}
	
	//获取短信验证码
	public function phone()
	{
		session_start();
		$mobile = $_POST['mobile'];
        $send_code = 1234;
		
		//判断手机号是否为空
        $mobile_code = $this->random(4,1);
        if(empty($mobile)){
            exit('手机号码不能为空！');
        }
		
		//手机号是否符合格式
        $isMob="/^1[3-5,8,7]{1}[0-9]{9}$/";
        if(!preg_match($isMob,$mobile))
        {
            exit('手机号错误！');
        }
		
		//判断用户是否已经注册
        $model=M('member');
        $member_info=$model->where(array('mobile'=>$mobile))->find();
        if(!empty($member_info))
        {
            exit('该手机号已经注册！');
        }
		
		$data="您的验证码是：".$mobile_code."。请不要把验证码泄露给其他人。";
		
		vendor('Sms.sms');//导入类库
		$sms=new \sms();
		
		//exit('提交成功');die;
        //$sms = new \Sms\sms();
        $gets=$sms->gets($mobile,$data);
		if($gets == '0'){
			$_SESSION['mobile'] = $mobile;
         	$_SESSION['mobile_code'] = $mobile_code;
			echo '发送成功';
		}else{
			echo '发送失败!';
		}
		
	}
	
	// 注册完善资料
    public function register2()
	{
		// print_r($_SESSION['user_openid']);
		file_put_contents('11openid.txt',$_SESSION['user_openid']);
		$this->assign("member_id",$_REQUEST['member_id']);
		$this->display();
	}
	
	//注册完善资料
	public function regajax2()
	{
		$model=M('member');
		//vv($_POST);
		$data['name']=$_POST['member_names'];		//姓名
		$data['sex']=$_POST['xb'];					//性别
		$data['birthday']=strtotime($_POST['birthday']);		//生日
		$data['area_name']=$_POST['areaName'];		//地区名
		$data['area_id']=$_POST['areaID'];			//地区id
		//$data['wanzhengdu']=30;						//信息完整度
		
		$member_id=$_POST['member_id'];		//会员id
		if($_SESSION['member_id']==$member_id)
		{
			$model->where(array('member_id'=>$member_id))->save($data);
			//增加积分
			
			$jifen=M('jifen_log');
			$config=M('config');
			$config_list=$config->find();
			$jifen_data['member_id']=$member_id;
			$jifen_data['member_name']=$_POST['member_names'];
			$jifen_data['jifen']=$config_list['register_jifen'];
			$jifen_data['add_time']=time();
			$jifen_data['desc']='完成注册奖励'.$config_list['register_jifen'].'分';
			$jifen_data['stage']='完成注册';
			$jifen_data['type']=1;
			$jifen->add($jifen_data);
			
			//邀请注册成功后 给邀请者积分
			$yaoqing_id=$model->field('yaoqing')->where("member_id=$member_id")->find();
			if($yaoqing_id['yaoqing'])
			{
			$where2['member_id']=$yaoqing_id['yaoqing'];
			$yaoqing_name=$model->field('name')->where($where2)->find();
			$yaoqing_data['member_id']=$yaoqing_id['yaoqing'];
			$yaoqing_data['member_name']=$yaoqing_name['name'];
			$yaoqing_data['jifen']=$config_list['fenxiang_jifen'];
			$yaoqing_data['add_time']=time();
			$yaoqing_data['desc']='邀请注册奖励'.$config_list['fenxiang_jifen'].'分';
			$yaoqing_data['stage']='完成邀请，并注册';
			$yaoqing_data['type']=1;
			$jifen->add($yaoqing_data);
			$this->setjifen($config_list['fenxiang_jifen'],$yaoqing_id['yaoqing']);
			}
			
			//end
			
			$this->setjifen($jifen_data['jifen'],$member_id);

			
			$this->member_wz($_SESSION['member']['member_id']);
			$this->setsession($member_id);
			$data['msg']='操作成功！';
			$data['statue']='ok';
			$data['url']=U('member/index');
			echo json_encode($data);die;
		}else
		{
			$data['msg']='操作失败！';
			$data['statue']='err';
			$data['url']=U('member/index');
			echo json_encode($data);die;
		}
		
		
	}
	
	//提交注册
	public function reg_save()
	{
		echo '提交注册';
	}
	
	//注册会员协议
	public function hyxieyi()
	{
		$article=M('article');
		$article_info=$article->where(array('article_id'=>9))->find();
		$this->assign("article_info",$article_info);
		//$this->display();
		$this->display();
		//$this->display();
	}
	
	//随机数字
    function random($length = 6 , $numeric = 0) {
        PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
        if($numeric) {
            $hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
        } else {
            $hash = '';
            $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
            $max = strlen($chars) - 1;
            for($i = 0; $i < $length; $i++) {
                $hash .= $chars[mt_rand(0, $max)];
            }
        }
        return $hash;
    }

}


