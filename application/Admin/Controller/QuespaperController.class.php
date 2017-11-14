<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class QuespaperController extends AdminbaseController{
	
	function _initialize() {
		parent::_initialize();
	}
	
	function index()
	{
		$this->selectques_list();
	}
	
	public function quespaper_list()
	{
		$model=M('ques_paper');
		//分组keyword
		if(!empty($_REQUEST['search_type']))
		{
			$where['type']=$_REQUEST['search_type'];
			$this->assign("search_type",$_REQUEST['search_type']);
			$_GET['search_type']=$_REQUEST['search_type'];
		}
		
		if(!empty($_REQUEST['keyword']))
		{
			$where['name']=array('like',"%".$_REQUEST['keyword']."%");
			
			$this->assign("keyword",$_REQUEST['keyword']);
			$_GET['keyword']=$_REQUEST['keyword'];
		}

		$count=$model->where($where)->count();
		$page = $this->page($count, 10);
		$users = $model->where($where)->order('paper_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		
		$this->assign("page", $page->show('Admin'));
		$this->assign("list",$users);
		$this->assign("count",$count);
		$this->assign("formget",$_GET);
		$this->display();
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
		$answerques=M('answerques');	//问答题题库表
		$project=M('project');	//项目表
		
		//选择字母表（selectzimu）
		$selectzimu=selectzimu();
		$this->assign("selectzimu",$selectzimu);
		
		//项目数据
		$project_id = intval(I("get.project_id"));
		$project_info=$project->where(array('id'=>$project_id))->find();
		vv($project_info);
		$this->assign("project_info",$project_info);
		
		//获取题目数据
		$paper_id = intval(I("get.paper_id"));
		if(!empty($paper_id))
		{
			
			$paper_info=$model->where(array('paper_id'=>$paper_id))->find();		//获取已有题目数据
			//var_dump($paper_info['selectques_index']);
			//选择题
			if(!empty($paper_info['selectques_index'])){
				
				$select_list=$selectques->where(array('select_id'=>array('in',$paper_info['selectques_index'])))->select();
				
				foreach($select_list as $k=>$v)
				{
					$new_select_list[$v['select_id']]=$v;
				}
			}
			
			$new_select_list=merge_arr($new_select_list,json_decode($paper_info['selectques'],true));
			//vv($new_select_list);
			
			if(!empty($_SESSION['selectques']) && !empty($new_select_list))
			{
				$select_news = $new_select_list+$_SESSION['selectques'];
			}else
			{
				if(empty($_SESSION['selectques']))
				{
					$select_news = $new_select_list;
				}else
				{
					$select_news = $_SESSION['selectques'];
				}
			}
			
			//问答题
			if(!empty($paper_info['selectques_index'])){
				
				$answer_list=$answerques->where(array('answer_id'=>array('in',$paper_info['answerques_index'])))->select();
				foreach($answer_list as $k=>$v)
				{
					$new_answer_list[$v['answer_id']]=$v;
				}
			}
			
			if(!empty($_SESSION['answerques']) && !empty($new_answer_list))
			{
				
				$answer_news = $new_answer_list+$_SESSION['answerques'];
			}else
			{
				if(empty($_SESSION['answerques']))
				{
					$answer_news = $new_answer_list;
				}else
				{
					$answer_news = $_SESSION['answerques'];
				}
			}
			//vv($answer_news);
			$this->assign("row",$paper_info);
		}else
		{
			$select_news = $_SESSION['selectques'];
			$answer_news = $_SESSION['answerques'];
		}
		$this->assign("selectques",$select_news);
		
		$this->assign("answerques",$answer_news);
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
			$array['selectques_num']=count($selectques_index);		//选择题数量
			
			//选择题
			$array['answerques']=json_encode($_POST['answer']);		//选择题id索引
			$array['answerques_index']=implode(',',$_POST['answer']);		//选择题id索引
			$array['answerques_num']=count($_POST['answer']);		//选择题数量
			//vv($array);die;
			if(empty($_POST['id']))
			{
				$array['add_time']=time();
				$result=$model->add($array);
			}else
			{
				$result=$model->where(array('paper_id'=>$_POST['id']))->save($array);
			}
			
			if ($result) {
				//释放json
				unset($_SESSION['selectques']);
				unset($_SESSION['answerques']);
				$this->success("操作成功！");
			} else {
				$this->error("操作失败！");
			}
			
		}
		//vv($array);
	}
	
	//删除
	function quespaper_del()
	{
		$model=M('ques_paper');
		if(isset($_GET['tid'])){
			$tid = intval(I("get.tid"));
			//$data['status']=0;
			if ($model->where("paper_id=$tid")->delete()) {
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}
		if(isset($_REQUEST['ids'])){
			$tids=join(",",$_REQUEST['ids']);
			//$data['status']=0;
			if ($model->where("paper_id in ($tids)")->delete()) {
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}
	}

	
}