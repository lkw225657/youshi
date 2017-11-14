<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class MemberController extends AdminbaseController{
	
	function _initialize() {
		parent::_initialize();
	}
	
	function index()
	{
		$this->selectques_list();
	}
	
	public function member_list()
	{
		$model=M('member');
		//性别搜索
		if(!empty($_REQUEST['search_sex']))
		{
			$where['sex']=$_REQUEST['search_sex'];
			$this->assign("search_sex",$_REQUEST['search_sex']);
			$_GET['search_sex']=$_REQUEST['search_sex'];
		}
		
		//是否绑定微信
		if(!empty($_REQUEST['search_wx']))
		{
			if($_REQUEST['search_wx']==1)
			{
				$where['open_id']=array('neq','');
			}else
			{
				$where['open_id']=array('eq','');
			}
			
			$this->assign("search_wx",$_REQUEST['search_wx']);
			$_GET['search_wx']=$_REQUEST['search_wx'];
		}
		
		//用户状态
		if(!empty($_REQUEST['search_status']))
		{
			$where['status']=$_REQUEST['search_status'];
			$this->assign("search_status",$_REQUEST['search_status']);
			$_GET['search_status']=$_REQUEST['search_status'];
		}
		
		//时间
		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])  && !empty($_REQUEST['time_type']))
		{
			//strtotime($_REQUEST['start_time']);
			$time_type=$_REQUEST['time_type'];
			$where["$time_type"] = array(array('gt',strtotime($_REQUEST['start_time'])),array('lt',strtotime($_REQUEST['end_time'])));
			
			$this->assign("start_time",$_REQUEST['start_time']);
			$this->assign("end_time",$_REQUEST['end_time']);
			$this->assign("time_type",$_REQUEST['time_type']);
			$_GET['start_time']=$_REQUEST['start_time'];
			$_GET['end_time']=$_REQUEST['end_time'];
			$_GET['time_type']=$_REQUEST['time_type'];
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
				$where['member_id']=$_REQUEST['keyword'];
			}else if($_REQUEST['search_type']==3)
			{
				$where['mobile|phone']=array('like',"%".$_REQUEST['keyword']."%");
			}

			$this->assign("search_type",$_REQUEST['search_type']);
			$this->assign("keyword",$_REQUEST['keyword']);
			$_GET['search_type']=$_REQUEST['search_type'];
			$_GET['keyword']=$_REQUEST['keyword'];
		}

		$count=$model->where($where)->count();
		$page = $this->page($count, 10);
		$users = $model->where($where)->order('member_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		//vv($users[0]);
		$member_enrol=M('member_enrol');
		foreach($users as $kk=>$vv)
		{
			//推荐
			if($vv['yaoqing'])
			{
				$mm=$model->field('name')->where("member_id=$vv[yaoqing]")->find();
			$users[$kk]['yaoqing']=$mm['name'];
			}else{
				$users[$kk]['yaoqing']='无';
			}
			//会员
			if($vv['type']=='1')
			{
				$users[$kk]['type']='普通';
			}else{
				$users[$kk]['type']='会员';
			}
			//报名次数
			$users[$kk]['baoming']=$member_enrol->where("member_id=$vv[member_id]")->count();
			//报名成功次数
			$users[$kk]['baoming_success']=$member_enrol->where("member_id=$vv[member_id] AND baoming=1 ")->count();
			//参与次数
			$users[$kk]['canyu']=$member_enrol->where("member_id=$vv[member_id] AND baoming=1 AND enrol_start_time > 0")->count();
			//完成次数
			$users[$kk]['wancheng']=$member_enrol->where("member_id=$vv[member_id] AND baoming=1 AND enrol_end_time > 0")->count();
		
			$users[$kk]['age']=birthday_age(date("Y-m-d",$vv['birthday']));
			if($vv['area_name'])
			{
				$area_name=mb_substr(trim($vv['area_name']),0,3,'utf-8');
				if($area_name=='上海市'||$area_name=='重庆市'||$area_name=='天津市'||$area_name=='北京市')
				{
					$users[$kk]['area_name']=$area_name.' '.$vv['area_name'];
				}
			}
		}
		$this->assign("page", $page->show('Admin'));
		$this->assign("list",$users);
		$this->assign("count",$count);
		$this->assign("formget",$_GET);
		$this->display();
	}
	
	//添加&修改选择题
	public function member_info()
	{
		//性别表（sexs）
		$sexs=sexs();		//获取标签
		$this->assign("sexs",$sexs);
		
		//性别表（sexs）
		$sexs=sexs();		//获取标签
		$this->assign("sexs",$sexs);
		
		//学历
		$educa=educa();		
		$this->assign("educa",$educa);
		
		//婚姻状况
		$marriage=marriage();		
		$this->assign("marriage",$marriage);
		
		//职业
		$profession=profession();		
		$this->assign("profession",$profession);
		
		//工作环境
		$work=work();		
		$this->assign("work",$work);
		
		//血型
		$blood=blood();		
		$this->assign("blood",$blood);
		
		//住房类型
		$housing=housing();		
		$this->assign("housing",$housing);
		
		$id=$_REQUEST['id'];
		$model=M('member');
		$where['member_id']=$id;
		$member_info=$model->where($where)->find();
		
		$member_info['child_data']=(array)json_decode($member_info['child_data']);
		//vv($member_info['child_data']);
		$member_info['birthday']=date('Y-m-d H:i:s',$member_info['birthday']);	//开始时间
		$member_info['area_id']=explode(",",$member_info['area_id']); 		//地区
		
		//vv($selectques_info);
		$this->assign("row",$member_info);
		
		$this->display();
	}
	
	//保存&修改选择题
	public function member_save()
	{
		$model=M('member');
		if (IS_POST) 
		{
			$array=I("post.post");
			
			$array['birthday']=strtotime($_POST['birthday']);		//出生日期
			
			//省市区
			$array['area_id']=implode(",",$_POST['area_id']);		//省市区
			
			//子女信息json格式
			foreach($_POST['child_name'] as $k=>$v)
			{
				if(!empty($v))
				{
					$demand_other[$k]['child_name']=$v;
					$demand_other[$k]['child_sex']=$_POST['child_sex'][$k];
					$demand_other[$k]['child_birthday']=$_POST['child_birthday'][$k];
				}
				
			}
			$array['child_num']=count($_POST['child_name']);
			$array['child_data']=json_encode($demand_other);
			
			//vv($array);die;
			if(empty($_POST['id']))
			{
				$array['add_time']=time();
				$result=$model->add($array);
				if($result)
				$this->admin_log($_SESSION['ADMIN_ID'],'添加会员,'.$array['name']);
			}else
			{
				$result=$model->where(array('member_id'=>$_POST['id']))->save($array);
				if($result)
				$this->admin_log($_SESSION['ADMIN_ID'],'修改会员,'.$array['name']);
			}
			
			if ($result) {
				$this->success("操作成功！");
			} else {
				$this->error("操作失败！");
			}
			//print_r($article);
		}
	}
	
	//删除
	function member_del()
	{
		$model=M('member');
		if(isset($_GET['tid'])){
			$tid = intval(I("get.tid"));
			//$data['status']=0;
			if ($model->where("member_id=$tid")->delete()) {
				$this->admin_log($_SESSION['ADMIN_ID'],'删除会员,'.$tid);
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}
		if(isset($_REQUEST['ids'])){
			$tids=join(",",$_REQUEST['ids']);
			//$data['status']=0;
			if ($model->where("member_id in ($tids)")->delete()) {
				$this->admin_log($_SESSION['ADMIN_ID'],'删除会员,'.$tids);
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}
	}
	
	//用户报名列表
	public function enrol_list()
	{
		$model=M('member_enrol');
		//项目类型搜索
		if(!empty($_REQUEST['project_type']))
		{
			$where['project_type']=$_REQUEST['project_type'];
			$this->assign("project_type",$_REQUEST['project_type']);
			$_GET['project_type']=$_REQUEST['project_type'];
		}
		
		//是否绑定微信
		if(!empty($_REQUEST['search_wx']))
		{
			if($_REQUEST['search_wx']==1)
			{
				$where['open_id']=array('neq','');
			}else
			{
				$where['open_id']=array('eq','');
			}
			
			$this->assign("search_wx",$_REQUEST['search_wx']);
			$_GET['search_wx']=$_REQUEST['search_wx'];
		}
		
		//用户状态
		if(!empty($_REQUEST['search_status']))
		{
			$where['status']=$_REQUEST['search_status'];
			$this->assign("search_status",$_REQUEST['search_status']);
			$_GET['search_status']=$_REQUEST['search_status'];
		}
		
		//时间
		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])  && !empty($_REQUEST['time_type']))
		{
			//strtotime($_REQUEST['start_time']);
			$time_type=$_REQUEST['time_type'];
			$where["$time_type"] = array(array('gt',strtotime($_REQUEST['start_time'])),array('lt',strtotime($_REQUEST['end_time'])));
			
			$this->assign("start_time",$_REQUEST['start_time']);
			$this->assign("end_time",$_REQUEST['end_time']);
			$this->assign("time_type",$_REQUEST['time_type']);
			$_GET['start_time']=$_REQUEST['start_time'];
			$_GET['end_time']=$_REQUEST['end_time'];
			$_GET['time_type']=$_REQUEST['time_type'];
		}
		
		//搜索keyword
		if(!empty($_REQUEST['keyword']) && !empty($_REQUEST['search_type']))
		{
			$_REQUEST['search_type'];
			if($_REQUEST['search_type']==1)
			{
				$where['project_no']=array('like',"%".$_REQUEST['keyword']."%");
			}else if($_REQUEST['search_type']==2)
			{
				$where['project_name']=array('like',"%".$_REQUEST['keyword']."%");
			}else if($_REQUEST['search_type']==3)
			{
				$where['member_id']=$_REQUEST['keyword'];
			}else if($_REQUEST['search_type']==3)
			{
				$where['member_name']=array('like',"%".$_REQUEST['keyword']."%");
			}

			$this->assign("search_type",$_REQUEST['search_type']);
			$this->assign("keyword",$_REQUEST['keyword']);
			$_GET['search_type']=$_REQUEST['search_type'];
			$_GET['keyword']=$_REQUEST['keyword'];
		}

		$count=$model->where($where)->count();
		$page = $this->page($count, 10);
		$users = $model->where($where)->order('member_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		//vv($users[0]);
		
		foreach($users as $kk=>$vv)
		{
			$users[$kk]['age']=birthday_age(date("Y-m-d",$vv['birthday']));
		}
		$this->assign("page", $page->show('Admin'));
		$this->assign("list",$users);
		$this->assign("count",$count);
		$this->assign("formget",$_GET);
		$this->display();
	}

	public function member_detail()
	{
		$model=M('member');
		$where['member_id']=$_GET['id'];
		$member_info=$model->where($where)->find();
		$member_info['age']=trim(time()-$member_info['birthday'],'-');
		$member_info['age']=(int)($member_info['age']/(3600*24)/365);
		$member_info['child_data']=(array)json_decode($member_info['child_data']);
		$member_info['birthday']=date('Y-m-d H:i',$member_info['birthday']);	//开始时间
		$member_info['add_time']=date('Y-m-d H:i',$member_info['add_time']);
		$member_info['area_id']=explode(",",$member_info['area_id']); 		//地区
		if($member_info['yaoqing'])
		$mm=$model->field('name')->where("member_id=$member_info[yaoqing]")->find();
		$member_info['yaoqing']=$mm['name'];
		if($member_info['open_id'])
			$member_info['is_weixin']='已绑定';
		else
			$member_info['is_weixin']='未绑定';
		if($member_info['type']=='1')
			$member_info['type']='普通用户';
		else
			$member_info['type']='会员';
		if($member_info['state']=='0')
			$member_info['state']='禁用';
		elseif($member_info['state']=='1')
			$member_info['state']='正常';
		else
			$member_info['state']='未认证';
		//查找会员参加的项目
		$member_enrol=M('member_enrol');
		$project=M('project');

		$enrol_list=$member_enrol->where($where)->select();
		foreach($enrol_list as $k=>$v)
		{
			$project_info=$project->field('title,project_no')->where("id=$v[project_id]")->find();
			$v['project_name']=$project_info['title'];
			$v['project_no']=$project_info['project_no'];
			if($v['enrol_start_time'])
			{

			}else{
				unset($v['enrol_start_time']);
			}

			if($v['project_type']=='1')
				$type1[]=$v;
			if($v['project_type']=='2')
				$type2[]=$v;
			if($v['project_type']=='3')
				$type3[]=$v;
			if($v['project_type']=='4')
				$type4[]=$v;
		}

		//会员的积分记录
		$jifen=M('jifen_log');
		$jifen_list=$jifen->where($where)->select();
		$this->assign("type1",$type1);
		$this->assign("type2",$type2);
		$this->assign("type3",$type3);
		$this->assign("type4",$type4);
		$this->assign("jifen_list",$jifen_list);
		$this->assign("row",$member_info);
		//print_r($jifen_list);
		$this->display();
	} 



