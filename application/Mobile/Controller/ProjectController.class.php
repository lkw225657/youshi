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
 * 个人中心
 */
class ProjectController extends MobilebaseController {
	
	function _initialize() {
		parent::_initialize();
		$appid = 'wx71f172895fb20911'; 
	$secret = '447c05fa58111bc771f35aaf50d90f79';
	$_SESSION['yaoqing']=$_GET['member_id'];
	define('WX_HOME_URL_HOST', 'm.youshi.ltd');
	if(empty($_SESSION['user_openid'])||empty($_SESSION['member']['member_id']))
	{
			if (!isset($_GET["code"])) {
			$controller=ACTION_NAME;
			$enrol_state_str='';
			if(!$controller)
			{
				$controller='project_list';
			}
			if($_GET['enrol_state'])
			{
				$enrol_state_str="&enrol_state=".$_GET['enrol_state'];
			}
			// if($controller=='project_member')
			// {
			// 	$state='ing';
			// 	$strl='&state=ing';
			// }
			$u1 = "http://m.youshi.ltd/index.php?g=&m=project&a=".$controller.$enrol_state_str;
			$u1 = urlencode($u1);
			$url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri='.$u1.'&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect';
			// die($url);
	    	 header("Location:".$url);
	    	//$this->redirect($url);
			 exit();
			}
	
	$code = $_GET["code"]; 	
	$get_token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$secret.'&code='.$code.'&grant_type=authorization_code';
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL,$get_token_url); 
	curl_setopt($ch,CURLOPT_HEADER,0); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 ); 
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); 
	$res = curl_exec($ch); 
	curl_close($ch); 
	$json_obj = json_decode($res,true); 
	
	//根据openid和access_token查询用户信息 
	$access_token = $json_obj['access_token']; 
	$openid = $json_obj['openid']; 
	
	//openid 保存到 session
	$_SESSION['user_openid'] = $openid;				//用户openid

	
	$_SESSION['user_access_token'] = $access_token;	//Access Token
	
	//获取微信用户全部信息
	$get_user_info_url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN'; 	
	$ch = curl_init(); 
	curl_setopt($ch,CURLOPT_URL,$get_user_info_url); 
	curl_setopt($ch,CURLOPT_HEADER,0); 
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1 ); 
	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, 10); 
	$res = curl_exec($ch); 
	curl_close($ch); 
	
	//解析json 
	$user_obj = json_decode($res,true);

	$_SESSION['user_header_img'] = $user_obj['headimgurl'];
	//将邀请人的id存入到session

	}

		//将openid存进数据库
		//如果是老用户直接登录 新用户跳转至注册页面
			// print_r($_SESSION['user_openid']);die;
			$mem=M('member');
			$where['open_id']=trim($_SESSION['user_openid']);
			$mem_info=$mem->where($where)->find();
			if($mem_info)
			{
				$_SESSION['member']['member_id']=$mem_info['member_id'];
			}else{
				//跳转至填写手机号接受验证码的页面
			}
			//如果是之前登录但是没有存open_id的用户  存入open_id
			if($_SESSION['member']['member_id']&&!$mem_info)
			{
				$where1['member_id']=$_SESSION['member']['member_id'];
			  $old_mem=$mem->where($where1)->find();			
				if(!$old_mem['open_id'])
				{
					$save['open_id']=$_SESSION['user_openid'];
					$where1['member_id']=$_SESSION['member']['member_id'];
					$mem->where($where1)->save($save);
				}
			}
			if(!$mem_info['name'] ||!$mem_info['birthday'])
				{
					$_SESSION['member_id']=$mem_info['member_id'];
					Header("Location: ".U('login/register2')."&member_id=".$mem_info['member_id']."&member_info=lost"); 
				}
			if($mem_info['wanzhengdu']<65)
			{
				Header("Location: ".U('member/perfect1')."&member_info=lost"); 
			}
	
		if(empty($_SESSION['member']))
		{

			Header("Location: ".U('login/register')."&member_id=".$_REQUEST['member_id']); 
			//$this->error("你还没有登录，请先登录！",U('login/login'));
		}

		
	}
	
	
    //首页
	public function index() 
	{
		//vv($_SESSION['member']);
		$this->display();
    }
	
	//产品列表
	public function project_list()
	{
		$project=M('project');
		$project_list=$project->where()->select();
		//vv($project_list[0]);
		$where['project_status']='1';
		$this->assign("project_list",$project_list);
		$this->assign("type",$_GET['type']);
		$this->assign("status",$_GET['status']);
		$this->display();
	}
	public function ajax_project()
	{
		$project=M('project');
		$where['project_status']='1';
		if(!empty($_POST['type']))
		{

			$where['type']=$_POST['type'];	//企业类型
		}
		//all 全部  start 开始  end 结束
		
		$status=$_POST['status'];
			if($status=='all')
			{
				//$map['id']  = array('gt',100);
				//$where['start_time']=array('gt',time());
			}
			if($status=='start')
			{
				$where['start_time']=array('lt',time());
				$where['end_time']=array('gt',time());
			}
			if($status=='end')
			{
				$where['end_time']=array('lt',time());
			}
		
		$count=$project->where($where)->count();
		$page = $this->page($count, 10);
		
		$list = $project->where($where)->order('end_time desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		//vv($list);
		
		if($_GET['p']>$page->Total_Pages)
		{
			$data['status']=0;
			$data['msg']='没有更多数据了！';
			echo json_encode($data);die;
		}
		
		$html='';
		foreach($list as $k=>$v)
		{
			$html.='<li class="ui-table-view-cell">';
          
            $html.='        <i class="ui-navigate-right"></i>';
            $html.='        <div class="ui-flex">';
			
			//图片类型
			$pic=$v['type'];
			if(!empty($v['longterm']))
			{
				$pic=$v['type'].'1';
			}
			
            $html.='            <img  src="themes/simplebootx/Public/mobile/images/test'.$pic.'x.png" alt="">';

          if($v['type']=='4')
            {
             $html.='            <div class="ui-media-body ui-flex-1" style="margin-left:10px;margin-top:10px;">';
              $html.='    <a href="'.U('project/project_show',array('id'=>$v['id'])).'">';
            $html.='                <h4>';
            $html.='                    <b class="color-pink">'.$v['title'].'</b>';
            $html.='                    <span class="color-grey">'.$v['project_no'].'</span>';
            $html.='                </h4>';
				   $html.='    </a>';
			$msg=$this->project_state($v['id']);
			if($v['end_time']<time())
			{
				 $html.='                <p class="color-orange" style="color:#CCC">状态&nbsp;'.$msg.'&nbsp;&nbsp;&nbsp;&nbsp;';
			}else{
				 $html.='                <p class="color-orange">状态&nbsp;'.$msg.'&nbsp;&nbsp;&nbsp;&nbsp;';
			}
           
    		 if($v['type']=='4')
            {
            $html.=	'<a style="text-decoration:underline;color:blue;" href="'.U('project/show_longterm',array('id'=>$v['id'])).'">查看组内项目</a>';
            }
            $html.=	'</p>';
            $html.='            </div>';
        }else{
        	 $html.='    <a href="'.U('project/project_show',array('id'=>$v['id'])).'">';
        	 $html.='            <div class="ui-media-body ui-flex-1" style="margin-left:10px;margin-top:10px;">';
             
            $html.='                <h4>';
            $html.='                    <b class="color-pink">'.$v['title'].'</b>';
            $html.='                    <span class="color-grey">'.$v['project_no'].'</span>';
            $html.='                </h4>';
				   $html.='    </a>';
			$msg=$this->project_state($v['id']);
			if($v['end_time']<time())
			{
				 $html.='                <p class="color-orange" style="color:#CCC">状态&nbsp;'.$msg.'&nbsp;&nbsp;&nbsp;&nbsp;';
			}else{
				 $html.='                <p class="color-orange">状态&nbsp;'.$msg.'&nbsp;&nbsp;&nbsp;&nbsp;';
			}
   
            $html.=	'</p>';
            $html.='            </div>';
            $html.='    </a>';
        }




            $html.='        </div>';
         
            $html.='</li>';
		}
		
		$data['status']=1;
		$data['msg']=$html;
		echo json_encode($data);die;
	}


	//按照项目状态筛选
			//产品列表
	public function project_select_list()
	{
		$project=M('project');
		$status=$_GET['type'];//all 全部  start 开始  end 结束
		$project_list=$project->where()->select();
		//vv($project_list[0]);
		$where['project_status']='1';
		$this->assign("project_list",$project_list);
		$this->assign("type",$_GET['type']);

		$this->display('project_list');
	}

	
	public function project_state($id)
	{
		//$id=1;
		$project=M('project');
		$project_info=$project->where(array('id'=>$id))->find();
		//echo date("Y-m-d",$project_info['start_time']).'--------'.date("Y-m-d",$project_info['end_time']).'--------'.date("Y-m-d",time())."<br>";
		
		//echo $project_info['start_time'].'--------'.$project_info['end_time'].'--------'.time()."<br>";
		
		if($project_info['start_time']>time())
		{
			$day=ceil(($project_info['start_time']-time())/(3600*24));
			return '招募未开始'.'&nbsp;&nbsp;距离开始还剩'.$day.'天；';
		}
		
		if($project_info['start_time']<time() && $project_info['end_time']>time())
		{
			$day=($project_info['end_time']-time())/(3600*24);
			return '招募中'.'&nbsp;&nbsp;距离结束还剩'.ceil($day).'天';
		}
		
		if($project_info['end_time']<time())
		{
			return '招募结束';
		}
		
		//vv($project_info);
	}
	
	//项目详情
	public function project_show()
	{
		$project=M('project');
		$member_enrol=M('member_enrol');
		$project_info=$project->where(array('id'=>$_GET['id']))->find();
		$project_info['price_desc']=str_replace(';', ';<br>', $project_info['price_desc']);
		//判断招募是否开始
		if($project_info['start_time']>time())
		{
			$project_info['but_state']=51;
			$project_info['but_info']='招募未开始';
			/*$day=ceil(($project_info['start_time']-time())/(3600*24));
			return '招募未开始'.'&nbsp;&nbsp;距离开始还剩'.$day.'天；';*/
		}
		//招募人数到达要求就招募结束
		if($project_info['type']=='2' || ($project_info['type']=='3'&&$project_info['is_xianchagn']=='0'))
		{
			$member_enrol=M('member_enrol');
			$where_m['project_id']=$_GET['id'];
			$where_m['baoming']=1;
			$count=$member_enrol->where($where_m)->count();
			if($count>=$project_info['demand_num'] &&$project_info['demand_num']){
				$project_info['but_state']=52;
				$project_info['but_info']='招募已结束';
			}
		}
		
		if($project_info['end_time']<time())
		{
			$project_info['but_state']=52;
			$project_info['but_info']='招募已结束';
		}

		//判断项目是否报名成功
		$baoming_where['member_id']=$_SESSION['member']['member_id'];
		$baoming_where['project_id']=$project_info['id'];
		$enrol_info=$member_enrol->where($baoming_where)->find();
		if($enrol_info['baoming']==1)
		{
			$project_info['but_state']=1;
			$project_info['but_info']='报名成功';
			//echo '报名成功！';
		}elseif($enrol_info['baoming']==2)
		{
			$project_info['but_state']=2;
			$project_info['but_info']='报名失败';
			//echo '报名失败！';
		}
		//审核中
		if($enrol_info['state']=='审核中'&&$enrol_info['project_type']==1)
		{
			$project_info['but_state']=22;
			$project_info['but_info']='审核中';
		}


		if($enrol_info['state']=='报名完成')
		{
			$project_info['but_state']=3;
			$project_info['but_info']='报名完成';
		}
		if($enrol_info['state']=='报名未通过')
		{
			$project_info['but_state']=44;
			$project_info['but_info']='报名未通过';
		}
		
		if($enrol_info['state']=='筛选中')
		{
			$project_info['but_state']=8;
			$project_info['but_info']='筛选中';
		}
		
		if($enrol_info['state']=='筛选未通过')
		{
			$project_info['but_state']=4;
			$project_info['but_info']='筛选未通过';
		}
		
		if($enrol_info['state']=='项目进行中')
		{
			$project_info['but_state']=5;
			$project_info['but_info']='项目进行中';
		}
		
		if($enrol_info['state']=='参与中止')
		{
			$project_info['but_state']=6;
			$project_info['but_info']='参与中止';
		}
		
		if($enrol_info['state']=='已完成')
		{
			$project_info['but_state']=7;
			$project_info['but_info']='已完成';
		}
		
		//在线调查
		if($enrol_info['state']=='项目进行中' && $enrol_info['project_type']==2)
		{
			$project_info['but_state']=21;
			$project_info['but_info']='填写问卷';
		}
		if($enrol_info['state']=='审核中' && $enrol_info['project_type']==2)
		{
			$project_info['but_state']=22;
			$project_info['but_info']='审核中';
		}
		
		//试用品派发
		if($enrol_info['state']=='项目进行中' && $enrol_info['project_type']==3)
		{
			$project_info['but_state']=33;
			$project_info['but_info']='试用反馈';
		}
		if($enrol_info['state']=='扫码签收试用品' && $enrol_info['project_type']==3)
		{
			$project_info['but_state']=32;
			$project_info['but_info']='扫码签收试用品';
		}
		if($enrol_info['state']=='填写收货地址' && $enrol_info['project_type']==3)
		{
			$project_info['but_state']=31;
			$project_info['but_info']='填写收货地址';
		}
		if($enrol_info['state']=='审核中' && $enrol_info['project_type']==3)
		{
			$project_info['but_state']=34;
			$project_info['but_info']='审核中';
		}
		
		//长期项目组
		//试用品派发
		if($enrol_info['state']=='已加入' && $enrol_info['project_type']==4)
		{
			$project_info['but_state']=41;
			$project_info['but_info']='已加入长期项目组';
		}

		//如果一个项目属于某个项目组，则报名这个项目的人必须是在这个长期项目组的人
		if($project_info['longterm'])
		{
			//查询会员是否报名这个项目组
			$b_where['member_id']=$_SESSION['member']['member_id'];
			$b_where['project_id']=$project_info['longterm'];
			$enrol_info_l=$member_enrol->where($b_where)->find();
			if($enrol_info_l['state']=='已加入' && $enrol_info_l['baoming']=='1')
			{
				//加入之后不进行操作
			}else{
				//没有加入完成就让客户先加入长期项目组
				$project_info['but_state']=42;
				$project_info['but_info']='请先加入所属项目组';
			}
			
		}
		
		
		//格式化出生日期，身高，如果只选择了开始时间则显示为  ****之后  选择结束事件显示  ****之前，身高同理
		//格式化年龄
		if($project_info['demand_age'])
		{
			if(substr($project_info['demand_age'],0,1)==',')
			{
				if(trim($project_info['demand_age'],','))
				$project_info['demand_age']=trim($project_info['demand_age'],',').'之前出生';
			}
			elseif(substr($project_info['demand_age'],-1,1)==',')
			{
				if(trim($project_info['demand_age'],','))
				$project_info['demand_age']=trim($project_info['demand_age'],',').'之后出生';
			}
			else{
				$project_age_arr=explode(',',$project_info['demand_age']);
				if($project_age_arr)
				$project_info['demand_age']=$project_age_arr['0'].'到'.$project_age_arr['1']."出生";
			}
			
		}
		//格式化身高
		if($project_info['demand_height'])
		{
			if(substr($project_info['demand_height'],0,1)=='-')
			{
				if(trim($project_info['demand_height'],'-'))
				$project_info['demand_height']=trim($project_info['demand_height'],'-')."<b style='color:red; margin:0 0 0 5px;'>cm</b>以下";
			}
			elseif(substr($project_info['demand_height'],-1,1)=='-')
			{
				if(trim($project_info['demand_height'],'-'))
				$project_info['demand_height']=trim($project_info['demand_height'],'-')."<b style='color:red; margin:0 0 0 5px;'>cm</b>以上";
			}
			else{
				$project_height_arr=explode('-',$project_info['demand_height']);
				if($project_height_arr)
				$project_info['demand_height']=$project_height_arr['0']."<b style='color:red; margin:0 0 0 5px;'>cm</b>~".$project_height_arr['1']."<b style='color:red; margin:0 0 0 5px;'>cm</b>";
			}
			
		}
		//格式化体重
		if($project_info['demand_weight'])
		{
			if(substr($project_info['demand_weight'],0,1)=='-')
			{
				if(trim($project_info['demand_weight'],'-'))
				$project_info['demand_weight']=trim($project_info['demand_weight'],'-')."<b style='color:red; margin:0 0 0 5px;'>kg</b>以下";
			}
			elseif(substr($project_info['demand_weight'],-1,1)=='-')
			{
				if(trim($project_info['demand_weight'],'-'))
				$project_info['demand_weight']=trim($project_info['demand_weight'],'-')."<b style='color:red; margin:0 0 0 5px;'>kg</b>以上";
			}
			else{
				$project_height_arr1=explode('-',$project_info['demand_weight']);
				if($project_height_arr1)
				$project_info['demand_weight']=$project_height_arr1['0']."<b style='color:red; margin:0 0 0 5px;'>kg</b>~".$project_height_arr1['1']."<b style='color:red; margin:0 0 0 5px;'>kg</b>";
			}
			
		}

		//其他追加需求
		if($project_info['demand_other'])
		{
			$project_info['demand_other']=json_decode($project_info['demand_other'],true);

		}

		//$project_info['but_state']=3;
		//vv($enrol_info);
		//vv($project_info);
		
		//判断用户是否收藏
		$shoucang=M('shoucang');
		$shoucang_info=$shoucang->where(array('member_id'=>$_SESSION['member']['member_id'],'type'=>2,'con_id'=>$project_info['id']))->find();
		if(empty($shoucang_info))
		{
			$project_info['shoucang']=0;
		}else
		{
			$project_info['shoucang']=1;
		}
		//jssdk
		//先注释掉
		vendor('jssdk.jssdk');//导入类库
		$jssdk=new \jssdk("wx71f172895fb20911","447c05fa58111bc771f35aaf50d90f79");
		$signPackage = $jssdk->GetSignPackage();
		//vv($signPackage);
		$this->assign("signPackage",$signPackage);
		// print_r($signPackage);
		// file_put_contents('wx111.txt', $signPackage);
		 
		$this->assign("project_info",$project_info);
		$this->display();
	}
	
	//项目报名
	public function baoming_ajax()
	{
		$project_id=$_GET['project_id'];
		$project=M('project');
		$project_info=$project->where(array('id'=>$project_id))->find();	//项目信息
		$member_info=$this->member_info($_SESSION['member']['member_id']);	//用户信息
		
		//（初步筛选）筛选用户基本信息
		//性别
		if(!empty($project_info['demand_sex']))
		{
			if($project_info['demand_sex']=='男女皆可')
			{
					
			}else{
				if($project_info['demand_sex']!=$member_info['sex'])
				{
				echo '性别不符！报名失败！';die;
				}
			}
			
		}
		
		//出生年月
		if($project_info['demand_age']!=',')
		{
			$age_arr=explode(',',$project_info['demand_age']);
			if($member_info['birthday']<strtotime($age_arr[0]) || $member_info['birthday']>strtotime($age_arr[1]))
			{
				echo '年龄不符！报名失败！';die;
			}
		}
		
		//体重筛选
		if($project_info['demand_weight']&&$project_info['demand_weight']!='-')
		{
			if(substr($project_info['demand_weight'],0,1)=='-')
			{
				if(trim($project_info['demand_weight'],'-'))
				$project_info['demand_weight']=trim($project_info['demand_weight'],'-');
				if($member_info['weight']>$project_info['demand_weight'])
				{
					echo '体重不符合，报名失败';die;
				}

			}
			elseif(substr($project_info['demand_weight'],-1,1)=='-')
			{
				if(trim($project_info['demand_weight'],'-'))
				$project_info['demand_weight']=trim($project_info['demand_weight'],'-');
				if($member_info['weight']<$project_info['demand_weight'])
				{
					echo '体重不符合，报名失败';die;
				}
			}
			else{
				$project_height_arr1=explode('-',$project_info['demand_weight']);
				if($project_height_arr1)
				if($member_info['weight']<$project_height_arr1[0] || $member_info['weight']>$project_height_arr1[1])
				{
					echo '体重不符合，报名失败';die;
				}
			}
			
		}

		// if($project_info['demand_weight']!='-')
		// {
		// 	$weight_arr=explode('-',$project_info['demand_weight']);
		// 	if($member_info['weight']<$weight_arr[0] || $member_info['weight']>$weight_arr[1])
		// 	{
		// 		echo $weight_arr[1];die;
		// 	}
		// }
		
		//教育程度
		if(!empty($project_info['demand_educa']))
		{
			$educa=educa();
			$t1=array_keys($educa,$project_info['demand_educa'],true); 
			$t2=array_keys($educa,$member_info['educa'],true); 
			if($t1[0]>$t2[0])
			{
				echo '学历不够！报名失败！';die;
			}
			//vv($project_info['demand_educa']);
		}
		
		//婚姻状态
		if(!empty($project_info['demand_marriage']))
		{
			if($project_info['demand_marriage']!=$member_info['marriage'])
			{
				echo '婚姻状态不符！报名失败！';die;
			}
		}
		
		//职业
		if(!empty($project_info['demand_profession']))
		{
			if($project_info['demand_profession']!=$member_info['profession'])
			{
				echo '职业不符！报名失败！';die;
			}
		}
		
		//工作环境
		if(!empty($project_info['demand_work']))
		{
			if($project_info['demand_work']!=$member_info['work'])
			{
				echo '工作环境不符！报名失败！';die;
			}
		}
		
		//个人月收入
		if(!empty($project_info['demand_month_income']))
		{
			if($project_info['demand_month_income']!=$member_info['month_income'])
			{
				echo '个人月收入不符！报名失败！';die;
			}
		}
		
		//家庭月收入
		if(!empty($project_info['demand_family_income']))
		{
			if($project_info['demand_family_income']!=$member_info['family_income'])
			{
				echo '家庭月收入不符！报名失败！';die;
			}
		}
		
		
		//vv($member_info);
		//vv($project_info);
		echo '报名成功！';
		//vv();
	}
	
	
	//产品列表
	public function project_member()
	{
		$project=M('project');
		//$project_list=$project->where()->select();
		
		$member_enrol=M('member_enrol');
		if(!empty($_GET['type']))
		{
			$where['m.project_type']=$_GET['type'];	//企业类型
		}
		
		if(!empty($_GET['enrol_state']))
		{
			$state=$_GET['enrol_state'];
			if($_GET['enrol_state']=='ing')
			{
				$state='项目进行中';
			}
			$where['m.state']=$state;	//企业类型
		}else{
			$state='报名完成';
			$where['m.state']=$state;
		}
		
		$where['member_id']=$_SESSION['member']['member_id'];
		
		//$count=$member_enrol->where($where)->count();
		$count = M('member_enrol')->alias('m')->join('v_project as p ON m.project_id =p.id')->where($where)->count();
		
		$page = $this->page($count, 10);
		
		$list = M('member_enrol')->field('p.*,m.state as enrol_state')->alias('m')->join('v_project as p ON m.project_id =p.id')->where($where)->order('enrol_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		

		
		$html='';
		foreach($list as $k=>$v)
		{
			$html.='<li class="ui-table-view-cell">';
            $html.='    <a href="'.U('project/project_show',array('id'=>$v['id'])).'">';
            $html.='        <i class="ui-navigate-right"></i>';
            $html.='        <div class="ui-flex">';
			
			//图片类型
			$pic=$v['type'];
			if(!empty($v['longterm']))
			{
				$pic=$v['type'].'1';
			}
			
            $html.='            <img src="/themes/simplebootx/Public/mobile/images/test'.$pic.'x.png" alt="">';
            $html.='            <div class="ui-media-body ui-flex-1">';
            $html.='                <h4>';
            $html.='                    <b class="color-pink">'.$v['title'].'</b>';
            $html.='                    <span class="color-grey">'.date("Y-m-d",$v['add_time']).'</span>';
            $html.='                </h4>';
			
			//$msg=$this->project_state($v['id']);
			
            $html.='                <p class="color-orange">状态&nbsp;'.$v['enrol_state'].'</p>';
            $html.='            </div>';
            $html.='        </div>';
            $html.='    </a>';
            $html.='</li>';
		}
		
		// $data['status']=1;
		// $data['msg']=$html;
		// echo json_encode($data);die;

		$this->assign("html",$html);
		$this->assign("type",$_GET['type']);
		$this->assign("state",$_GET['state']);
		$this->display();
	}
	public function ajax_member()
	{
		$member_enrol=M('member_enrol');
		if(!empty($_POST['type']))
		{
			$where['m.project_type']=$_POST['type'];	//企业类型
		}
		
		if(!empty($_POST['state']))
		{
			$state=$_POST['state'];
			if($_POST['state']=='ing')
			{
				$state='项目进行中';
			}
			$where['m.state']=$state;	//企业类型
		}
		
		$where['member_id']=$_SESSION['member']['member_id'];
		
		//$count=$member_enrol->where($where)->count();
		$count = M('member_enrol')->alias('m')->join('v_project as p ON m.project_id =p.id')->where($where)->count();
		
		$page = $this->page($count, 10);
		
		//$join = C('DB_PREFIX').'project as b on a.project_id =b.id';
		//$users = $member_enrol->field('a.*,b.name,b.sex,b.birthday,b.mobile,b.area_name,b.member_id')->alias("a")->join($join)->where($where)->order('a.enrol_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		
		$list = M('member_enrol')->field('p.*,m.state as enrol_state')->alias('m')->join('v_project as p ON m.project_id =p.id')->where($where)->order('enrol_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		//vv($list);
		//die;
		if($_GET['p']>$page->Total_Pages)
		{
			$data['status']=0;
			$data['msg']='没有更多数据了！';
			echo json_encode($data);die;
		}
		
		$html='';
		foreach($list as $k=>$v)
		{
			$html.='<li class="ui-table-view-cell">';
          
            $html.='        <i class="ui-navigate-right"></i>';
            $html.='        <div class="ui-flex">';
			
			//图片类型
			$pic=$v['type'];
			if(!empty($v['longterm']))
			{
				$pic=$v['type'].'1';
			}
			
            $html.='            <img  src="themes/simplebootx/Public/mobile/images/test'.$pic.'x.png" alt="">';

          if($v['type']=='4')
            {
             $html.='            <div class="ui-media-body ui-flex-1" style="margin-left:10px;margin-top:10px;">';
              $html.='    <a href="'.U('project/project_show',array('id'=>$v['id'])).'">';
            $html.='                <h4>';
            $html.='                    <b class="color-pink">'.$v['title'].'</b>';
            $html.='                    <span class="color-grey">'.$v['project_no'].'</span>';
            $html.='                </h4>';
				   $html.='    </a>';
			$msg=$this->project_state($v['id']);
			if($v['end_time']<time())
			{
				 $html.='                <p class="color-orange" style="color:#CCC">状态&nbsp;'.$msg.'&nbsp;&nbsp;&nbsp;&nbsp;';
			}else{
				 $html.='                <p class="color-orange">状态&nbsp;'.$msg.'&nbsp;&nbsp;&nbsp;&nbsp;';
			}
           
    		 if($v['type']=='4')
            {
            $html.=	'<a style="text-decoration:underline;color:blue;" href="'.U('project/show_longterm',array('id'=>$v['id'])).'">查看组内项目</a>';
            }
            $html.=	'</p>';
            $html.='            </div>';
        }else{
        	 $html.='    <a href="'.U('project/project_show',array('id'=>$v['id'])).'">';
        	 $html.='            <div class="ui-media-body ui-flex-1" style="margin-left:10px;margin-top:10px;">';
             
            $html.='                <h4>';
            $html.='                    <b class="color-pink">'.$v['title'].'</b>';
            $html.='                    <span class="color-grey">'.$v['project_no'].'</span>';
            $html.='                </h4>';
				   $html.='    </a>';
			// $msg=$this->project_state($v['id']);
			// if($v['end_time']<time())
			// {
			// 	 $html.='                <p class="color-orange" style="color:#CCC">状态&nbsp;'.$msg.'&nbsp;&nbsp;&nbsp;&nbsp;';
			// }else{
				 $html.='                <p class="color-orange">状态&nbsp;'.$v['enrol_state'].'&nbsp;&nbsp;&nbsp;&nbsp;';
			// }
   
            $html.=	'</p>';
            $html.='            </div>';
            $html.='    </a>';
        }




            $html.='        </div>';
         
            $html.='</li>';
		}
		
		$data['status']=1;
		$data['msg']=$html;
		echo json_encode($data);die;
	}
	
	//签到
	public function project_qiandao()
	{
		$project=M('project');
		$project_info=$project->where(array('id'=>$_GET['project_id']))->find();
		$member_enrol=M('member_enrol');
		$enrol_info=$member_enrol->where(array('project_id'=>$_GET['project_id'],'member_id'=>$_SESSION['member']['member_id']))->find();
		//扫描的二维码是否正确
		if($enrol_info['state']=='报名通过'&&$enrol_info['baoming']=='1')
		{
			//只有状态是报名通过的才进行下一步
			if($project_info['type']==1)
			{
				$member_enrol->where(array('enrol_id'=>$enrol_info['enrol_id']))->save(array('state'=>'筛选中'));
				$data['title']=$project_info['title'];
				$data['msg']='签到扫描完成！请等待筛选！';
				$data['url']=U('project/project_show',array('id'=>$_GET['project_id']));
			}
		// elseif($project_info['type']==3)
		// {
		// 	$member_enrol->where(array('enrol_id'=>$enrol_info['enrol_id']))->save(array('state'=>'试用反馈'));
		// 	$data['title']=$project_info['title'];
		// 	$data['msg']='扫码签收试用品完成，请进行试用产品！';
		// 	$data['url']=U('project/project_show',array('id'=>$_GET['project_id']));
		// }
			 if($project_info['type']==3)
			{
				$member_enrol->where(array('enrol_id'=>$enrol_info['enrol_id']))->save(array('state'=>'筛选中'));
				$data['title']=$project_info['title'];
				$data['msg']='签到扫描完成！请等待筛选！';
				$data['url']=U('project/project_show',array('id'=>$_GET['project_id']));
			}
			 if($project_info['type']==4)
			{
				$member_enrol->where(array('enrol_id'=>$enrol_info['enrol_id']))->save(array('state'=>'筛选中'));
				$data['title']=$project_info['title'];
				$data['msg']='签到扫描完成！请等待筛选！';
				$data['url']=U('project/project_show',array('id'=>$_GET['project_id']));
			}
		}else{
				$data['title']=$project_info['title'];
				$data['msg']='请扫描正确的二维码（初筛签到）！';
				$data['url']=U('project/project_show',array('id'=>$_GET['project_id']));
		}
		

		//vv($data);
		
		$this->assign("data",$data);
		$this->display();
		
	}
	
	//收货
	public function shouhuo()
	{
		echo '收货地址';
	}

	//查看组内项目
	public function show_longterm()
	{
		//先查询该客户是否属于该项目组 不属于就不显示
		$member_id=$_SESSION['member']['member_id'];
		$project_id=$_GET['id'];
		$member_enrol=M('member_enrol');
		$project=M('project');
		$where_e['member_id']=$member_id;
		$where_e['project_id']=$project_id;
		$where_e['baoming']='1';
		$where_e['state']='已加入';
		$enrol_info=$member_enrol->where($where_e)->find();
		// print_r($enrol_info);
		if($enrol_info)
		{
		$where['project_status']='1';
		$where['longterm']=$project_id;
		
		$project_list=$project->where($where)->select();
		
		foreach($project_list as $k=>$v)
			{
				$project_list[$k]['state']='状态&nbsp'.$this->project_state($v['id']);
				$pic=$v['type'];
				$pic=$v['type'].'1';
				$project_list[$k]['imgsrc']=$pic;
			}
		}else{
		$where['project_status']='1';
		$where['longterm']=$project_id;
		$where['is_zw']='1';
		$project_list=$project->where($where)->select();
		
		foreach($project_list as $k=>$v)
			{
				$project_list[$k]['state']='状态&nbsp'.$this->project_state($v['id']);
				$pic=$v['type'];
				$pic=$v['type'].'1';
				$project_list[$k]['imgsrc']=$pic;
			}
		}
		//vv($project_list[0]);
		$this->assign("project_lists",$project_list);
		$this->assign("type",$_GET['type']);
		$this->display();

	}

	//产品及用法介绍
	public function show_project_desc()
	{
		//product_desc
		$project_id=$_GET['id'];
		$project=M('project');
		$project_info=$project->field('product_desc')->where("id=$project_id")->find();
		$product_desc=htmlspecialchars_decode($project_info['product_desc']);
		$this->assign("product_desc",$product_desc);
		$this->display();

	}

}


