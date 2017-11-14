<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class UserController extends AdminbaseController{

	protected $users_model,$role_model;

	public function _initialize() {
		parent::_initialize();
		$this->users_model = D("Common/Users");
		$this->role_model = D("Common/Role");
	}

	// 管理员列表
	public function index(){
		$where = array("user_type"=>1,"role_deep"=>'1');
		/**搜索条件**/
		$user_login = I('request.user_login');
		$user_email = trim(I('request.user_email'));
		if($user_login){
			$where['user_login'] = array('like',"%$user_login%");
		}
		
		if($user_email){
			$where['user_email'] = array('like',"%$user_email%");;
		}
		
		$count=$this->users_model->where($where)->count();
		$page = $this->page($count, 20);
        $users = $this->users_model
            ->where($where)
            ->order("create_time DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
		$roles_src=$this->role_model->select();
		$roles=array();
		foreach ($roles_src as $r){
			$roleid=$r['id'];
			$roles["$roleid"]=$r;
		}
		$this->assign("page", $page->show('Admin'));
		$this->assign("roles",$roles);
		$this->assign("users",$users);
		$this->assign("role_deep",$_SESSION['ROLE_DEEP']);
		$this->display();
	}

	// 管理员添加
	public function add(){
		$roles=$this->role_model->where(array('status' => 1))->order("id DESC")->select();
		$this->assign("roles",$roles);
		$this->display();
	}

	// 管理员添加提交
	public function add_post(){
		if(IS_POST){
			if(!empty($_POST['role_id']) && is_array($_POST['role_id'])){
				$role_ids=$_POST['role_id'];
				unset($_POST['role_id']);
				$_POST['role']=implode(",",$role_ids); 		//;
				if ($this->users_model->create()!==false) {
					$result=$this->users_model->add();
					if ($result!==false) {
						$role_user_model=M("RoleUser");
						foreach ($role_ids as $role_id){
							if(sp_get_current_admin_id() != 1 && $role_id == 1){
								$this->error("为了网站的安全，非网站创建者不可创建超级管理员！");
							}
							$role_user_model->add(array("role_id"=>$role_id,"user_id"=>$result));
						}
						$this->success("添加成功！", U("user/index"));
					} else {
						$this->error("添加失败！");
					}
				} else {
					$this->error($this->users_model->getError());
				}
			}else{
				$this->error("请为此用户指定角色！");
			}

		}
	}

	// 管理员编辑
	public function edit(){
	    $id = I('get.id',0,'intval');
		$roles=$this->role_model->where(array('status' => 1))->order("id DESC")->select();
		$this->assign("roles",$roles);
		$role_user_model=M("RoleUser");
		$role_ids=$role_user_model->where(array("user_id"=>$id))->getField("role_id",true);
		$this->assign("role_ids",$role_ids);

		$user=$this->users_model->where(array("id"=>$id))->find();
		$this->assign($user);
		$this->display();
	}

	// 管理员编辑提交
	public function edit_post(){
		if (IS_POST) {
			if(!empty($_POST['role_id']) && is_array($_POST['role_id'])){
				if(empty($_POST['user_pass'])){
					unset($_POST['user_pass']);
				}
				$role_ids = I('post.role_id/a');
				unset($_POST['role_id']);
				$_POST['role']=implode(",",$role_ids); 		//;
				if ($this->users_model->create()!==false) {
					$result=$this->users_model->save();
					if ($result!==false) {
						$uid = I('post.id',0,'intval');
						$role_user_model=M("RoleUser");
						$role_user_model->where(array("user_id"=>$uid))->delete();
						foreach ($role_ids as $role_id){
							if(sp_get_current_admin_id() != 1 && $role_id == 1){
								$this->error("为了网站的安全，非网站创建者不可创建超级管理员！");
							}
							$role_user_model->add(array("role_id"=>$role_id,"user_id"=>$uid));
						}
						$this->success("保存成功！");
					} else {
						$this->error("保存失败！");
					}
				} else {
					$this->error($this->users_model->getError());
				}
			}else{
				$this->error("请为此用户指定角色！");
			}

		}
	}

	// 管理员删除
	public function delete(){
	    $id = I('get.id',0,'intval');
		if($id==1){
			$this->error("最高管理员不能删除！");
		}

		if ($this->users_model->delete($id)!==false) {
			M("RoleUser")->where(array("user_id"=>$id))->delete();
			$this->admin_log($_SESSION['ADMIN_ID'],'删除管理员,删除的管理员id为'.$id);
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}

	// 管理员个人信息修改
	public function userinfo(){
		$id=sp_get_current_admin_id();
		$user=$this->users_model->where(array("id"=>$id))->find();
		$this->assign($user);
		$this->display();
	}

	// 管理员个人信息修改提交
	public function userinfo_post(){
		if (IS_POST) {
			$_POST['id']=sp_get_current_admin_id();
			$create_result=$this->users_model
			->field("id,user_nicename,sex,birthday,user_url,signature")
			->create();
			if ($create_result!==false) {
				if ($this->users_model->save()!==false) {
					$this->admin_log($_SESSION['ADMIN_ID'],'管理员个人信息修改,id为'.$_POST['id']);
					$this->success("保存成功！");
				} else {
					$this->error("保存失败！");
				}
			} else {
				$this->error($this->users_model->getError());
			}
		}
	}

	// 停用管理员
    public function ban(){
        $id = I('get.id',0,'intval');
    	if (!empty($id)) {
    		$result = $this->users_model->where(array("id"=>$id,"user_type"=>1))->setField('user_status','0');
    		if ($result!==false) {
    			$this->admin_log($_SESSION['ADMIN_ID'],'管理员停用,停用的管理员id为'.$id);
    			$this->success("管理员停用成功！", U("user/index"));
    		} else {
    			$this->error('管理员停用失败！');
    		}
    	} else {
    		$this->error('数据传入失败！');
    	}
    }

    // 启用管理员
    public function cancelban(){
    	$id = I('get.id',0,'intval');
    	if (!empty($id)) {
    		$result = $this->users_model->where(array("id"=>$id,"user_type"=>1))->setField('user_status','1');
    		if ($result!==false) {
    			$this->success("管理员启用成功！", U("user/index"));
    		} else {
    			$this->error('管理员启用失败！');
    		}
    	} else {
    		$this->error('数据传入失败！');
    	}
    }

    //管理员操作日志
    public function log_list()
    {
    	$log=M('admin_log');
    	$count=$log->count();
    	$page = $this->page($count, 20);
    	// $log_list=$log->join('v_users on v_users.id=v_admin_log.admin_id')->select();
    	$log_list = $log->join('v_users on v_users.id=v_admin_log.admin_id')->order('log_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
    	foreach($log_list as $k=>$v)
    	{
    		$log_list[$k]['time']=date('Y-m-d H:i:s',$v['time']);
    	}
    	
		
		$this->assign("page", $page->show('Admin'));
		$this->assign("log_list",$log_list);
		$this->assign("count",$count);
		$this->display();
    }



}