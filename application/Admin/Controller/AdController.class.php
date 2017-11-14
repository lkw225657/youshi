<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class AdController extends AdminbaseController{
	protected $ad_model;
	
	function _initialize() {
		parent::_initialize();
		$this->ad_model = D("Common/Ad");
	}
	
	function index(){
		$ads=$this->ad_model->select();
		$this->assign("ads",$ads);
		$this->display();
	}
	
	//广告位列表
	function positionList()
	{
		//echo '广告位列表';
		$model=M('ad_position');
		$ap_list=$model->order('id desc')->select();
		//vv($ap_list);
		$this->assign("ap_list",$ap_list);
		$this->display();
	}
	
	//添加111
	function ap_add()
	{
		$model=M('ad_position');
		$_GET['id'];
		$ap_info=$model->where(array('id'=>$_GET['id']))->find();
		//vv($ap_info);
		$this->assign("ap_info",$ap_info);
		$this->display();
	}
	
	/**
	 *  删除
	 */
	function ap_delete(){
		$id = I("get.id",0,"intval");
		$model=M('ad_position');
		if ($model->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}
	
	//添加
	function ap_post()
	{
		$model=M('ad_position');
		$data['ap_name']=$_POST['ap_name'];
		$data['ap_width']=$_POST['ap_width'];
		$data['ap_height']=$_POST['ap_height'];
		$data['ap_intro']=$_POST['ap_intro'];
		
		if(empty($_POST['id']))
		{
			
			$status=$model->add($data);
		}else
		{
			$status=$model->where(array('id'=>$_POST['id']))->save($data);
		}
		//echo $status;die;
		if($status)
		{
			$this->success("保存成功！", U("ad/positionList"));
		}else
		{
			$this->error("保存失败！");
		}
		
		
	}
	
	//广告列表
	function adList()
	{
		$ap=M('ad_position');
		$ap_list=$ap->order('id desc')->select();
		$ads=$this->ad_model->order('ad_id desc')->select();
		//vv($ap_list);
		$this->assign("ap_list",$ap_list);
		//广告分类
		//$ads=$this->ad_model->order('ad_id desc')->select();
		
		//订单状态
		if(!empty($_REQUEST['team_type']))
		{
			$where['ap_id']=$_REQUEST['team_type'];
			$this->assign("team_type",$_REQUEST['team_type']);
			$_GET['team_type']=$_REQUEST['team_type'];
		}
		
		if(!empty($_REQUEST['keyword']))
		{
			$where['ad_name']=array("like","%".$_REQUEST['keyword']."%");
			$this->assign("keyword",$_REQUEST['keyword']);
			$_GET['keyword']=$_REQUEST['keyword'];
		}
		
		$count=$this->ad_model->where($where)->count();
		$page = $this->page($count, 10);
		$ads = $this->ad_model->where($where)->order('ad_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		//vv($where);
		$this->assign("page", $page->show('Admin'));
		$this->assign("list",$ads);
		$this->assign("count",$count);
		
		
		foreach($ads as $k=>$v)
		{
			$ap_info=$ap->where(array('id'=>$v['ap_id']))->find();
			$ads[$k]['ap_name']=$ap_info['ap_name'];
		}
		$this->assign("ads",$ads);
		$this->display('index');
	}
	
	
	function add(){
		$ap=M('ad_position');
		$ap_list=$ap->order('id desc')->select();
		$ads=$this->ad_model->select();
		//vv($ap_list);
		
		$this->assign("ap_list",$ap_list);
		
		$model=M('ad');
		$_GET['id'];
		$ad_info=$model->where(array('ad_id'=>$_GET['id']))->find();
		$this->assign("type_id",$_GET['type_id']);
		$this->assign("ad_info",$ad_info);
		$this->display();
	}
	
	function add_post(){
		$data['ad_name']=$_POST['ad_name'];
		$data['ad_url']=$_POST['ad_url'];
		$data['start_time']=time($_POST['start_time']);
		$data['end_time']=time($_POST['end_time']);
		$data['thumb']=$_POST['thumb'];
		$data['ap_id']=$_POST['ap_id'];
		
		$model=M('ad');
		
		if(empty($_POST['ad_id']))
		{
			
			$status=$model->add($data);
		}else
		{
			$status=$model->where(array('ad_id'=>$_POST['ad_id']))->save($data);
		}
		//echo $status;die;
		if($status)
		{
			$this->success("保存成功！", U("ad/adList"));
		}else
		{
			$this->error("保存失败！");
		}
		
	}
	
	function edit(){
		$id=I("get.id");
		$ad=$this->ad_model->where("ad_id=$id")->find();
		$this->assign($ad);
		$this->display();
	}
	
	function edit_post(){
		if (IS_POST) {
			if ($this->ad_model->create()) {
				if ($this->ad_model->save()!==false) {
					$this->success("保存成功！", U("ad/index"));
				} else {
					$this->error("保存失败！");
				}
			} else {
				$this->error($this->ad_model->getError());
			}
		}
	}
	
	/**
	 *  删除
	 */
	function delete(){
		$id = I("get.id",0,"intval");
		if ($this->ad_model->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}
	
	function toggle(){
		if(isset($_POST['ids']) && $_GET["display"]){
			$ids = implode(",", $_POST['ids']);
			$data['status']=1;
			if ($this->ad_model->where("ad_id in ($ids)")->save($data)!==false) {
				$this->success("显示成功！");
			} else {
				$this->error("显示失败！");
			}
		}
		if(isset($_POST['ids']) && $_GET["hide"]){
			$ids = implode(",", $_POST['ids']);
			$data['status']=0;
			if ($this->ad_model->where("ad_id in ($ids)")->save($data)!==false) {
				$this->success("隐藏成功！");
			} else {
				$this->error("隐藏失败！");
			}
		}
	}
	
	
	
	
	
}