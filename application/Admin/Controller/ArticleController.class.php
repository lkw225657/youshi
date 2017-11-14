<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class ArticleController extends AdminbaseController{
	
	function _initialize() {
		parent::_initialize();
	}
	
	function index()
	{
		echo '111';
	}
	
	function Article_list()
	{
		$model=M('article');
		$cat=M('article_cat');
		$cat_list=$cat->where()->order('sort_order asc')->select();
		//vv($cat_list[0]);
		$this->assign("cat_list",$cat_list);
		//分组keyword
		if(!empty($_REQUEST['cat_id']))
		{
			$where['cat_id']=$_REQUEST['cat_id'];
			
			$this->assign("cat_id",$_REQUEST['cat_id']);
			$_GET['cat_id']=$_REQUEST['cat_id'];
		}
		
		if(!empty($_REQUEST['keyword']))
		{
			$where['title']=array('like',"%".$_REQUEST['keyword']."%");
			
			$this->assign("keyword",$_REQUEST['keyword']);
			$_GET['keyword']=$_REQUEST['keyword'];
		}

		$count=$model->where($where)->count();
		$page = $this->page($count, 10);
		$users = $model->where($where)->order('article_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();

		$this->assign("page", $page->show('Admin'));
		$this->assign("list",$users);
		$this->assign("count",$count);
		$this->assign("formget",$_GET);
		$this->display();
	}
	
	//添加修改
	function article_info()
	{
		$id=$_REQUEST['id'];
		$article=M('article');
		$where['article_id']=$id;
		$article_info=$article->where($where)->find();
        //vv($article_info);
		
		$cat=M('article_cat');
		$cat_list=$cat->where()->order('sort_order asc')->select();
		//vv($cat_list[0]);
		$this->assign("cat_list",$cat_list);
		
		$this->assign("row",$article_info);
		$this->display();
	}
	
	//添加修改
	function article_info1()
	{
		$id=$_REQUEST['id'];
		$article=M('article');
		$where['article_id']=$id;
		$article_info=$article->where($where)->find();
        //vv($article_info);
		$this->assign("row",$article_info);
		$this->display('article_info');
	}
	
	//保存新闻
	public function article_save()
	{
		$model=M('article');
		if (IS_POST) 
		{
			$array=I("post.post");
			
			$cat_name=explode(',',$_POST['cat_name']);
			$array['cat_name']=$cat_name['1'];
			$array['cat_id']=$cat_name['0'];
			
			$array['publish_time']=strtotime($array['publish_time']);  //发布时间
			
			if(empty($_POST['article_id']))
			{
				$array['add_time']=time();
				$result=$model->add($array);
			}else
			{
				$result=$model->where(array('article_id'=>$_POST['article_id']))->save($array);
			}
			
			if ($result) {
				$this->success("操作成功！",'index.php?g=Admin&m=Article&a=article_list');
			} else {
				$this->error("操作失败！");
			}
			//print_r($article);
		}
	}
	
	//删除
	function article_del()
	{
		$model=M('article');
		if(isset($_GET['tid'])){
			$tid = intval(I("get.tid"));
			//$data['status']=0;
			if ($model->where("article_id=$tid")->delete()) {
				$this->success("删除成功！",'index.php?g=Admin&m=Article&a=article_list');
			} else {
				$this->error("删除失败！");
			}
		}
		if(isset($_REQUEST['ids'])){
			$tids=join(",",$_REQUEST['ids']);
			//$data['status']=0;
			if ($model->where("article_id in ($tids)")->delete()) {
				$this->success("删除成功！",'index.php?g=Admin&m=Article&a=article_list');
			} else {
				$this->error("删除失败！");
			}
		}
	}
	
	//分类列表
	public function article_type()
	{
		$model=M('article_cat');
		//分组keyword
		
		if(!empty($_REQUEST['cat_name']))
		{
			$where['cat_name']=$_REQUEST['cat_name'];
			
			$this->assign("cat_name",$_REQUEST['cat_name']);
			$_GET['cat_name']=$_REQUEST['cat_name'];
		}
		
		if(!empty($_REQUEST['keyword']))
		{
			$where['title']=array('like',"%".$_REQUEST['keyword']."%");
			
			$this->assign("keyword",$_REQUEST['keyword']);
			$_GET['keyword']=$_REQUEST['keyword'];
		}

		$count=$model->where($where)->count();
		$page = $this->page($count, 10);
		$users = $model->where($where)->order('cat_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();

		$this->assign("page", $page->show('Admin'));
		$this->assign("list",$users);
		$this->assign("count",$count);
		$this->assign("formget",$_GET);
		$this->display();
	}
	
	//添加分类
	public function type_info()
	{
		
		$id=$_REQUEST['id'];
		$article=M('article_cat');
		$where['cat_id']=$id;
		$article_info=$article->where($where)->find();
        //vv($article_info);
		$this->assign("row",$article_info);
		$this->display();
	}
	
	//保存分类
	public function type_save()
	{
		$model=M('article_cat');
		if (IS_POST) 
		{
			$array=I("post.post");
			$array['cat_type']=0;
			$array['parent_id']=0;
			if(empty($_POST['cat_id']))
			{
				//$array['add_time']=time();
				$result=$model->add($array);
			}else
			{
				$result=$model->where(array('cat_id'=>$_POST['cat_id']))->save($array);
			}
			
			if ($result) {
				$this->success("操作成功！",'index.php?g=Admin&m=Article&a=article_type');
			} else {
				$this->error("操作失败！");
			}
			//print_r($article);
		}
	}
	
	
	//删除
	function type_del()
	{
		$model=M('article_cat');
		if(isset($_GET['tid'])){
			$tid = intval(I("get.tid"));
			//$data['status']=0;
			if ($model->where("cat_id=$tid")->delete()) {
				$this->success("操作成功！",'index.php?g=Admin&m=Article&a=article_type');
			} else {
				$this->error("删除失败！");
			}
		}
		if(isset($_REQUEST['ids'])){
			$tids=join(",",$_REQUEST['ids']);
			//$data['status']=0;
			if ($model->where("cat_id in ($tids)")->delete()) {
				$this->success("操作成功！",'index.php?g=Admin&m=Article&a=article_type');
			} else {
				$this->error("删除失败！");
			}
		}
	}

	
}