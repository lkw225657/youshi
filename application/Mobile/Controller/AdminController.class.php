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
 * 项目过程
 */
class AdminController extends MobilebaseController {
	
	function _initialize() {
		parent::_initialize();
		// if(empty($_SESSION['member']))
		// {
		// 	Header("Location: ".U('login/login')); 
		// 	//$this->error("你还没有登录，请先登录！",U('login/login'));
		// }
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
	public function admin() 
	{
		
		$project=M('project');
		$project_info=$project->where(array('id'=>$_GET['project_id']))->find();
		$this->assign("project_info",$project_info);
		//vv($project_info);
		$process_airradio=M('process_airradio');
		$airradio_info=$process_airradio->where(array('project_id'=>$_GET['project_id'],'is_show'=>'1'))->find();
			$this->assign("airradio_info",$airradio_info);
		$member_process=M('member_process');
		$mp_where['member_id']=$_GET['member_id'];
		$mp_where['project_id']=$_GET['project_id'];
		$mp_info=$member_process->where($mp_where)->find();
		
		if(empty($mp_info))
		{
			$sub_num=1;
		}else
		{
			$sub_num=$mp_info['sub_num']+1;
		}
		
		$new_mp=json_decode($mp_info['process_desc'],true);
		//vv($new_mp);
		$project_process=M('project_process');
		$process_list=$project_process->where(array('project_id'=>$_GET['project_id']))->select();
		$sup_num=1;
		foreach($process_list as $k=>$v)
		{
			$chongzuo_arr=explode(",",$mp_info['chongzuo_id']);
			
			if(in_array($v['process_id'],$chongzuo_arr))
			{
				$v['is_chongzuo']=1;
			}else
			{
				$v['is_chongzuo']=0;
			}
			
			if(!empty($new_mp[$v['process_id']]))
			{
				$v['process_desc']=$new_mp[$v['process_id']]['mp_desc'];
				
				//项目已完成
				if($v['name']=='照片上传')
				{
					$v['href_url']='<a href="javascript:" onClick="show_photo('.$_GET['member_id'].','.$v['process_id'].','.$_GET['project_id'].')" target="_blank">查看照片</a> &nbsp;&nbsp;&nbsp;<button id="cut_off" style="display:none" onClick="cutoff()">关闭</button>  ';
				}elseif($v['name']=='视频上传')
				{
					$v['href_url']='<a href="javascript:" onClick="show_video('.$_GET['member_id'].','.$v['process_id'].','.$_GET['project_id'].')" target="_blank">查看视频</a> &nbsp;&nbsp;&nbsp;<button id="cut_off_f" style="display:none" onClick="cutoff_f()">关闭</button>  ';
				}elseif($v['name']=='问卷反馈')
				{
					$v['href_url']='<a href="index.php?g=Admin&m=project&a=zhenbie_show&member_id='.$_GET['member_id'].'&project_id='.$_GET['project_id'].'&type=4" target="_blank">查看问卷</a>';
				}elseif($v['name']=='获取环境数据')
				{
					$v['href_url']="完成";
						$desc="温度：&nbsp;".$v['process_desc']['temperature']."℃<br>湿度：&nbsp;".$v['process_desc']['humility']."%rh<br>pm2.5:&nbsp;".$v['process_desc']['pm25']."μg/m3<br>pm10:&nbsp;".$v['process_desc']['pm10'].'μg/m3';
					
					$v['desc']=$desc;
				}
				else{
					$v['href_url']="完成";
				}
			}else
			{
				$v['href_url']="未完成";
			}
			
			if($sup_num==$sub_num)
			{
				$new_info=$v;
			}
			$new_list[$v['start_date']][]=$v;
			$sup_num++;
		}
		
		//vv($new_list);
		$this->assign("member_id",$_GET['member_id']);
		$this->assign("project_id",$_GET['project_id']);
		$this->assign("sub_num",$sub_num);
		$this->assign("mp_info",$mp_info);
		$this->assign("new_info",$new_info);
		$this->assign("new_list",$new_list);
		$this->display();
    }
	
	//设置项目重做
	public function chongzuo_ajax()
	{
		$process_id=$_GET['process_id'];
		$member_process=M('member_process');
		$mp_where['mp_id']=$_GET['mp_id'];
		$mp_info=$member_process->where($mp_where)->find();
		$chongzuo_arr=explode(",",$mp_info['chongzuo_id']);
		//$chongzuo_arr[]=$process_id;
		if(!in_array($process_id,$chongzuo_arr))
		{
			$chongzuo_arr[]=$process_id;
		}
		$is_ok=$member_process->where($mp_where)->save(array('chongzuo_id'=>implode(",",$chongzuo_arr)));
		if($is_ok)
		{
			echo '修改成功';
		}else
		{
			echo '修改失败';
		}
		//vv($chongzuo_arr);
	}
	public function wancheng_ajax()
	{
		$process_id=$_GET['process_id'];
		$member_id=$_GET['member_id'];
		$project_id=$_GET['project_id'];
		$project=M('project');
		$member_process=M('member_process');
		$mp_where['mp_id']=$_GET['mp_id'];
		$mp_info=$member_process->where($mp_where)->find();
		$process_desc[$_GET['process_id']]['mp_id']=$_GET['process_id'];
		$process_desc[$_GET['process_id']]['mp_desc']='现场测试完成';
		$process_desc[$_GET['process_id']]['mp_time']=time();
		
		if(empty($mp_info))
		{

		$project_info=$project->where(array('id'=>$project_id))->find();
		$data['member_id']=$_GET['member_id'];
		$data['project_id']=$project_info['id'];
		$data['project_name']=$project_info['title'];
		$data['project_type']=$project_info['type'];
		$data['add_time']=time();
		$data['sub_num']=1;
		$data['process_desc']=json_encode($process_desc);
		$isok=$member_process->add($data);
			
		}else{
		$new_desc=$process_desc+json_decode($mp_info['process_desc'],true);
		$isok=$member_process->where($mp_where)->save(array('process_desc'=>json_encode($new_desc),'sub_num'=>count($new_desc)));	//修改信息
		}
		//新的描述

		//$chongzuo_arr[]=$process_id;
		
		if($isok)
		{
			echo '修改成功';
		}else
		{
			echo '修改失败';
		}
		//vv($chongzuo_arr);
	}

		//审核
	public function check_ajax()
	{
		$process_id=$_GET['process_id'];
		$member_id=$_GET['member_id'];
		$project_id=$_GET['project_id'];
		$project=M('project');
		$project_process=M('project_process');
		$member_process=M('member_process');
		$member_enrol=M('member_enrol');
		$mp_where['mp_id']=$_GET['mp_id'];
		$mp_info=$member_process->where($mp_where)->find();
		if($mp_info)
		{
			$mp_info_array=json_decode($mp_info['process_desc'],true);
		}
		if(empty($mp_info)||empty($mp_info_array[$process_id]))
		{
			//没有完成的时候不能审核
			echo '用户还没有完成该流程,暂时不能审核!';
			
		}else{

		$mp_info_array[$process_id]['is_check']=1;
		$new_desc=$mp_info_array;


		$isok=$member_process->where($mp_where)->save(array('process_desc'=>json_encode($new_desc),'sub_num'=>count($new_desc)));	//修改信息
		//判断是否都完成
		$i=0;
		foreach($new_desc as $k=>$v)
		{
			if($v['is_check'])
				$i+=1;
		}

		$count=$project_process->where("project_id=$project_id")->count();
		if($i==$count)
		{
			//数量相等就是全部完成
			//修改项目状态为已完成
			//是否更改项目状态为已完成，条件(有已完成 但是没有项目进项中)
			$save['state']='已完成';
			$where_m['project_id']=$project_id;
			$where_m['member_id']=$member_id;
			$isok=$member_enrol->where($where_m)->save($save);
			//修改了报名状态后 修改项目状态
			//进行中
			$where_ing['project_id']=$project_id;
			$where_ing['state']='项目进行中';
			$where_done['project_id']=$project_id;
			$where_done['state']='已完成';
			$count_ing=$member_enrol->where($where_ing)->count();
			$count_done=$member_enrol->where($where_done)->count();
			if($count_done&&!$count_ing)
			{
				$save_project['state']='项目完成';
				//完成时间
				$save_project['project_end_time']=time();
				$project->where("id=$project_id")->save($save_project);

			}
		}

		
		if($isok)
		{
			echo '修改成功';
		}else
		{
			echo '修改失败';
		}

	}

}

	public function show_photos()
	{
		$member_id=$_REQUEST['member_id'];
		$process_id=$_REQUEST['process_id'];
		$project_id=$_REQUEST['project_id'];
		$where['member_id']=$member_id;
		//$where['process_id']=$process_id;
		$where['project_id']=$project_id;
		$member_process=M('member_process');
		$process_info=$member_process->where($where)->find();
		$process_desc=json_decode($process_info['process_desc'],true);
	
		$photos=$process_desc[$process_id]['mp_desc'];
	
		die(json_encode($photos));
	}
	public function show_videos()
	{
		$member_id=$_REQUEST['member_id'];
		$process_id=$_REQUEST['process_id'];
		$project_id=$_REQUEST['project_id'];
		$where['member_id']=$member_id;
		//$where['process_id']=$process_id;
		$where['project_id']=$project_id;
		$member_process=M('member_process');
		$process_info=$member_process->where($where)->find();
		$process_desc=json_decode($process_info['process_desc'],true);
		$video=$process_desc[$process_id]['mp_desc'];


		die(json_encode($video));
	}


}


