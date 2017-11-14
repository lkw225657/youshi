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
class ArticleController extends MobilebaseController {
	
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
	
	//产品列表
	public function article_list()
	{
		/*$project=M('project');
		$project_list=$project->where()->select();
		//vv($project_list[0]);
		$this->assign("project_list",$project_list);*/
		$this->assign("type",$_GET['type']);
		$this->display();
	}
	public function ajax_article()
	{
		$article=M('article');
		if(!empty($_POST['type']))
		{
			$where['cat_id']=$_POST['type'];	//企业类型
		}
		
		$count=$article->where($where)->count();
		$page = $this->page($count, 6);
		
		$list = $article->where($where)->order('article_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
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
			$html.='<li>';
                $html.='<a href="'.U('article/article_show',array('article_id'=>$v['article_id'])).'">';
                    $html.=$v['title'];
                $html.='</a>';
            $html.='</li>';
		}
		
		$data['status']=1;
		$data['msg']=$html;
		echo json_encode($data);die;
	}
	
	public function article_show()
	{
		
		$article=M('article');
		$article_info=$article->where(array('article_id'=>$_GET['article_id']))->find();
		//vv($article_info);
		if(empty($article_info))
		{
			echo '没有数据！';
		}else
		{
			$this->assign("article_info",$article_info);
			$this->display();
		}
		
		
	}


}


