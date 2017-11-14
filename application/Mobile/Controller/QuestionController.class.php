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

			 ________     _		  __
			|______	 |	 | |     / / 
				   | |	 | |    / /   
			 ______| |   | |   / /    
			|  ______|	 | |  / /    
			| |      ____| |_/ /_____	
			| |  	|________  ______| 	
			| |______	 | | \ \		  
			|______	 |	 | |  \ \    
				   | |	 | |   \ \   
			    _  | | 	 | |  _ \ \   
			    \ \| |	 | | / / \ \  
				 \ | |	 | |/ /   \ \  
				  \__|	 |_ _/	   \_\	 
				 
				  
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
class QuestionController extends MobilebaseController {
	
	function _initialize() {
		parent::_initialize();
		if(empty($_SESSION['member']))
		{
			Header("Location: ".U('login/login')); 
			//$this->error("你还没有登录，请先登录！",U('login/login'));
		}
		//$this->assign("active",'member');
		/*if(empty($_SESSION['member']))
		{
			Header("Location: ".U('login/register')); 
			//$this->error("你还没有登录，请先登录！",U('login/login'));
		}else
		{
			$member_info=$this->member_info($_SESSION['member']['member_id']);
			//vv($member_info);
			$this->assign("member_info",$member_info);
		}*/
		
	}
	
	
    //首页
	public function index() 
	{
		//vv($_SESSION['member']);
		$this->display();
    }
	
	//问题表
	public function ques()
	{
		$ques=M('ques');
		$project_id=$_GET['project_id'];
		$sort=$_GET['sort'];
		$turn_to=$_GET['turn_to'];
		$where['project_id']=$project_id;
		$where['type']=1;
		if($turn_to)
		$where['ques_id']=$turn_to;
		//$where['sort']=$sort;
		 if($turn_to > 0)
		 {
		 $ques_list=$ques->where($where)->select();
		 }else{
			$ques_list=$ques->where($where)->limit($sort-1,1)->select();
		}
		
		$ques_info=$ques_list[0];
		//vv($ques_info);
		if(!empty($ques_info))
		{
			$zimu=array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t');
			foreach($zimu as $k=>$v)
			{
				if(!empty($ques_info[$v]))
				{
					$ques_info['answer'][$v]=/*strtoupper($v).":".*/$ques_info[$v];
				}else
				{
					break;
				}
			}
			$new_daan=$this->member_daan($ques_info['project_id'],$ques_info['ques_id']);
			$ques_info['new_daan']=explode("|",$new_daan);
			//vv($new_daan);
			$this->assign("ques_info",$ques_info);
			// print_r($ques_info);
			$this->display();
		}else
		{
			//如果没有试题 跳到显示所有试题页面
			$member_ques=M('member_ques');
			$where_mq['member_id']=$_SESSION['member']['member_id'];
			$where_mq['project_id']=$project_id;
			$member_ques_info=$member_ques->where($where_mq)->find();
			$paper_desc=json_decode($member_ques_info['paper_desc'],true);
			//vv($paper_desc);
			// print_r($paper_desc);
			$where2['project_id']=$project_id;
			$where2['type']=1;
			$ques_list=$ques->where($where2)->select();
			foreach($ques_list as $k=>$v)
			{
				$ques_arr[$k]['title']=$v['name'];
				$zimu=$paper_desc[$v['ques_id']]['ques_daan'];
				//vv($zimu);
				$new_zimu=explode("|",$zimu);
				$html='';
				foreach($new_zimu as $kk=>$vv)
				{
					$html.=strtoupper($vv)."：".$v[$vv]."<br>";
				}
				$ques_arr[$k]['daan']=$html;

			}
			// print_r($ques_list);
			//判断项目类型
			$project=M('project');
			$project_ifno=$project->where(array('id'=>$project_id))->find();
			if($project_ifno['type']==2)
			{
				$msg_info['title']='项目报名';
				$msg_info['content']='接下来请尽快完成调查问卷！';
				$msg_info['url']="index.php?g=&m=Process&a=diaocha&project_id=".$project_id."&type=5&sort=1";
				
			}elseif($project_ifno['type']==3 && $project_ifno['is_xianchagn']==0)
			{
				$msg_info['title']='项目报名';
				$msg_info['content']='请选择或填写您的收货地址！';
				$msg_info['url']="index.php?g=&m=member&a=queren&project_id=$project_id&type=1";
			}elseif($project_ifno['type']==3 && $project_ifno['is_xianchagn']==1)
			{
				$msg_info['title']='项目报名';
				$msg_info['content']='我们会在报名截止之后已经项目启动前，安排工作人员与您电话联系，请耐心等待。！';
				$msg_info['url']=U('project/project_show',array('id'=>$project_id));
			}else
			{
				$msg_info['title']='项目报名';
				$msg_info['content']='我们会在报名截止后以及项目启动前，安排工作人员与您电话联系，请耐心等待。';
				$msg_info['url']=U('project/project_show',array('id'=>$project_id));
				
			}
			
			$this->assign("msg_info",$msg_info);
			$this->assign("ques_info",$ques_list[0]);
			$this->assign("ques_arr",$ques_arr);
			$this->display('ques_show');
			//echo '123';
		}
		
		
	}
	//获取用户选的答案
	public function member_daan($project_id,$ques_id)
	{
		$member_ques=M('member_ques');
		$where_mq['member_id']=$_SESSION['member']['member_id'];
		$where_mq['project_id']=$project_id;
		$member_ques_info=$member_ques->where($where_mq)->find();
		if(empty($member_ques_info))
		{
			return '';
		}else
		{
			$paper_desc=json_decode($member_ques_info['paper_desc'],true);
			//vv($paper_desc[$ques_id]);
			return $paper_desc[$ques_id]['ques_daan'];
			
		}
		//vv($member_ques_info);
		//vv();
	}
	
