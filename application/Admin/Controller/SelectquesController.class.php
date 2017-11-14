<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class SelectquesController extends AdminbaseController{
	
	function _initialize() {
		parent::_initialize();
	}
	
	function index()
	{
		$this->selectques_list();
	}
	
	public function selectques_list()
	{
		$model=M('selectques');
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
		$users = $model->where($where)->order('select_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
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
	public function selectques_info()
	{
		//性别表（sexs）
		$sexs=sexs();		//获取标签
		$this->assign("sexs",$sexs);
		
		$id=$_REQUEST['id'];
		$model=M('selectques');
		$where['select_id']=$id;
		$selectques_info=$model->where($where)->find();
		$zimu=array('a','b','c','d','e','f','g','h');
		foreach($zimu as $k=>$v)
		{
			//echo $v.'111';
			if(!empty($selectques_info[$v]))
			{
				$zimu_arr[$v]=$selectques_info[$v];
			}
		}
		$selectques_info['answer']=$zimu_arr;
		vv($selectques_info);die;
		$this->assign("row",$selectques_info);
		
		$this->display();
	}
	
	//保存&修改选择题
	public function selectques_save()
	{
		$model=M('selectques');
		if (IS_POST) 
		{
			$array=I("post.post");
			$zimu=array('a','b','c','d','e','f','g');
			foreach($zimu as $k=>$v)
			{
				if(empty($array[strtoupper($v)]))
				{
					$array[strtoupper($v)]='';
				}
				
			}
			
			//vv($array);die;
			if(empty($_POST['id']))
			{
				$array['add_time']=time();
				$result=$model->add($array);
			}else
			{
				$result=$model->where(array('select_id'=>$_POST['id']))->save($array);
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
	public function selectques_add()
	{
		$model=M('selectques');
		$id = intval(I("get.id"));
		
		$select_info=$model->where(array('select_id'=>$id))->find();
		
		if(!empty($select_info))
		{
			$_SESSION['selectques'][$id]=$select_info;
			$this->success("操作成功！");
		}else
		{
			$this->error("操作失败！");
		}
		
		
	}
	
	//修改试卷的答案
	public function quespaper_info()
	{
		$model=M('ques_paper');		//题目表
		$selectques=M('selectques');	//选择题题库表
		
		//选择字母表（selectzimu）
		$selectzimu=selectzimu();
		$this->assign("selectzimu",$selectzimu);
		
		//获取题目数据
		$paper_id = intval(I("get.paper_id"));
		if(!empty($paper_id))
		{
			$paper_info=$model->where(array('paper_id'=>$paper_id))->find();		//获取已有题目数据
			$select_list=$selectques->where(array('select_id'=>array('in',$paper_info['selectques_index'])))->select();
			
			foreach($select_list as $k=>$v)
			{
				$new_select_list[$v['select_id']]=$v;
			}
			
			$new_select_list=merge_arr($new_select_list,json_decode($paper_info['selectques'],true));
			//vv($new_select_list);
			if(empty($_SESSION['selectques']))
			{
				$select_news = $new_select_list;
			}else
			{
				$select_news = $new_select_list+$_SESSION['selectques'];
			}
			
			
			$this->assign("row",$paper_info);
		}else
		{
			$select_news = $_SESSION['selectques'];
		}
		
		//vv($select_news);
		
		//vv(merge_arr($select_news,json_decode($paper_info['selectques'],true)));
		$this->assign("selectques",$select_news);
		
		$this->display();
	}
	
	//保存调查卷
	public function quespaper_save()
	{
		$model=M('ques_paper');
		if (IS_POST) 
		{
			$array=I("post.post");
			//选择题
			$paper_arr=I("post.paper");
			foreach($paper_arr as $k=>$v)
			{
				$id_arr=explode('-',$k); 
				$selectques_data['selectques_id']=$id_arr[1];	//选择题的id
				if(count($v)>1)
				{
					$selectques_data['selectques_class']=2;		//多个答案
				}else
				{
					$selectques_data['selectques_class']=1;		//单个答案
				}
				
				$selectques_data['selectques_ok']=implode(',',$v);		//选择题答案
				$selectques_arr[$id_arr[1]]=$selectques_data;
				$selectques_index[]=$id_arr[1];
				
			}
			//vv($selectques_arr);
			$array['selectques']=json_encode($selectques_arr);		//选择题id索引
			$array['selectques_index']=implode(',',$selectques_index);		//选择题id索引
			$array['selectques_num']=count($paper_arr);		//选择题数量
			
			if(empty($_POST['id']))
			{
				$array['add_time']=time();
				$result=$model->add($array);
			}else
			{
				$result=$model->where(array('paper_id'=>$_POST['id']))->save($array);
			}
			
			if ($result) {
				unset($_SESSION['selectques']);
				$this->success("操作成功！");
			} else {
				$this->error("操作失败！");
			}
			
		}
		//vv($array);
	}
	
	//删除
	function selectques_del()
	{
		$model=M('selectques');
		if(isset($_GET['tid'])){
			$tid = intval(I("get.tid"));
			//$data['status']=0;
			if ($model->where("select_id=$tid")->delete()) {
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}
		if(isset($_REQUEST['ids'])){
			$tids=join(",",$_REQUEST['ids']);
			//$data['status']=0;
			if ($model->where("select_id in ($tids)")->delete()) {
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}
	}

	
}