<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class AnswerquesController extends AdminbaseController{
	
	function _initialize() {
		parent::_initialize();
	}
	
	function index()
	{
		$this->answerques_list();
	}
	
	public function answerques_list()
	{
		$model=M('answerques');
		//分组keyword
		if(!empty($_REQUEST['cat_id']))
		{
			$where['cat_id']=$_REQUEST['cat_id'];
			
			$this->assign("cat_id",$_REQUEST['cat_id']);
			$_GET['cat_id']=$_REQUEST['cat_id'];
		}
		
		if(!empty($_REQUEST['keyword']))
		{
			$where['name']=array('like',"%".$_REQUEST['keyword']."%");
			
			$this->assign("keyword",$_REQUEST['keyword']);
			$_GET['keyword']=$_REQUEST['keyword'];
		}

		$count=$model->where($where)->count();
		$page = $this->page($count, 10);
		$users = $model->where($where)->order('answer_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		//vv($users[0]);
		$zimu=array('a','b','c','d','e','f','g','h');
		foreach($users as $kk=>$vv)
		{
			$answer_arr=array();
			foreach($zimu as $k=>$v)
			{
				//echo $v.'111';
				if(!empty($vv[$v]))
				{
					$answer_arr[]=strtoupper($v).":".$vv[$v];
				}else
				{
					break;
				}
			}
			$users[$kk]['answer_str']=implode(' | ',$answer_arr);
		}
		$this->assign("page", $page->show('Admin'));
		$this->assign("list",$users);
		$this->assign("count",$count);
		$this->assign("formget",$_GET);
		$this->display();
	}
	
	//添加&修改选择题
	public function answerques_info()
	{
		//性别表（sexs）
		$sexs=sexs();		//获取标签
		$this->assign("sexs",$sexs);
		
		$id=$_REQUEST['id'];
		$model=M('answerques');
		$where['answer_id']=$id;
		$selectques_info=$model->where($where)->find();
		
		//vv($selectques_info);
		$this->assign("row",$selectques_info);
		
		$this->display();
	}
	
	//保存&修改选择题
	public function answerques_save()
	{
		$model=M('answerques');
		if (IS_POST) 
		{
			$array=I("post.post");
			
			if(empty($_POST['id']))
			{
				$array['add_time']=time();
				$result=$model->add($array);
			}else
			{
				$result=$model->where(array('answer_id'=>$_POST['id']))->save($array);
			}
			
			if ($result) {
				$this->success("操作成功！");
			} else {
				$this->error("操作失败！");
			}
			//print_r($article);
		}
	}
	
	//添加题目（保存到session里）
	public function answerques_add()
	{
		$model=M('answerques');
		$id = intval(I("get.id"));
		
		$select_info=$model->where(array('answer_id'=>$id))->find();
		
		if(!empty($select_info))
		{
			$_SESSION['answerques'][$id]=$select_info;
			$this->success("操作成功！");
		}else
		{
			$this->error("操作失败！");
		}
		
		
	}
	
	//删除
	function answerques_del()
	{
		$model=M('answerques');
		if(isset($_GET['tid'])){
			$tid = intval(I("get.tid"));
			//$data['status']=0;
			if ($model->where("answer_id=$tid")->delete()) {
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}
		if(isset($_REQUEST['ids'])){
			$tids=join(",",$_REQUEST['ids']);
			//$data['status']=0;
			if ($model->where("answer_id in ($tids)")->delete()) {
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}
	}

	
}