	public function ques_ajax()
	{
		$ques=M('ques');
		$project=M('project');
		$member_ques=M('member_ques');
		
		if (IS_POST) 
		{
			
			$ques_info=$ques->where(array('ques_id'=>$_POST['ques_id']))->find();
			$project_info=$project->where(array('id'=>$ques_info['project_id']))->find();
			//判断问题是否回答正确
			$ques_isok=$this->ques_isok($ques_info['ques_id'],$_POST['ques']);
			//die;
			$array['ques_id']=$ques_info['ques_id'];	//问题id
			$array['ques_class']=$ques_info['class'];	//问题分类
			$array['ques_daan']=implode("|",$_POST['ques']);			//问题答案
			$array['ques_ok']=$ques_isok;	 					//是否回答正确 0.未正确 1.回答正确
			
			$new_array[$array['ques_id']]=$array;
			$where_mq['member_id']=$_SESSION['member']['member_id'];
			$where_mq['project_id']=$project_info['id'];
			$member_ques_info=$member_ques->where($where_mq)->find();
			//逻辑跳转的where条件
			$where['project_id']=$ques_info['project_id'];
			$where['type']=1;
			//逻辑跳转
			$turn_to=0;
			//如果没有跳转逻辑则返回下一题的ques_id
		
			if($ques_info['turn_to'])
			{
				$ques_info['turn_to']=unserialize($ques_info['turn_to']);	

			}
			else{
			//如果没有跳转逻辑则返回下一题的ques_id			
				//$sort=$ques_info['sort'];
				$ques_list=$ques->where($where)->select();
				foreach ($ques_list as $key => $value) {
					if($value['ques_id']==$ques_info['ques_id'])
					{
						$ques_info_next[]=$ques_list[$key+1];
					}
				}
				//当ques_id < 下一个ques_id时，返回下一题 否则返回字符
				//file_put_contents('444.txt',var_export($ques_info_next[0],true));
				if($ques_info['ques_id'] < $ques_info_next[0]['ques_id'])
				{
				   $turn_to=$ques_info_next[0]['ques_id'];
				}else{
					//返回字符串 供判断回答是否结束
					$turn_to='thisall';
				}

			}
			
			if($ques_isok&&$ques_info['turn_to']&&$ques_info['class']=='单选')
			{
				$turn_to=$ques_info['turn_to'][$_POST[ques][0]];
			}
			
			//file_put_contents('111.txt',var_export($turn_to,true));
			if(empty($member_ques_info))// || empty($member_ques_info['paper_desc']))
			{
				
				$data['member_id']=$_SESSION['member']['member_id'];
				$data['paper_type']=1;
				$data['paper_desc']=json_encode($new_array);
				$data['project_id']=$project_info['id'];
				$data['project_name']=$project_info['title'];
				$data['project_type']=$project_info['type'];
				$data['add_time']=time();
				$member_ques->add($data);
				$result['msg']='操作成功！';
				$result['turn_to']=$turn_to;
				die(json_encode($result));
				// echo '操作成功！';
			}else
			{
				//vv(json_decode($member_ques_info['paper_desc'],true));die;
				$new_paper_desc=$new_array+json_decode($member_ques_info['paper_desc'],true);
				//vv($new_paper_desc);
				//die;
				$member_ques->where(array('member_ques_id'=>$member_ques_info['member_ques_id']))->save(array('paper_desc'=>json_encode($new_paper_desc)));
				$result['msg']='操作成功！';
				$result['turn_to']=$turn_to;
				// echo '操作成功！';
				die(json_encode($result));
				
			}
			//die;
		}
		
	}
	
