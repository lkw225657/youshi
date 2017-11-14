<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class OrderController extends AdminbaseController{
	
	function _initialize() {
		parent::_initialize();
	}
	
	public function index()
	{
		echo '123';
	}
	
	public function order_list()
	{
		
		$model=M('jifen_order');
		
		//项目分类
		if(!empty($_REQUEST['state']))
		{
			$where['state']=$_REQUEST['state'];
			$this->assign("state",$_REQUEST['state']);
			$_GET['state']=$_REQUEST['state'];
		}
		
		//搜索keyword
		if(!empty($_REQUEST['keyword']) && !empty($_REQUEST['search_type']))
		{
			$_REQUEST['search_type'];
			if($_REQUEST['search_type']==1)
			{
				$where['order_no']=array('like',"%".$_REQUEST['keyword']."%");
			}else if($_REQUEST['search_type']==2)
			{
				$where['user_name']=array('like',"%".$_REQUEST['keyword']."%");
			}else if($_REQUEST['search_type']==3)
			{
				$where['goods_name']=array('like',"%".$_REQUEST['keyword']."%");
			}
			else if($_REQUEST['search_type']==4)    //商品编号
			{
				$where['phone']=array('like',"%".$_REQUEST['keyword']."%");
				//$where['user_id']=$_REQUEST['keyword'];
			}

			$this->assign("search_type",$_REQUEST['search_type']);
			$this->assign("keyword",$_REQUEST['keyword']);
			$_GET['search_type']=$_REQUEST['search_type'];
			$_GET['keyword']=$_REQUEST['keyword'];
		}
		
		
		$count=$model->where($where)->count();
		$page = $this->page($count, 10);
		$user=M('member');
		$users = $model->where($where)->order('order_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		
		$express=M('express');
		$express_list=$express->where(array('state'=>1))->select();
		$this->assign("express_list",$express_list);
		$this->assign("page", $page->show('Admin'));
		$this->assign("list",$users);
		$this->assign("count",$count);
		$this->assign("formget",$_GET);
		$this->display();
	}
	
	//修改订单state状态
	public function order_state()
	{
		$data['state']=$_GET['state'];
		if($_GET['state']==3)
		{
			$data['shou_time']=time();
		}
		$model=M('jifen_order');
		$result=$model->where(array('order_id'=>$_GET['order_id']))->save($data);
		if ($result) {
			$this->admin_log($_SESSION['ADMIN_ID'],'修改订单状态为,'.$data['state']);
			$this->success("操作成功！");//,'index.php?g=Admin&m=project&a=project_info_step3&type=1&project_id='.$project_id);
		} else {
			$this->error("操作失败！");
		}
	}
	
	//订单发货
	public function order_fahuo()
	{
		$model=M('jifen_order');
		if (IS_POST) 
		{
			$array=I("post.post");
			$array['state']=2;
			
			$kuaidi_arr=explode('-',$_POST['kuaidi']);
			$array['kuaidi']=$kuaidi_arr[0];
			$array['kuaidi_code']=$kuaidi_arr[1];
			$array['fa_time']=time();
			$result=$model->where(array('order_id'=>$_POST['order_id']))->save($array);
			
			if ($result) {
				$this->admin_log($_SESSION['ADMIN_ID'],'修改订单状态为发货,订单id为'.$_POST['order_id']);
				$this->success("操作成功！");//,'index.php?g=Admin&m=project&a=project_info_step3&type=1&project_id='.$project_id);
			} else {
				$this->error("操作失败！");
			}
		}
		//vv($_POST);
	}
	
	//删除
	function order_del()
	{
		$model=M('jifen_order');
		if(isset($_GET['tid'])){
			$tid = intval(I("get.tid"));
			//$data['status']=0;
			if ($model->where("order_id=$tid")->delete()) {
				$this->admin_log($_SESSION['ADMIN_ID'],'删除订单,订单id为'.$tid);
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}
		if(isset($_REQUEST['ids'])){
			$tids=join(",",$_REQUEST['ids']);
			//$data['status']=0;
			if ($model->where("order_id in ($tids)")->delete()) {
				$this->admin_log($_SESSION['ADMIN_ID'],'批量删除订单,订单id为'.$tids);
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}
	}
	
	public function express_list()
	{
		$model=M('express');
		
		//项目分类
		if(!empty($_REQUEST['state']))
		{
			$where['state']=$_REQUEST['state'];
			$this->assign("state",$_REQUEST['state']);
			$_GET['state']=$_REQUEST['state'];
		}
		
		//搜索keyword
		if(!empty($_REQUEST['keyword']) && !empty($_REQUEST['search_type']))
		{
			$_REQUEST['search_type'];
			if($_REQUEST['search_type']==1)
			{
				$where['name']=array('like',"%".$_REQUEST['keyword']."%");
			}else if($_REQUEST['search_type']==2)
			{
				$where['user_name']=array('like',"%".$_REQUEST['keyword']."%");
			}else if($_REQUEST['search_type']==3)
			{
				$where['goods_name']=array('like',"%".$_REQUEST['keyword']."%");
			}
			else if($_REQUEST['search_type']==4)    //商品编号
			{
				$where['phone']=array('like',"%".$_REQUEST['keyword']."%");
				//$where['user_id']=$_REQUEST['keyword'];
			}

			$this->assign("search_type",$_REQUEST['search_type']);
			$this->assign("keyword",$_REQUEST['keyword']);
			$_GET['search_type']=$_REQUEST['search_type'];
			$_GET['keyword']=$_REQUEST['keyword'];
		}
		
		
		$count=$model->where($where)->count();
		$page = $this->page($count, 10);
		$users = $model->where($where)->order('express_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		
		$this->assign("page", $page->show('Admin'));
		$this->assign("list",$users);
		$this->assign("count",$count);
		$this->assign("formget",$_GET);
		$this->display();
		
	}
	
	//修改VIP状态
	function express_state()
	{
		
		$model=M('express');
		$id=$_REQUEST['express_id'];   //id
		$state=$_REQUEST['state'];   //状态
		if(!empty($id))
		{
			$type=$_REQUEST['type'];
			$data[$type]=$state;
			if ($model->where(array('express_id'=>$id))->save($data)) {
				//die;
				$this->success("执行成功！");
			} else {
				$this->error("执行失败！");
			}
		}
	}
	

	
}