//报酬申请列表
	function apply_list()
	{
		$model=M('member_pay');
		$member=M('member');
		$project=M('project');

	$where['pay_state']=0;
		//时间
		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])  && !empty($_REQUEST['time_type']))
		{
			//strtotime($_REQUEST['start_time']);
			$time_type=$_REQUEST['time_type'];
			$where["apply_time"] = array(array('gt',strtotime($_REQUEST['start_time'])),array('lt',strtotime($_REQUEST['end_time'])));
			
			$this->assign("start_time",$_REQUEST['start_time']);
			$this->assign("end_time",$_REQUEST['end_time']);
			
		}
		//搜索keyword
		if(!empty($_REQUEST['keyword']) && !empty($_REQUEST['search_type']))
		{
			
			 if($_REQUEST['search_type']==2)
			{
				$where['project_name']=array('like',"%".$_REQUEST['keyword']."%");
			}else if($_REQUEST['search_type']==3)
			{
				$where['member_id']=$_REQUEST['keyword'];
			}else if($_REQUEST['search_type']==3)
			{
				$where['member_name']=array('like',"%".$_REQUEST['keyword']."%");
			}

			$this->assign("search_type",$_REQUEST['search_type']);
			$this->assign("keyword",$_REQUEST['keyword']);
	
		}
		$count=$model->where($where)->count();
		$page = $this->page($count, 10);
		$join = C('DB_PREFIX').'member as b on a.member_id =b.member_id ';
		$join1=C('DB_PREFIX').'project as p on a.project_id =p.id';
		$users = $model->field('a.*,b.name,b.sex,b.mobile,b.member_id,p.title')->alias("a")->join($join)->join($join1)->where($where)->order('a.id desc')->limit($page->firstRow . ',' . $page->listRows)->select();

		foreach($users as $k=>$v)
		{
			if($v['pay_state']=='1')
			{
				$users[$k]['pay_state']='已支付';
			}
			if($v['pay_state']=='0')
			{
				$users[$k]['pay_state']='未支付';
			}
		}

		$this->assign("page", $page->show('Admin'));
		$this->assign("list",$users);
		$this->assign("count",$count);
		$this->assign("formget",$_GET);
		
		$this->display();
	}

	//发放过的红包
	function apply_log()
	{
		$model=M('member_pay');
		$member=M('member');
		$project=M('project');
		$where['pay_state']=1;
		//时间
		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])  && !empty($_REQUEST['time_type']))
		{
			//strtotime($_REQUEST['start_time']);
			$time_type=$_REQUEST['time_type'];
			$where["apply_time"] = array(array('gt',strtotime($_REQUEST['start_time'])),array('lt',strtotime($_REQUEST['end_time'])));
			
			$this->assign("start_time",$_REQUEST['start_time']);
			$this->assign("end_time",$_REQUEST['end_time']);
			
		}
		//搜索keyword
		if(!empty($_REQUEST['keyword']) && !empty($_REQUEST['search_type']))
		{
			
			 if($_REQUEST['search_type']==2)
			{
				$where['project_name']=array('like',"%".$_REQUEST['keyword']."%");
			}else if($_REQUEST['search_type']==3)
			{
				$where['member_id']=$_REQUEST['keyword'];
			}else if($_REQUEST['search_type']==3)
			{
				$where['member_name']=array('like',"%".$_REQUEST['keyword']."%");
			}

			$this->assign("search_type",$_REQUEST['search_type']);
			$this->assign("keyword",$_REQUEST['keyword']);
	
		}
		$count=$model->where($where)->count();
		$page = $this->page($count, 10);
		$join = C('DB_PREFIX').'member as b on a.member_id =b.member_id ';
		$join1=C('DB_PREFIX').'project as p on a.project_id =p.id';
		$users = $model->field('a.*,b.name,b.sex,b.mobile,b.member_id,p.title')->alias("a")->join($join)->join($join1)->where($where)->order('a.id desc')->limit($page->firstRow . ',' . $page->listRows)->select();

		foreach($users as $k=>$v)
		{
			if($v['pay_state']=='1')
			{
				$users[$k]['pay_state']='已支付';
			}
			if($v['pay_state']=='0')
			{
				$users[$k]['pay_state']='未支付';
			}
		}

		$this->assign("page", $page->show('Admin'));
		$this->assign("list",$users);
		$this->assign("count",$count);
		$this->assign("formget",$_GET);
		
		$this->display();
	}

	//发送红包
	function ajax_send()
	{
		//引入文件
		vendor("weixin.wxhongbao");
		//实例化红包类
		$wxhongbao=new \WXHongBao();
		//请求发红包的id
		$log_id=$_REQUEST['id'];
		//
		 $member_pay=M('member_pay');
		 $member=M('member');

		 $where_p['id']=$log_id;
		 $pay_info=$member_pay->where($where_p)->find();
		 $where_m['member_id']=$pay_info['member_id'];
		 $member_info=$member->where($where_m)->find();
		 //需要发放的openid 金额
		 $user_openid=$member_info['open_id'];
		 $pay_money=$pay_info['apply_money'];
		 $wxhongbao->newhb($user_openid, $pay_money*100);
		 if($user_openid&&$pay_info)
		 {
		 	$wxhongbao->setActName("项目酬金");
                $wxhongbao->setWishing($pay_info['project_name'] . " 项目酬金,欢迎再次参与");
                $wxhongbao->setRemark($pay_info['project_name']);

                if (!$wxhongbao->send()) {
                	// print_r($wxhongbao);die;
                	echo $wxhongbao->error();
                    echo json_encode(array('result'=> 'error4', 'reason' => $wxhongbao->error()));
                    exit;
                }else{
                	//发放成功 之后的操作
                	//记录发放时间，发放人，发放状态修改
                	$user=M('users');
                	$user_info = $user->where("id=$_SESSION[ADMIN_ID]")->find();
                	$update_array['pay_admin_name']=$user_info['user_login'];
                	$update_array['pay_time']=time();
                	$update_array['pay_state']=1;
                	$result=$member_pay->where($where_p)->save($update_array);
                	if($result)
                	{
                		echo '发放成功';	
                	}

                		
                }
		 }
		   
		
	}

	
}