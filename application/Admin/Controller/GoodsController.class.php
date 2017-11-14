<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class GoodsController extends AdminbaseController{
	
	function _initialize() {
		parent::_initialize();
	}
	
	public function index()
	{
		echo '123';
	}
	
	public function goods_list()
	{
		//获取商品列表
		$goods_type=M('goods_type');
		$type_list=$goods_type->where(array('type_state'=>1))->select();
		foreach($type_list as $k=>$v)
		{
			$new_type_list[$v['type_id']]=$v;
		}
		$this->assign("type_list",$new_type_list);
		
		$model=M('goods');
		
		//项目分类
		if(!empty($_REQUEST['search_class']))
		{
			$where['type_id']=$_REQUEST['search_class'];
			$this->assign("search_class",$_REQUEST['search_class']);
			$_GET['search_class']=$_REQUEST['search_class'];
		}
		
		//搜索keyword
		if(!empty($_REQUEST['keyword']) && !empty($_REQUEST['search_type']))
		{
			$_REQUEST['search_type'];
			if($_REQUEST['search_type']==1)
			{
				$where['goods_name']=array('like',"%".$_REQUEST['keyword']."%");
			}else if($_REQUEST['search_type']==2)
			{
				$where['title']=array('like',"%".$_REQUEST['keyword']."%");
			}else if($_REQUEST['search_type']==3)
			{
				$where['id']=$_REQUEST['keyword'];
			}
			else if($_REQUEST['search_type']==4)    //商品编号
			{
				$where['user_id']=$_REQUEST['keyword'];
			}

			$this->assign("search_type",$_REQUEST['search_type']);
			$this->assign("keyword",$_REQUEST['keyword']);
			$_GET['search_type']=$_REQUEST['search_type'];
			$_GET['keyword']=$_REQUEST['keyword'];
		}
		
		
		$count=$model->where($where)->count();
		$page = $this->page($count, 10);
		$user=M('users');
		$users = $model->where($where)->order('goods_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		
		$this->assign("page", $page->show('Admin'));
		$this->assign("list",$users);
		$this->assign("count",$count);
		$this->assign("formget",$_GET);
		$this->display();
	}
	
	public function goods_info()
	{
		//获取商品列表
		$goods_type=M('goods_type');
		$type_list=$goods_type->where(array('type_state'=>1))->select();
		//vv($type_list[0]);
		$this->assign("type_list",$type_list);
		
		
		
		$id=$_REQUEST['goods_id'];
		$model=M('goods');
		if(!empty($id))
		{
			$where['goods_id']=$id;
			$goods_info=$model->where($where)->find();
			//vv($goods_info);
			$this->assign("goods_info",$goods_info);
		}
		
		
		$this->display();
	}
	
	public function goods_save()
	{
		$model=M('goods');
		if (IS_POST) 
		{
			$array=I("post.post");
			//vv($_POST);
			//vv($array);
			if(empty($_POST['goods_id']))
			{
				$array['add_time']=time();
				$result=$model->add($array);
				if($result)
					$this->admin_log($_SESSION['ADMIN_ID'],'添加商品,'.$array['goods_name']);
			}else
			{
				$result=$model->where(array('goods_id'=>$_POST['goods_id']))->save($array);
				if($result)
					$this->admin_log($_SESSION['ADMIN_ID'],'修改商品,'.$array['goods_name']);
			}
			
			if ($result) {
				$this->success("操作成功！");//,'index.php?g=Admin&m=project&a=project_info_step3&type=1&project_id='.$project_id);
			} else {
				$this->error("操作失败！");
			}
			
		}
		//echo '保存商品';
	}
	
	//修改VIP状态
	function goods_state()
	{
		
		$model=M('goods');
		$id=$_REQUEST['goods_id'];   //id
		$status=$_REQUEST['status'];   //状态
		if(!empty($id))
		{
			$type=$_REQUEST['type'];
			$data[$type]=$status;
			if ($model->where(array('goods_id'=>$id))->save($data)) {
				//die;
				$this->success("执行成功！");
			} else {
				$this->error("执行失败！");
			}
		}
	}
	
	//删除
	function goods_del()
	{
		$model=M('goods');
		if(isset($_GET['tid'])){
			$tid = intval(I("get.tid"));
			//$data['status']=0;
			$array=$model->field('goods_name')->where("goods_id=$tid")->find();
			if ($model->where("goods_id=$tid")->delete()) {

					$this->admin_log($_SESSION['ADMIN_ID'],'删除商品,'.$array['goods_name']);
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}
		if(isset($_REQUEST['ids'])){
			$tids=join(",",$_REQUEST['ids']);
			//$data['status']=0;
			if ($model->where("goods_id in ($tids)")->delete()) {
				$this->admin_log($_SESSION['ADMIN_ID'],'批量删除商品,'.$tids);
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}
	}
	
	//商品分类
	public function goods_type()
	{
		
		$model=M('goods_type');
		
		//搜索keyword
		if(!empty($_REQUEST['keyword']) && !empty($_REQUEST['search_type']))
		{
			$_REQUEST['search_type'];
			if($_REQUEST['search_type']==1)
			{
				$where['type_name']=array('like',"%".$_REQUEST['keyword']."%");
			}else if($_REQUEST['search_type']==2)
			{
				$where['title']=array('like',"%".$_REQUEST['keyword']."%");
			}else if($_REQUEST['search_type']==3)
			{
				$where['id']=$_REQUEST['keyword'];
			}
			else if($_REQUEST['search_type']==4)    //商品编号
			{
				$where['user_id']=$_REQUEST['keyword'];
			}

			$this->assign("search_type",$_REQUEST['search_type']);
			$this->assign("keyword",$_REQUEST['keyword']);
			$_GET['search_type']=$_REQUEST['search_type'];
			$_GET['keyword']=$_REQUEST['keyword'];
		}
		
		
		$count=$model->where($where)->count();
		$page = $this->page($count, 10);
		$user=M('users');
		$users = $model->where($where)->order('type_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		
		$this->assign("page", $page->show('Admin'));
		$this->assign("list",$users);
		$this->assign("count",$count);
		$this->assign("formget",$_GET);
		$this->display();
	}
	
	//商品分类添加修改
	public function type_info()
	{
		//echo '1111';die;
		$id=$_REQUEST['type_id'];
		$model=M('goods_type');
		$where['type_id']=$id;
		$type_info=$model->where($where)->find();
		//vv($type_info);
		$this->assign("type_info",$type_info);
		
		$this->display();
	}
	//保存分类
	public function type_save()
	{
		$model=M('goods_type');
		if (IS_POST) 
		{
			$array=I("post.post");
			
			if(empty($_POST['type_id']))
			{
				$array['add_time']=time();
				$result=$model->add($array);
				if($result)
					$this->admin_log($_SESSION['ADMIN_ID'],'添加商品分类,'.$array['type_name']);

			}else
			{
				$result=$model->where(array('type_id'=>$_POST['type_id']))->save($array);
				if($result)
					$this->admin_log($_SESSION['ADMIN_ID'],'修改商品分类,'.$array['type_name']);

			}
			
			if ($result) {
				$this->success("操作成功！");//,'index.php?g=Admin&m=project&a=project_info_step3&type=1&project_id='.$project_id);
			} else {
				$this->error("操作失败！");
			}
			
			//vv($_POST);
		}
		
	}
	
	//删除
	function type_del()
	{
		$model=M('goods_type');
		if(isset($_GET['tid'])){
			$tid = intval(I("get.tid"));
			//$data['status']=0;
			$array=$model->field('type_name')->where("type_id=$tid")->find();
			if ($model->where("type_id=$tid")->delete()) {
				$this->admin_log($_SESSION['ADMIN_ID'],'删除商品分类,'.$array['type_name']);
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}
		if(isset($_REQUEST['ids'])){
			$tids=join(",",$_REQUEST['ids']);
			//$data['status']=0;
			if ($model->where("type_id in ($tids)")->delete()) {
				$this->admin_log($_SESSION['ADMIN_ID'],'删除商品分类,'.$tids);
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}
	}
	
	
	
	//积分列表
	public function jifen_list()
	{
		$model=M('jifen_log');
		
		//搜索keyword
		if(!empty($_REQUEST['keyword']) && !empty($_REQUEST['search_type']))
		{
			$_REQUEST['search_type'];
			if($_REQUEST['search_type']==1)
			{
				$where['goods_name']=array('like',"%".$_REQUEST['keyword']."%");
			}else if($_REQUEST['search_type']==2)
			{
				$where['goods_id']=$_REQUEST['keyword'];
			}else if($_REQUEST['search_type']==3)
			{
				$where['user_name']=array('like',"%".$_REQUEST['keyword']."%");
			}
			else if($_REQUEST['search_type']==4)    //商品编号
			{
				$where['user_id']=$_REQUEST['keyword'];
			}

			$this->assign("search_type",$_REQUEST['search_type']);
			$this->assign("keyword",$_REQUEST['keyword']);
			$_GET['search_type']=$_REQUEST['search_type'];
			$_GET['keyword']=$_REQUEST['keyword'];
		}
		
		
		$count=$model->where($where)->count();
		$page = $this->page($count, 10);
		$user=M('users');
		$users = $model->where($where)->order('jifen_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		//vv($users[0]);
		$this->assign("page", $page->show('Admin'));
		$this->assign("list",$users);
		$this->assign("count",$count);
		$this->assign("formget",$_GET);
		$this->display();
	}
	
	//删除
	function jifen_del()
	{
		$model=M('jifen_log');
		if(isset($_GET['tid'])){
			$tid = intval(I("get.tid"));
			//$data['status']=0;
			if ($model->where("jifen_id=$tid")->delete()) {
			$this->admin_log($_SESSION['ADMIN_ID'],'删除积分记录,'.$tid);
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}
		if(isset($_REQUEST['ids'])){
			$tids=join(",",$_REQUEST['ids']);
			//$data['status']=0;
			if ($model->where("jifen_id in ($tids)")->delete()) {
				$this->admin_log($_SESSION['ADMIN_ID'],'删除积分记录,'.$tids);
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}
	}
	
	//评论列表
	public function pinglun_list()
	{
		$model=M('goods_pinglun');
		
		//搜索keyword
		if(!empty($_REQUEST['keyword']) && !empty($_REQUEST['search_type']))
		{
			$_REQUEST['search_type'];
			if($_REQUEST['search_type']==1)
			{
				$where['goods_name']=array('like',"%".$_REQUEST['keyword']."%");
			}else if($_REQUEST['search_type']==2)
			{
				$where['goods_id']=$_REQUEST['keyword'];
			}else if($_REQUEST['search_type']==3)
			{
				$where['user_name']=array('like',"%".$_REQUEST['keyword']."%");
			}
			else if($_REQUEST['search_type']==4)    //商品编号
			{
				$where['user_id']=$_REQUEST['keyword'];
			}

			$this->assign("search_type",$_REQUEST['search_type']);
			$this->assign("keyword",$_REQUEST['keyword']);
			$_GET['search_type']=$_REQUEST['search_type'];
			$_GET['keyword']=$_REQUEST['keyword'];
		}
		
		
		$count=$model->where($where)->count();
		$page = $this->page($count, 10);
		$user=M('users');
		$users = $model->where($where)->order('pinglun_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		//vv($users[0]);
		$this->assign("page", $page->show('Admin'));
		$this->assign("list",$users);
		$this->assign("count",$count);
		$this->assign("formget",$_GET);
		$this->display();
	}
	
	//删除
	function pinglun_del()
	{
		$model=M('goods_pinglun');
		if(isset($_GET['tid'])){
			$tid = intval(I("get.tid"));
			//$data['status']=0;
			if ($model->where("pinglun_id=$tid")->delete()) {
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}
		if(isset($_REQUEST['ids'])){
			$tids=join(",",$_REQUEST['ids']);
			//$data['status']=0;
			if ($model->where("pinglun_id in ($tids)")->delete()) {
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}
	}
	

	
}