	public function quesok_ajax()
	{
		$member_ques=M('member_ques');
		$member_enrol=M('member_enrol');
		
		if (IS_POST) 
		{
			$where_mq['member_id']=$_SESSION['member']['member_id'];
			$where_mq['project_id']=$_GET['project_id'];
			$member_ques_info=$member_ques->where($where_mq)->find();
			$paper_desc=json_decode($member_ques_info['paper_desc'],true);
			$status=1;
			foreach($paper_desc as $k=>$v)
			{
				if($v['ques_ok']!=1)
				{
					$status=2;
					break;
				}
			}
			$ques_type=$_GET['ques_type'];
			$member_ques->where(array('member_ques_id'=>$member_ques_info['member_ques_id']))->save(array('status'=>$status,'type'=>$ques_type));
			
			
			
			$data['member_id']=$_SESSION['member']['member_id'];;			//用户id
			$data['member_name']=$_SESSION['member']['name'];;			//用户姓名
			$data['ques_id']=$member_ques_info['member_ques_id'];				//用户答卷id
			$data['project_id']=$member_ques_info['project_id'];			//项目id
			$data['project_no']=$member_ques_info['member_ques_id'];			//项目编号
			$data['project_name']=$member_ques_info['project_name'];			//项目名
			$data['project_type']=$member_ques_info['project_type'];			//项目分类
			$data['add_time']=time();				//添加时间

			$member_enrol_info=$member_enrol->where($where_mq)->find();
			if($member_enrol_info)
			{
				echo '报名重复';die;
			}
			if($status==1 && !empty($member_ques_info))
			{
				$data['baoming']=1;		//报名状态 1.成功  2.失败
				
				//判断项目类型
				$project=M('project');
				$project_ifno=$project->where(array('id'=>$member_ques_info['project_id']))->find();
				
				if($project_ifno['type']==2)
				{
					$data['state']='项目进行中';
				}elseif($project_ifno['type']==3 && $project_ifno['is_xianchagn']==0)
				{
					$data['state']='填写收货地址';
					
				}elseif($project_ifno['type']==3 && $project_ifno['is_xianchagn']==1)
				{
					$data['state']='报名完成';
				}
				else
				{
					$data['state']='报名完成';
				}
				
				
				$config=M('config');
				$config_info=$config->field('baoming_jifen')->where('config_id=1')->find();
				$jifen=M('jifen_log');
				$jifen_data['member_id']=$_SESSION['member']['member_id'];
				$jifen_data['member_name']=$_SESSION['member']['name'];
				$jifen_data['jifen']=$config_info['baoming_jifen'];
				$jifen_data['add_time']=time();
				$jifen_data['desc']='报名完成'.$config_info['baoming_jifen'].'积分';
				$jifen_data['stage']='报名完成';
				$jifen_data['type']=3;
				$jifen->add($jifen_data);
				$this->setjifen($jifen_data['jifen'],$_SESSION['member']['member_id']);
				
				$member_enrol->add($data);
				
				echo '报名成功';
			}else
			{
				$data['state']='报名未通过';
				$data['baoming']=2;		//报名状态 1.成功  2.失败
				if(!empty($member_ques_info))
				{
					$member_enrol->add($data);
				}
				
				echo '报名失败';
			}
			//vv($data);
		}
		
	}
	
	//判断是否回答正确
	public function ques_isok($ques_id,$daan)
	{
		$ques=M('ques');
		$ques_info=$ques->where(array('ques_id'=>$ques_id))->find();
		if($ques_info['class']=='单选')
		{
				$daan_list=explode('|',$ques_info['daan']);
			if(in_array(strtoupper($daan[0]),$daan_list))
			{
				return 1;
			}else
			{
				return 0;
			}
		}
		
		if($ques_info['class']=='多选')
		{
			foreach($daan as $k=>$v)
			{
				if(strstr($ques_info['daan'],strtoupper($v)))
				{
					return 1;
				}
				elseif(strstr($ques_info['daan'],strtoupper($v)))
				{
					return 1;
				}
				else
				{
					return 0;
				}
			}
		}
		if($ques_info['class']=='多选-选一')
		{
			foreach($daan as $k=>$v)
			{
				if(strstr($ques_info['daan'],strtoupper($v)))
				{
					return 1;
				}
				
				else
				{
					return 0;
				}
			}
		}

		
		if($ques_info['class']=='多选-包含')
		{
			foreach($daan as $k=>$v)
			{
				if(strstr($ques_info['daan'],strtoupper($v)))
				{
					
				}else
				{
					return 0;die;
				}
			}
			return 1;
		}
		
		if($ques_info['class']=='多选-全选')
		{
			//vv($daan);
			$ques_arr=explode('|',$ques_info['daan']);
			//vv($ques_arr);
			foreach($ques_arr as $k=>$v)
			{
				//vv(strtolower($v));
				if(!in_array(strtolower($v),$daan))
				{
					return 0;die;
				}
			}
			return 1;
		}
		//vv($ques_info);
	}


}


