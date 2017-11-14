<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class ClassController extends AdminbaseController{
	
	function _initialize() {
		parent::_initialize();
	}
	
	function index()
	{
		$model=M('class');
		$parent_list=$model->where(array('parent_id'=>0))->select();
		$this->assign("parent_list",$parent_list);
		//vv($parent_list);
		
		//订单状态
		if(!empty($_REQUEST['parent_id']))
		{
			$where['parent_id']=$_REQUEST['parent_id'];
			$this->assign("parent_id",$_REQUEST['parent_id']);
			$_GET['parent_id']=$_REQUEST['parent_id'];
		}
		
		if(!empty($_REQUEST['keyword']))
		{
			$where['name']=array("like","%".$_REQUEST['keyword']."%");
			$this->assign("keyword",$_REQUEST['keyword']);
			$_GET['keyword']=$_REQUEST['keyword'];
		}
		
		$count=$model->where($where)->count();
		$page = $this->page($count, 10);
		$users = $model->where($where)->order('class_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();

		$this->assign("page", $page->show('Admin'));
		$this->assign("list",$users);
		$this->assign("count",$count);
		$this->assign("formget",$_GET);
	
		$this->display('');
	}
	
	public function class_info()
	{
		
		$model=M('class');
		$parent_list=$model->where(array('parent_id'=>0))->select();
		$this->assign("parent_list",$parent_list);
		
		$this->assign("formget",$_GET);
		$this->display();
	}
	
	public function class_save()
	{
		$model=M('class');
		if (IS_POST) 
		{
			$array=I("post.post");
			
			if(empty($_POST['class_id']))
			{
				$array['add_time']=time();
				$result=$model->add($array);
			}else
			{
				$result=$model->where(array('class_id'=>$_POST['class_id']))->save($array);
			}
			
			if ($result) {
				$this->success("操作成功！");
			} else {
				$this->error("操作失败！");
			}
			//print_r($article);
		}
	}

	public function class_edit()
	{
		$model=M('class');
		$class_id=$_GET['id'];
		$parent_list=$model->where(array('parent_id'=>0))->select();
		$this->assign("parent_list",$parent_list);
		$class_info=$model->where("class_id=$class_id")->find();
		$this->assign('row',$class_info);
		$this->display('class_info');
	}
	public function class_del()
	{
		$model=M('class');
		$class_id=$_GET['id'];
		
		$result=$model->where("class_id=$class_id")->delete();
		if ($result) {
				$this->success("操作成功！");
			} else {
				$this->error("操作失败！");
			}
	}
	
}