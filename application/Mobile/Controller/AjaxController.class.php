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
class AjaxController extends MobilebaseController {
	
	function _initialize() {
		parent::_initialize();
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
	
	public function shoucang()
	{
		$shoucang=M('shoucang');
		$project=M('project');
		$shoucang_info=$shoucang->where(array('member_id'=>$_SESSION['member']['member_id'],'type'=>$_GET['type'],'con_id'=>$_GET['project_id']))->find();
		
		if(empty($shoucang_info))
		{
			//收藏项目
			if($_GET['type']==2)
			{
				$project_info=$project->where(array('id'=>$_GET['project_id']))->find();
				
				$data['type']=$_GET['type'];
				$data['member_id']=$_SESSION['member']['member_id'];
				$data['project_type']=$project_info['type'];
				$data['con_id']=$project_info['id'];
				$data['title']=$project_info['title'];
				$data['add_time']=time();
				$is_ok=$shoucang->add($data);
				if($is_ok)
				{
					echo '收藏成功';die;
				}else
				{
					echo '收藏失败';die;
				}
			}
		}else
		{
			echo '您已经收藏过了！';
		}

	}
	
	//删除收藏
	public function del_shoucang()
	{
		$shoucang=M('shoucang');
		$is_ok=$shoucang->where(array('shoucang_id'=>$_GET['id']))->delete();
		if($is_ok)
		{
			echo '删除成功';die;
		}else
		{
			echo '删除失败';die;
		}
	}


}


