<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class ProjectController extends AdminbaseController{
	
	function _initialize() {
		parent::_initialize();
	}
	
	public function erweima($url)
	{
		if($_POST['url'])
		{
			$url=$_POST['url'];
		}
		die($this->geterweima($url,time()));
		// return $this->geterweima($url,time());
	}
	
	//产品测试
	public function goodstest()
	{
		$this->project_list(1,'goodstest');
	}
	//在线调查
	public function survey()
	{
		$this->project_list(2,'goodstest');
	}
	//试用品派发
	public function grant()
	{
		$this->project_list(3,'goodstest');
	}
	
	//长期项目组
	public function longterm()
	{
		$this->project_list(4,'goodstest');
	}
	//审批
	public function check_project()
	{
		$session_admin_id=session('ADMIN_ID');
		//是否有修改项目状态的权限
		$name='admin/project/change_state';
		if(sp_auth_check($session_admin_id,$name))
		{
			$this->assign('auth_check','1');
		}
		$class=M('class');
		// $typedata=$this->typedata($type);
		// $this->assign("typedata",$typedata);
		//项目分类
		$class_list=$class->where(array('parent_id'=>$typedata['class_id']))->select();
		$this->assign("class_list",$class_list);
		
		//隶属客户
		$kehu_list=$class->where(array('parent_id'=>1))->select();
		$this->assign("kehu_list",$kehu_list);
		//项目状态 产品测试
		
		$this->assign("project_state",$project_state);

		$model=M('project');
		
		//项目分类
		if(!empty($_REQUEST['search_class']))
		{
			$where['class']=$_REQUEST['search_class'];
			$this->assign("search_class",$_REQUEST['search_class']);
			$_GET['search_class']=$_REQUEST['search_class'];
		}
		//项目审核
		if(!empty($_REQUEST['search_project_status']))
		{
			$where['project_status']=$_REQUEST['search_project_status'];
			if($_REQUEST['search_project_status']=='wait')
			{
				$where['project_status']=0;
			}
			$this->assign("project_status",$_REQUEST['search_project_status']);
			$_GET['project_status']=$_REQUEST['search_project_status'];
		}
		//项目状态
		if(!empty($_REQUEST['search_project_state']))
		{
			if($_REQUEST['search_project_state'])
			$where['state']=trim($_REQUEST['search_project_state']);
			
			$this->assign("search_project_state",$_REQUEST['search_project_state']);
			$_GET['search_project_state']=$_REQUEST['search_project_state'];
		}
		
		//隶属客户
		if(!empty($_REQUEST['search_kehu']))
		{
			$where['kehu']=$_REQUEST['search_kehu'];
			$this->assign("search_kehu",$_REQUEST['search_kehu']);
			$_GET['search_kehu']=$_REQUEST['search_kehu'];
		}
		
		//时间
		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])  && !empty($_REQUEST['time_type']))
		{
			//strtotime($_REQUEST['start_time']);
			$time_type=$_REQUEST['time_type'];
			$where["$time_type"] = array(array('gt',strtotime($_REQUEST['start_time'])),array('lt',strtotime($_REQUEST['end_time'])));
			$order1="$time_type asc";
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
		
		$where['project_status']='0';		//大分类
		$count=$model->where($where)->count();
		$page = $this->page($count, 10);
		$user=M('users');
		$member_enrol=M('member_enrol');
	
		$users = $model->where($where)->order('id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		foreach($users as $k=>$v)
		{
			$user_info=$user->where(array('id'=>$v['user_id']))->find();
			//vv($user_info);
			$users[$k]['user_name']=$user_info['user_nicename'];


			//获取报名人数
			$users[$k]['baoming']=$member_enrol->where(array('project_id'=>$v['id']))->count();
			//获取报名成功人数
			$users[$k]['baoming_ok']=$member_enrol->where(array('project_id'=>$v['id'],'baoming'=>1))->count();

		}
		$this->assign("page", $page->show('Admin'));
		$this->assign("list",$users);
		$this->assign("count",$count);
		$this->assign("formget",$_GET);
		$this->display();
	}

	//查看项目详情
	public function check_project_info(){
		//项目详情
		$project_id=$_GET['id'];
		$project=M('project');
		$project_info=$project->where(array("id"=>$project_id))->find();

		if(!empty($project_info))
		{
			$project_info['add_time']=date('Y-m-d H:i',$project_info['add_time']);	
			$project_info['start_time']=date('Y-m-d H:i',$project_info['start_time']);	//开始时间
			$project_info['end_time']=date('Y-m-d H:i',$project_info['end_time']);		//结束时间
			$project_info['project_start_time']=date('Y-m-d H:i:s',$project_info['project_start_time']);	//开始时间2
			$project_info['project_end_time']=date('Y-m-d H:i:s',$project_info['project_end_time']);		//结束时间2	
			$project_info['demand_other']=(array)json_decode($project_info['demand_other']);
			$project_info['price']=intval($project_info['price']); 		//格式化为整数
			if($project_info['project_status']=='1')
			{
				$project_info['project_status']='审核通过';
			}
			if($project_info['project_status']=='2')
			{
				$project_info['project_status']='审核不通过';
			}
			if($project_info['project_status']=='0')
			{
				$project_info['project_status']='待审核';
			}
			if($project_info['type']=='1')
			{
				$project_info['type']='产品测试';
			}
			if($project_info['type']=='2')
			{
				$project_info['type']='在线调查';
			}
			if($project_info['type']=='3')
			{
				$project_info['type']='试用品派发';
			}
			if($project_info['type']=='4')
			{
				$project_info['type']='长期项目组';
			}

			if($project_info['longterm']>0)
			{
				$pp=$project->field('title')->where(array('id'=>$project_info['longterm']))->find();
				$project_info['longterm']=$pp['title'];
			}
			$project_info['project_desc']=html_entity_decode($project_info['project_desc']);
			$project_info['demand']=html_entity_decode($project_info['demand']);
			//m
			$member=M('member_enrol');
			$baoming=$member->where(array('project_id'=>$project_id))->count();
			$tongguo=$member->where(array('project_id'=>$project_id,'baoming'=>'1'))->count();
			//$canyu=$member->where(array('project_id'=>$project_id,'baoming'=>'1'))->count();
			
			//甄别问卷
			$ques=M('ques');
		
			$id = $project_id;
			$type = 1;


			$ques_list_zb=$ques->where(array('project_id'=>$id,'type'=>$type))->select();
			
			$zimu=array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t');
			foreach($ques_list_zb as $kk=>$vv)
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
				$ques_list_zb[$kk]['answer_str']=implode(' | ',$answer_arr);
			}
		
			
		//现场筛选
			$id = $project_id;
			$type = 2;
			$ques_list_sx=$ques->where(array('project_id'=>$id,'type'=>$type))->select();
			foreach($ques_list_sx as $kk=>$vv)
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
				$ques_list_sx[$kk]['answer_str']=implode(' | ',$answer_arr);
			}

			//调查问卷
			$id = $project_id;
			$type = 5;
			$ques_list_dc=$ques->where(array('project_id'=>$id,'type'=>$type))->select();
			foreach($ques_list_dc as $kk=>$vv)
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
				$ques_list_dc[$kk]['answer_str']=implode(' | ',$answer_arr);
			}


			//流程
			$process=M('project_process');

			$process_list=$process->where(array('project_id'=>$id))->select();
			foreach($process_list as $k=>$v)
			{
				$process_type=strstr($v['start_date'],'-');
				$v['start_time']=explode(":",$v['start_time']); 		//年龄范围转换为数组
				$v['end_time']=explode(":",$v['end_time']); 		//年龄范围转换为数组
				
				
				$new_list[$v['type']][]=$v;
			}
			if($process_type)
			{
				$process_type='日期';
			}
			else{
				$process_type='天数';
			}
			$this->assign("process_type",$process_type);
			$this->assign("new_list",$new_list);
			
			$f_renwu=f_renwu();
			$this->assign("f_renwu",$f_renwu);


			$this->assign("ques_list_dc",$ques_list_dc);
			$this->assign("ques_list_sx",$ques_list_sx);
			$this->assign("ques_list_zb",$ques_list_zb);
			$this->assign('baoming',$baoming);
			$this->assign('tongguo',$tongguo);
			$this->assign('project_info',$project_info);
			$this->display();
		}
	}

	
	public function project_list($type,$view)
	{
		$session_admin_id=session('ADMIN_ID');
		//是否有修改项目状态的权限
		$name='admin/project/change_state';
		if(sp_auth_check($session_admin_id,$name))
		{
			$this->assign('auth_check','1');
		}
		$class=M('class');
		$typedata=$this->typedata($type);
		$this->assign("typedata",$typedata);
		//项目分类
		$class_list=$class->where(array('parent_id'=>$typedata['class_id']))->select();
		$this->assign("class_list",$class_list);
		
		//隶属客户
		$kehu_list=$class->where(array('parent_id'=>1))->select();
		$this->assign("kehu_list",$kehu_list);
		//项目状态 产品测试
		if($type=='1')
		{
			$project_state=project_state();
		}
		//在线调查状态
		if($type=='2')
		{
			$project_state=project_state_1();
		}
		//试用品
		if($type=='3')
		{
			$project_state=project_state_2();
		}
		//长期
		if($type=='4')
		{
			$project_state=project_state_3();
		}
		$this->assign("project_state",$project_state);

		$model=M('project');
		
		//项目分类
		if(!empty($_REQUEST['search_class']))
		{
			$where['class']=$_REQUEST['search_class'];
			$this->assign("search_class",$_REQUEST['search_class']);
			$_GET['search_class']=$_REQUEST['search_class'];
		}
		//项目审核
		if(!empty($_REQUEST['search_project_status']))
		{
			$where['project_status']=$_REQUEST['search_project_status'];
			if($_REQUEST['search_project_status']=='wait')
			{
				$where['project_status']=0;
			}
			$this->assign("project_status",$_REQUEST['search_project_status']);
			$_GET['project_status']=$_REQUEST['search_project_status'];
		}
		//项目状态
		if(!empty($_REQUEST['search_project_state']))
		{
			if($_REQUEST['search_project_state'])
			$where['state']=trim($_REQUEST['search_project_state']);
			
			$this->assign("search_project_state",$_REQUEST['search_project_state']);
			$_GET['search_project_state']=$_REQUEST['search_project_state'];
		}
		
		//隶属客户
		if(!empty($_REQUEST['search_kehu']))
		{
			$where['kehu']=$_REQUEST['search_kehu'];
			$this->assign("search_kehu",$_REQUEST['search_kehu']);
			$_GET['search_kehu']=$_REQUEST['search_kehu'];
		}
		
		//时间
		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])  && !empty($_REQUEST['time_type']))
		{
			//strtotime($_REQUEST['start_time']);
			$time_type=$_REQUEST['time_type'];
			$where["$time_type"] = array(array('gt',strtotime($_REQUEST['start_time'])),array('lt',strtotime($_REQUEST['end_time'])));
			$order1="$time_type asc";
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
		
		$where['type']=$type;		//大分类
		$count=$model->where($where)->count();
		$page = $this->page($count, 10);
		$user=M('users');
		$member_enrol=M('member_enrol');
	
		$users = $model->where($where)->order('id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		foreach($users as $k=>$v)
		{
			$user_info=$user->where(array('id'=>$v['user_id']))->find();
			//vv($user_info);
			$users[$k]['user_name']=$user_info['user_nicename'];
			
			//获取报名人数
			$users[$k]['baoming']=$member_enrol->where(array('project_id'=>$v['id']))->count();
			//获取报名成功人数
			$users[$k]['baoming_ok']=$member_enrol->where(array('project_id'=>$v['id'],'baoming'=>1))->count();
			//参与人数
			$users[$k]['canyu']=$member_enrol->where("project_id=$v[id] AND state ='已完成'")->count();
			if($v['longterm'])
			{
				$ll=$model->field('title')->where("id=$v[longterm]")->find();
				$users[$k]['longterm']=$ll['title'];
			}else{
				$users[$k]['longterm']='暂无';
			}
			
			
		}
		$this->assign("page", $page->show('Admin'));
		$this->assign("list",$users);
		$this->assign("count",$count);
		$this->assign("formget",$_GET);
		$this->assign("type",$type);
		$this->display($view);
	}
	
	//添加&修改产品测试
	public function project_info()
	{
		//项目类型
		$class=M('class');
		$user=M('users');
		$project=M('project');
		$project_type_list=$class->where(array('parent_id'=>2))->select();
		//vv($project_type_list);
		$this->assign("project_type_list",$project_type_list);
		
		//隶属客户
		$kehu_list=$class->where(array('parent_id'=>1))->select();
		//vv($kehu_list);
		$this->assign("kehu_list",$kehu_list);
		
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
		
		//个人月收入
		$f_demand_month_income=f_demand_month_income();		
		$this->assign("f_demand_month_income",$f_demand_month_income);
		
		//家庭月收入
		$f_demand_family_income=f_demand_family_income();		
		$this->assign("f_demand_family_income",$f_demand_family_income);
		
		//项目状态分类
	  	$project_state=project_state();
		$this->assign("project_state",$project_state);
		//vv($project_state);
		
		//负责人
		$jingli_list=$user->where(array('role'=>array('in','4')))->select();
		$this->assign("jingli_list",$jingli_list);
		//兼职
		$jianzhi_list=$user->where(array('role'=>3))->select();
		$this->assign("jianzhi_list",$jianzhi_list);
		//研究员
		$yanjiu_list=$user->where(array('role'=>2))->select();
		$this->assign("yanjiu_list",$yanjiu_list);
		//项目经理
		$jingli_list=$user->where(array('role'=>4))->select();
		$this->assign("jingli_list",$jingli_list);
		
		//所有项目组
		$project_list=$project->where(array('type'=>4))->select();
		//vv($project_list[0]);
		$this->assign("project_list",$project_list);
		//项目表
		$id=$_REQUEST['id'];
		$model=M('project');
		$where['id']=$id;
		$project_info=$model->where($where)->find();
		//其他需求json格式
		if(!empty($project_info))
		{
			$project_info['demand_age']=explode(",",$project_info['demand_age']); 		//年龄范围转换为数组
			$project_info['demand_height']=explode("-",$project_info['demand_height']); 		//年龄范围转换为数组
			$project_info['demand_weight']=explode("-",$project_info['demand_weight']); 		//年龄范围转换为数组
			//$project_info['demand_month_income']=explode("-",$project_info['demand_month_income']); 		//年龄范围转换为数组
			//$project_info['demand_family_income']=explode("-",$project_info['demand_family_income']); 		//年龄范围转换为数组
			$project_info['start_time']=date('Y-m-d H:i:s',$project_info['start_time']);	//开始时间
			$project_info['end_time']=date('Y-m-d H:i:s',$project_info['end_time']);		//结束时间
			$project_info['project_start_time']=date('Y-m-d H:i:s',$project_info['project_start_time']);	//开始时间2
			$project_info['project_end_time']=date('Y-m-d H:i:s',$project_info['project_end_time']);		//结束时间2
			//$qiye_info['erweima_img']=json_decode($team_info['erweima']);
			$project_info['demand_other']=(array)json_decode($project_info['demand_other']);
			$project_info['demand_area_id']=explode(",",$project_info['demand_area_id']); 		//年龄范围转换为数组
			$project_info['price']=intval($project_info['price']); 		//格式化为整数
		}
        //vv($project_info);
		$this->assign("row",$project_info);
		
		//项目类型
		$type=$_REQUEST['type']?$_REQUEST['type']:$project_info['type'];
		$type=$type?$type:1;
		$typedata=$this->typedata($type);
		$this->assign("typedata",$typedata);
		
		//项目分类
		$longterm_list=$class->where(array('parent_id'=>$typedata['class_id']))->select();
		//vv($longterm_list);
		$this->assign("longterm_list",$longterm_list);
		
		$this->display();
	}
	
	public function typedata($type)
	{
		if($type==1)	//产品测试
		{
			$typedata['title']='产品测试';
			$typedata['list']='产品测试列表';
			$typedata['list_url']=U('project/goodstest'); 		//点击地址
			$typedata['add']='添加产品测试';
			$typedata['add_url']=U('project/project_info',array('type'=>$type)); 		//点击地址
			$typedata['type']=$type;
			$typedata['class_id']=4;
		}elseif($type==2)	//在线调查
		{
			$typedata['title']='在线调查';
			$typedata['list']='在线调查列表';
			$typedata['list_url']=U('project/survey'); 		//点击地址
			$typedata['add']='添加在线调查';
			$typedata['add_url']=U('project/project_info',array('type'=>$type)); 		//点击地址
			$typedata['type']=$type;
			$typedata['class_id']=5;
			
		}elseif($type==3)	//试用品派发
		{
			$typedata['title']='试用品派发';
			$typedata['list']='试用品派发列表';
			$typedata['list_url']=U('project/grant'); 		//点击地址
			$typedata['add']='添加试用品派发';
			$typedata['add_url']=U('project/project_info',array('type'=>$type)); 		//点击地址
			$typedata['type']=$type;
			$typedata['class_id']=6;
		}elseif($type==4)	//长期项目组
		{
			$typedata['title']='长期项目组';
			$typedata['list']='长期项目组列表';
			$typedata['list_url']=U('project/longterm'); 		//点击地址
			$typedata['add']='添加长期项目组';
			$typedata['add_url']=U('project/project_info',array('type'=>$type)); 		//点击地址
			$typedata['type']=$type;
			$typedata['class_id']=3;
		}
		
		return $typedata;
	}
	
	//保存&修改项目
	public function project_save()
	{
		$model=M('project');
		if (IS_POST) 
		{
			$array=I("post.post");
			if($_POST['start_time'])
			$array['start_time']=strtotime($_POST['start_time']);		//开始时间
			else
				$array['start_time']=time();
			if($_POST['end_time'])
			$array['end_time']=strtotime($_POST['end_time']);		//结束时间
			else
				$array['end_time']=time();
			if($_POST['project_start_time'])
			$array['project_start_time']=strtotime($_POST['project_start_time']);		//开始时间2
			else
				$array['project_start_time']=time();
			if($_POST['project_end_time'])
			$array['project_end_time']=strtotime($_POST['project_end_time']);		//结束时间2
			else
				$array['project_end_time']=time();
			$array['demand_age']=implode(",",$_POST['demand_age']);		//年龄范围
			$array['demand_height']=implode("-",$_POST['demand_height']);		//身高范围
			$array['demand_weight']=implode("-",$_POST['demand_weight']);		//体重范围
			//$array['demand_month_income']=implode("-",$_POST['demand_month_income']);		//个人月收入范围
			//$array['demand_family_income']=implode("-",$_POST['demand_family_income']);		//家庭月收入范围
			$array['user_yj']=implode(",",$_POST['user_yj']);		//研究员
			$array['user_jz']=implode(",",$_POST['user_jz']);		//兼职
			//关联项目经理
			$array['user_jl']=implode(",",$_POST['user_jl']);

			//省市区
			$array['demand_area_id']=implode(",",$_POST['demand_area_id']);		//省市区
			//是否对外显示
			if($_POST['demand_num_show']==1)
			{
				$array['demand_num_show']=1;
			}else
			{
				$array['demand_num_show']=0;
			}
			
			//是否组外发布
			if($_POST['is_zw']==1)
			{
				//修改组内发布 不修改字段 选择的时候选择为0
				$array['is_zw']=0;
			}else
			{
				//修改组内发布 不选择为1
				$array['is_zw']=1;
			}
			
			//通知所有联络员
			if($_POST['is_tongzhi']==1)
			{
				$array['is_tongzhi']=1;
			}else
			{
				$array['is_tongzhi']=0;
			}
			//其他需求json格式
			foreach($_POST['other_name'] as $k=>$v)
			{
				if(!empty($v))
				{
					$demand_other[$k]['other_name']=$v;
					$demand_other[$k]['other_value']=$_POST['other_value'][$k];
				}
				
			}
			$array['demand_other']=json_encode($demand_other);
			if(empty($_POST['id']))
			{
				$array['project_status']='4';
				$array['creat_id']=$_SESSION['ADMIN_ID'];
				$array['creat_name']=$_SESSION['name'];
				$array['add_time']=time();
				$array['state']='草稿';
				//检查项目号是否重复
				$project_no=$array['project_no'];
				$result_no=$model->where(array('project_no'=>$project_no))->find();
				//操作记录
				$this->admin_log($_SESSION['ADMIN_ID'],'添加项目,'.$array['title']);
				//如果有返回值则编号重复 返回false 不存在则执行插入
				$result=true;
				if($result_no)
				{
					$result=false;
					$result_erro='操作失败，该项目编号已存在，请重新生成或输入！';
				}else{
				$result=$model->add($array);
				$project_id=$result;
				}
				
			}else
			{
				$array['project_status']='4';

				$project_id=$_POST['id'];
				$result=$model->where(array('id'=>$_POST['id']))->save($array);

				//如果数据不改变则返回false 保存出错
				if($result !== false)
				{
					$result=true;
				}
				//操作记录
				if($result)
				$this->admin_log($_SESSION['ADMIN_ID'],'修改项目,'.$array['title']);

			
			}
			
			if(!$_GET['ajax'])
			{
				if ($result) {
				$this->success("操作成功！",'index.php?g=Admin&m=project&a=project_info_step3&type=1&project_id='.$project_id);
				} else {
				//如果变量存在，则提醒项目编号重复的错误
				if($result_erro)
					{
						$this->error($result_erro);

					}else{
						$this->error("操作失败！");
					}
				
				}
			}else{

				die(print_r('ajax_request'));
			}
			//print_r($article);
		}

	}
	
	//删除
	function project_del()
	{
		$model=M('project');
		if(isset($_GET['tid'])){
			$tid = intval(I("get.tid"));
			//$data['status']=0;
			if ($model->where("id=$tid")->delete()) {
				//操作记录
				$this->admin_log($_SESSION['ADMIN_ID'],'删除项目'.$tid);
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}
		if(isset($_REQUEST['ids'])){
			$tids=join(",",$_REQUEST['ids']);
			//$data['status']=0;
			if ($model->where("id in ($tids)")->delete()) {
				//操作记录
				$this->admin_log($_SESSION['ADMIN_ID'],'删除项目'.$ids);
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}
	}
	//删除
	function enrol_del()
	{
		$model=M('member_enrol');
		if(isset($_GET['tid'])){
			$tid = intval(I("get.tid"));
			//$data['status']=0;
			if ($model->where("enrol_id=$tid")->delete()) {
				//操作记录
				$this->admin_log($_SESSION['ADMIN_ID'],'删除项目报名'.$tid);
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}
		// if(isset($_REQUEST['ids'])){
		// 	$tids=join(",",$_REQUEST['ids']);
		// 	//$data['status']=0;
		// 	if ($model->where("id in ($tids)")->delete()) {
		// 		//操作记录
		// 		$this->admin_log($_SESSION['ADMIN_ID'],'删除项目'.$ids);
		// 		$this->success("删除成功！");
		// 	} else {
		// 		$this->error("删除失败！");
		// 	}
		// }
	}

	//批量修改项目状态
	function project_change_state()
	{
		$model=M('project');
		$status=trim($_REQUEST['status']);
		$save['state']=$status;
		$ids=trim($_REQUEST['check_val'],',');
		if ($model->where("id in ($ids)")->save($save)) {
				//操作记录
			
				$this->admin_log($_SESSION['ADMIN_ID'],'批量修改项目状态'.$ids);
				$this->success("修改成功！");
			} else {
				$this->error("修改失败！");
			}
	//	die($status);
		
	}
	//批量修改报名状态
	function project_baoming_state()
	{
		$model=M('member_enrol');
		$status=trim($_REQUEST['status']);
		$save['state']=$status;
		$ids=trim($_REQUEST['check_val'],',');
		if ($model->where("enrol_id in ($ids)")->save($save)) {
				//操作记录
			
				$this->admin_log($_SESSION['ADMIN_ID'],'批量修改报名状态'.$ids);
				$this->success("修改成功！");
				
			} else {
				$this->error("修改失败！");
			}
	
		
	}
	
	//第三部
	public function project_info_step3()
	{
		$ques=M('ques');
		$project=M('project');
		$id = intval(I("get.project_id"));
		$type = intval(I("get.type"));
		
		$project_info=$project->where(array('id'=>$id))->find();
		$project_info['ques_type']=$type;
		$project_info['ques_type_name']=f_project_ques($type);
		
		//vv($project_info);
		$this->assign("project_info",$project_info);
		
		$ques_list=$ques->where(array('project_id'=>$id,'type'=>$type))->order('sort asc')->select();
		
		$zimu=array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t');
		foreach($ques_list as $kk=>$vv)
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
			$ques_list[$kk]['answer_str']=implode(' | ',$answer_arr);
		}
		//vv($ques_list);
		$this->assign("ques_list",$ques_list);
		
		$this->display();
	}
	
	//第四步
	public function project_info_step4()
	{
		$process=M('project_process');
		$project=M('project');
		$id = intval(I("get.project_id"));
		$url="http://m.youshi.ltd/index.php?g=&m=Project&a=project_qiandao&project_id=".$id;
		$erweima= $this->geterweima($url,time());
		$this->assign("erweima",$erweima);
		$project_info=$project->where(array('id'=>$id))->find();
		//vv($project_info);
		$this->assign("project_info",$project_info);
		
		$id = intval(I("get.project_id"));
		$this->assign("project_id",$id);
		
		if($project_info['type']==2)
		{
			$this->display('project_info_step4_survey');
			die;
		}
		if($project_info['type']==4)
		{
			$this->display('project_info_step4_longterm');
			die;
		}
		
		$this->display();
	}
	
	
	//第五步
	public function project_info_step5()
	{
		$process=M('project_process');
		$project=M('project');
		$process_airradio=M('process_airradio');
		$id = intval(I("get.project_id"));
		
		$project_info=$project->where(array('id'=>$id))->find();
		$airradio_info=$process_airradio->where(array('project_id'=>$id))->find();
		// print_r($airradio_info);
		$this->assign("project_info",$project_info);
		$this->assign("airradio_info",$airradio_info);
		
		$process_list=$process->where(array('project_id'=>$id))->select();
		foreach($process_list as $k=>$v)
		{
			$process_type=strstr($v['start_date'],'-');
			$v['start_time']=explode(":",$v['start_time']); 		//年龄范围转换为数组
			$v['end_time']=explode(":",$v['end_time']); 		//年龄范围转换为数组
			
			
			$new_list[$v['type']][]=$v;
		}
		if($process_type)
		{
			$process_type='日期';
		}
		else{
			$process_type='天数';
		}
		$this->assign("process_type",$process_type);
		$this->assign("new_list",$new_list);
		
		$f_renwu=f_renwu();
		$this->assign("f_renwu",$f_renwu);
		//vv($f_renwu);
		
		//print_r($new_list);
		$this->display();
	}
	
	public function project_liucheng()
	{
		$model=M('project_process');
		$process_airradio=M('process_airradio');
		if (IS_POST) 
		{
			$type_arr=explode(",",$_POST['sos_type']);
			
			$array['project_id']=$_POST['project_id'];
			//
			$airradio_info=$process_airradio->where("project_id=$_POST[project_id]")->find();
			if(empty($airradio_info)&&$_POST['airradio'])
			{
				$air_array['project_id']=$_POST['project_id'];
				$air_array['is_show']=$_POST['airradio'];
				$process_airradio->add($air_array);
			}else{
				$air_up['is_show']=$_POST['airradio'];
				$process_airradio->where("project_id=$_POST[project_id]")->save($air_up);
				
			}

			

			foreach($_POST['name'] as $k=>$v)
			{
				//计算分类
				if($type_arr[$type-1]==$i)
				{
					$type++;
					$i=0;
				}
				$i++;
				$array['type']=		$type;
				$process_id=$_POST['process_id'][$k];		//主id
				$array['name']=		$v;	
				$array['start_date']=		$_POST['start_date'][$type-1];
				$array['section_date']=		$_POST['section_date'][$type-1];
				$array['start_time']=		$_POST['shi'][$k].':'.$_POST['fen'][$k].':'.$_POST['miao'][$k];
				$array['end_time']=			$_POST['end_shi'][$k].':'.$_POST['end_fen'][$k].':'.$_POST['end_miao'][$k];
				$array['is_tx']=			$_POST['is_tx'][$k];
				$array['space_time']=		$_POST['space_time'][$k];
				$array['desc']=		$_POST['desc'][$k];
				$array['ques_type']=		$_POST['ques_type'][$k];
				
				
				if(empty($process_id))
				{
					$model->add($array);
				}else
				{
					$model->where(array('process_id'=>$process_id))->save($array);
				}
				//vv($array);die;
			}
			//提交之后状态为待审批
			$project_arr['project_status']=0;
			$project=M('project');
			$project->where(array('id'=>$array['project_id']))->save($project_arr);

			//操作记录
			$array1=$project-> field('type,title')->where(array('id'=>$array['project_id']))->find();
				$this->admin_log($_SESSION['ADMIN_ID'],'修改项目流程'.$array1['title']);

			
			if($array1['type']==1)
			$this->success("操作成功！",'index.php?g=Admin&m=project&a=goodstest');
			if($array1['type']==2)
				$this->success("操作成功！",'index.php?g=Admin&m=project&a=survey');
			
			if($array1['type']==3)
				$this->success("操作成功！",'index.php?g=Admin&m=project&a=grant');
			
			if($array1['type']==4)
				$this->success("操作成功！",'index.php?g=Admin&m=project&a=longterm');
			//$this->success("操作成功！",'index.php?g=Admin&m=project&a=goodstest');
		}
		
		
		/*implode("-",$_POST['demand_weight']);		//体重范围
		
		explode("-",$project_info['demand_age']); 		//年龄范围转换为数组
		vv($_POST);
*/		
	}

		public function ajax_project_liucheng()
		{
			$model=M('project_process');
			$process_airradio=M('process_airradio');

			$type_arr=explode(",",$_POST['sos_type']);
			
			$array['project_id']=$_POST['project_id'];

			//
			$airradio_info=$process_airradio->where("project_id=$_POST[project_id]")->find();
			if(empty($airradio_info)&&$_POST['airradio'])
			{
				$air_array['project_id']=$_POST['project_id'];
				$air_array['is_show']=$_POST['airradio'];
				$process_airradio->add($air_array);
			}else{
				$air_up['is_show']=$_POST['airradio'];
				$process_airradio->where("project_id=$_POST[project_id]")->save($air_up);
				
			}
			// print_r($_POST);die;
			foreach($_POST['name'] as $k=>$v)
			{
				//计算分类
				if($type_arr[$type-1]==$i)
				{
					$type++;
					$i=0;
				}
				$i++;
				$array['type']=		$type;
				$process_id=$_POST['process_id'][$k];		//主id
				$array['name']=		$v;	
				$array['start_date']=		$_POST['start_date'][$type-1];
				$array['section_date']=		$_POST['section_date'][$type-1];
				$array['start_time']=		$_POST['shi'][$k].':'.$_POST['fen'][$k].':'.$_POST['miao'][$k];
				$array['end_time']=			$_POST['end_shi'][$k].':'.$_POST['end_fen'][$k].':'.$_POST['end_miao'][$k];
				$array['is_tx']=			$_POST['is_tx'][$k];
				$array['space_time']=		$_POST['space_time'][$k];
				$array['desc']=		$_POST['desc'][$k];
				$array['ques_type']=		$_POST['ques_type'][$k];
				
				
				if(empty($process_id))
				{
					$model->add($array);
				}else
				{
					$model->where(array('process_id'=>$process_id))->save($array);
				}
				//vv($array);die;
			}
			//提交之后状态为待审批
			$project_arr['project_status']=0;
			$project=M('project');
			$project->where(array('id'=>$array['project_id']))->save($project_arr);
			//操作记录
			$array=$project-> field('title')->where(array('id'=>$array['project_id']))->find();
				$this->admin_log($_SESSION['ADMIN_ID'],'修改项目流程'.$array['title']);
			if($process_id)
				{
					die($process_id);
				}
		
		
	
	}
	
	//为项目添加题
	public function project_ques()
	{
		$ques=M('ques');
		$project=M('project');
		$id = intval(I("get.project_id"));
		$type = intval(I("get.type"));
		$num = intval(I("get.num"));
		
		$project_info=$project->where(array('id'=>$id))->find();
		$project_info['ques_type']=$type;
		$project_info['ques_type_name']=f_project_ques($type);
		//vv($project_info);
		if($type==2 || $type==5)
		{
			$caozuo.='<a class="btn btn-danger" style="float:left; margin:0 20px 0 0" href="index.php?g=Admin&m=project&a=project_info_step4&project_id='.$id.'">返回上一步</a>';
		}
		
		if($type==3 || $type==4)
		{
			$caozuo.='<a class="btn btn-danger" style="float:left; margin:0 20px 0 0" href="index.php?g=Admin&m=project&a=project_info_step5&project_id='.$id.'">返回上一步</a>';
		}
		
		$this->assign("caozuo",$caozuo);
		
		
		$this->assign("project_info",$project_info);
		
		$ques_list=$ques->where(array('project_id'=>$id,'type'=>$type,'num'=>$num))->order('sort asc')->select();
		
		$zimu=array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t');
		foreach($ques_list as $kk=>$vv)
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
			$ques_list[$kk]['answer_str']=implode(' | ',$answer_arr);
		}
		//vv($ques_list);
		$this->assign("ques_list",$ques_list);
		$this->assign("num",$num);
		
		$this->display();
		
	}
	
	//获取报名列表
	public function baoming()
	{

		$project=M('project');
		$member_enrol=M('member_enrol');
		$project_id=$_GET['id'];
		
		//报名状态 根据是否勾选现场筛选 选择状态数组
		$status=$project->field('is_xianchagn,is_xcinput,is_xcsm,type')->where(array("id"=>$project_id))->find();
		//产品测试
		if($status['type']=='1')
		{
			$f_admin_baoming=f_admin_baoming1();
		}
		//在线调查
		if($status['type']=='2')
		{
			$f_admin_baoming=f_admin_baoming2();
		}

		if($status['type']=='3')
		{
			if($status['is_xianchagn']=='1'&& $status['is_xcinput']=='1'){
				//现场筛选返回状态数组
				$f_admin_baoming=f_admin_baoming3();
			}
			else{
				//未选择现场筛选返回状态数组
				$f_admin_baoming=f_admin_baoming_3();
			}
		}
		if($status['type']=='4')
		{
			$f_admin_baoming=f_admin_baoming4();
		}
				
		$this->assign("f_admin_baoming",$f_admin_baoming);
		//项目详情
		$project_info=$project->where(array("id"=>$project_id))->find();

		if(!empty($project_info))
		{
			$project_info['add_time']=date('Y-m-d H:i',$project_info['add_time']);	
			$project_info['start_time']=date('Y-m-d H:i',$project_info['start_time']);	//开始时间
			$project_info['end_time']=date('Y-m-d H:i',$project_info['end_time']);		//结束时间
			$project_info['project_start_time']=date('Y-m-d H:i:s',$project_info['project_start_time']);	//开始时间2
			$project_info['project_end_time']=date('Y-m-d H:i:s',$project_info['project_end_time']);		//结束时间2	
			$project_info['demand_other']=(array)json_decode($project_info['demand_other']);
			$project_info['price']=intval($project_info['price']); 		//格式化为整数
			if($project_info['project_status']=='1')
			{
				$project_info['project_status']='审核通过';
			}
			if($project_info['project_status']=='2')
			{
				$project_info['project_status']='审核不通过';
			}
			if($project_info['project_status']=='0')
			{
				$project_info['project_status']='待审核';
			}
			if($project_info['type']=='1')
			{
				$project_info['type']='产品测试';
			}
			if($project_info['type']=='2')
			{
				$project_info['type']='在线调查';
			}
			if($project_info['type']=='3')
			{
				$project_info['type']='试用品派发';
			}
			if($project_info['type']=='4')
			{
				$project_info['type']='长期项目组';
			}

			if($project_info['longterm']>0)
			{
				$pp=$project->field('title')->where(array('id'=>$project_info['longterm']))->find();
				$project_info['longterm']=$pp['title'];
			}
			$project_info['project_desc']=html_entity_decode($project_info['project_desc']);
			$project_info['demand']=html_entity_decode($project_info['demand']);
			//m
			$member=M('member_enrol');
			$baoming=$member->where(array('project_id'=>$project_id))->count();
			$tongguo=$member->where(array('project_id'=>$project_id,'baoming'=>'1'))->count();
			//$canyu=$member->where(array('project_id'=>$project_id,'baoming'=>'1'))->count();

		}
		//手机号
		if(!empty($_REQUEST['mobile']))
		{
			if($_REQUEST['mobile'])
			$where['mobile']=trim($_REQUEST['mobile']);
			
			$this->assign("mobile",$_REQUEST['mobile']);
			$_GET['mobile']=$_REQUEST['mobile'];
		}
		//性别
		if(!empty($_REQUEST['sex']))
		{
			if($_REQUEST['sex'])
			$where['sex']=trim($_REQUEST['sex']);
			
			$this->assign("sex",$_REQUEST['sex']);
			$_GET['sex']=$_REQUEST['sex'];
		}
		//姓名
		if(!empty($_REQUEST['user_name']))
		{
			if($_REQUEST['user_name'])
			$where['name']=array('like',"%".trim($_REQUEST['user_name'])."%");
			
			$this->assign("name",trim($_REQUEST['user_name']));
			$_GET['user_name']=trim($_REQUEST['user_name']);
		}

		//报名状态
		if(!empty($_REQUEST['state']))
		{
			if($_REQUEST['state'])
			$where['state']=trim($_REQUEST['state']);
			
			$this->assign("state",$_REQUEST['state']);
			$_GET['state']=$_REQUEST['state'];
		}

		//年龄
		if(!empty($_REQUEST['min_age']) && !empty($_REQUEST['max_age']))
		{
			$min_age=time()-$_REQUEST['min_age']*3600*24*365;
			$max_age=time()-$_REQUEST['max_age']*3600*24*365;

			$where["b.birthday"] = array(array('lt',$min_age),array('gt',$max_age));
			

			$this->assign("min_age",$_REQUEST['min_age']);
			$this->assign("max_age",$_REQUEST['max_age']);
			$_GET['min_age']=$_REQUEST['min_age'];
			$_GET['max_age']=$_REQUEST['max_age'];
		}

		//时间
		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])  && !empty($_REQUEST['time_type']))
		{
			//strtotime($_REQUEST['start_time']);
			$time_type=$_REQUEST['time_type'];
			if($time_type=='add_time'){
				$where["a.add_time"] = array(array('gt',strtotime($_REQUEST['start_time'])),array('lt',strtotime($_REQUEST['end_time'])));
			}
			if($time_type=='enrol_start_time'){
				$where["a.enrol_start_time"] = array(array('gt',$_REQUEST['start_time']),array('lt',$_REQUEST['end_time']));
			}
			
			$order1="$time_type asc";
			$this->assign("start_time",$_REQUEST['start_time']);
			$this->assign("end_time",$_REQUEST['end_time']);
			$this->assign("time_type",$_REQUEST['time_type']);
			$_GET['start_time']=$_REQUEST['start_time'];
			$_GET['end_time']=$_REQUEST['end_time'];
			$_GET['time_type']=$_REQUEST['time_type'];
		}
		
		//搜索keyword
		if(!empty($_REQUEST['keyword']))
		{

			$where['name']=array('like',"%".$_REQUEST['keyword']."%");
			$this->assign("keyword",$_REQUEST['keyword']);
			$_GET['keyword']=$_REQUEST['keyword'];
		}

		$where['project_id']=$project_id;
		$join = C('DB_PREFIX').'member as b on a.member_id =b.member_id';
		// print_r($where);
		$count=$member_enrol->alias("a")->join($join)->where($where)->count();
		$page = $this->page($count, 10);
		
		
		
		$users = $member_enrol->field('a.*,b.name,b.sex,b.birthday,b.mobile,b.area_name,b.member_id')->alias("a")->join($join)->where($where)->order('a.enrol_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		$this->assign("project_info",$project_info);
		$this->assign("baoming",$baoming);
		$this->assign("tongguo",$tongguo);
		//二维码
		
		$url="http://m.youshi.ltd/index.php?g=Mobile&m=process&a=shouhuo_ajax&project_id=".$project_id;
		$erweima= $this->geterweima($url,time());
		$this->assign("erweima",$erweima);
		$this->assign("page", $page->show('Admin'));

		//快递信息
		$express=M('express');
		$express_list=$express->where(array('state'=>1))->select();
		$this->assign("express_list",$express_list);
		$order=M('order');
		foreach ( $users as $k=>$v)
		{
			if($v['project_type']=='3')
			{
				$where_o['project_id']=$v['project_id'];
				$where_o['member_id']=$v['member_id'];
				$order_info=$order->field('name,phone,address,kuaidi,kuaidi_no')->where($where_o)->find();
				$users[$k]['kuaidi']=$order_info['kuaidi'];
				$users[$k]['kuaidi_no']=$order_info['kuaidi_no'];
				$users[$k]['order_name']=$order_info['name'];
				$users[$k]['order_phone']=$order_info['phone'];
				$users[$k]['address']=$order_info['address'];
			}

		}
		$this->assign('type',$status['type']);
		
		$this->assign("list",$users);
		$this->assign("count",$count);
		$this->assign("formget",$_GET);
		$this->display();
		//vv($users);
		//echo $project_id;
	}
	
	//修改报名状态
	//修改报名状态
	public function enrol_state()
	{
		$member_enrol=M('member_enrol');
		$enrol_info=$member_enrol->where("enrol_id=$_GET[enrol_id]")->find();
		if($enrol_info['baoming']==2&&$_GET['enrol_state']=='报名通过')
		{
			$baoming=1;
			$save['baoming']=1;
		}
		$save['state']=$_GET['enrol_state'];
		$member_enrol->where(array('enrol_id'=>$_GET['enrol_id']))->save($save);

		echo '成功!';
		
		
	}
	
	//甄别问卷
	public function zhenbie_show()
	{
		$ques=M('ques');
		//如果没有试题 跳到显示所有试题页面
		$member_ques=M('member_ques');
		$where_mq['member_id']=$_GET['member_id'];;
		$where_mq['project_id']=$_GET['project_id'];
		$where_mq['type']=$_GET['type'];
		$member_ques_info=$member_ques->where($where_mq)->find();
		$paper_desc=json_decode($member_ques_info['paper_desc'],true);
		$where2['project_id']=$_GET['project_id'];
		$where2['type']=$_GET['type'];
		$ques_list=$ques->where($where2)->order('num asc ,sort asc')->select();
		
		foreach($ques_list as $k=>$v)
		{
			$ques_arr[$k]['title']=$v['name'];
			$zimu=$paper_desc[$v['ques_id']]['ques_daan'];
			//vv($zimu);
			$new_zimu=explode("|",$zimu);
			$html='';
			foreach($new_zimu as $kk=>$vv)
			{
				if($v['class']=='问答题')
				{
					$html.=$vv;
				}else
				{
					$html.=strtoupper($vv)."：".$v[$vv]."<br>";
				}
				
			}
			$ques_arr[$k]['daan']=$html;
			$ques_list[$k]['ok_daan']=$vv;
		}
		
		$zimu=array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t');
		foreach($ques_list as $kk=>$vv)
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
			$ques_list[$kk]['answer_str']=implode(' | ',$answer_arr);
		}
		
		// print_r($where_mq);
		$this->assign("ques_info",$ques_list[0]);
		$this->assign("ques_arr",$ques_arr);
		$this->assign("ques_list",$ques_list);
		$this->display();
	}
	//修改项目状态
	function change_state()
	{
		$project_id=(int)$_REQUEST['project_id'];
		$status=$_REQUEST['status'];
		$model=M('project');
		$save['state']=$status;
		$where['id']=$project_id;
		$res=$model->where($where)->save($save);

		if($res)
		{
			//操作记录
			$array=$model->field('title')->where(array('id'=>$project_id))->find();
				$this->admin_log($_SESSION['ADMIN_ID'],'修改项目状态为'.$status.',项目名称'.$array['title']);
			die('操作成功');
		}else{
			die('更新失败');
		}
	}
		//修改审批状态
	function change_project_status()
	{
		$project_id=(int)$_REQUEST['project_id'];
		$status=trim($_REQUEST['status']);
		$model=M('project');
		if($status=='待审批')
		{
			$project_status=0;
		}
		if($status=='审批通过')
		{
			$project_status=1;
			
		}
		if($status=='审批不通过')
		{
			$project_status=2;
		}
		$save['project_status']=$project_status;
		
		$where['id']=$project_id;
		$array=$model->field('start_time,title')->where(array('id'=>$project_id))->find();
		if($project_status=='1')
		{
			if($array['start_time']>=time())
			{
				$save['state']='待招募';
			}else{
				$save['state']='招募中';
			}
		}
			
		$res=$model->where($where)->save($save);

		if($res)
		{
			//审批之后将项目改为招募中
			
			//操作记录
			
				$this->admin_log($_SESSION['ADMIN_ID'],'修改项目审批状态为'.$status.',项目名称'.$array['title']);
			die('操作成功');
		}else{
			die('更新失败');
		}
	}
	//修改会员项目的开始时间
	public function ajax_start_time()
	{
		$enrol_id=$_REQUEST['enrol_id'];
		$enrol_start_time=$_REQUEST['enrol_start_time'];
		$enrol=M('member_enrol');
		$save['enrol_start_time']=$enrol_start_time;
		$where['enrol_id']=$enrol_id;
		$res=$enrol->where($where)->save($save);
		// die($enrol_id.'-----------'.$enrol_start_time);
		if($res)
		die('成功');
		else
			die('失败');
	}

	//订单发货
	public function order_fahuo()
	{
		
		if (IS_POST) 
		{
			$array=I("post.post");
			$model=M('order');
			$member_enrol=M('member_enrol');
			$enrol_id=$_POST['enrol_id_f'];	
			$member_enrol_info=$member_enrol->where("enrol_id=$enrol_id")->find();
			$where['project_id']=$member_enrol_info['project_id'];
			$where['member_id']=$member_enrol_info['member_id'];
			
			$kuaidi_arr=explode('-',$_POST['kuaidi']);
			$array['kuaidi']=$kuaidi_arr[0];
			$array['kuaidi_code']=$kuaidi_arr[1];
			
			$result=$model->where($where)->save($array);
			
			if ($result) {
				$this->admin_log($_SESSION['ADMIN_ID'],'修改试用品派发订单状态为发货,姓名为'.$member_enrol_info['member_name'].'项目名为'.$member_enrol_info['project_name']);
				$this->success("操作成功！");//,'index.php?g=Admin&m=project&a=project_info_step3&type=1&project_id='.$project_id);
			} else {
				$this->error("操作失败！");
			}
		}
		//vv($_POST);
	}
	

	public function send_check()
	{
		$project_id=$_GET['project_id'];
		$project=M('project');
		$project_info['project_status']=0;
		$res1=$project->where("id=$project_id")->save($project_info);
		if($res1)
		{
			echo '提交审核成功';die;
		}
		else{
			echo '重新提交';die;
		}
	}

	//查询会员是否存在
	public function search_add()
	{
		$member=M('member');
		if (IS_POST) 
		{
			if(trim($_POST['add_name']))
			{
				$keyword="%".trim($_POST['add_name'])."%";
				$where['name']=array('like',$keyword);
			}
			if(trim($_POST['add_mobile']))
			{
				$keyword="%".trim($_POST['add_mobile'])."%";
				$where['mobile']=array('like',$keyword);
			}
			$member_info=$member->field('member_id,name,mobile')->where($where)->find();
		}
		if($member_info)
		{
			print_r(json_encode($member_info));die;
		}else{
			$result['erro']=1;
			$result['msg']='没有搜索到符合条件的用户';
			print_r(json_encode($result));die;
		}
		
	}
	//将会员添加进项目组
	public function search_add_save()
	{
		$member=M('member');
		$member_enrol=M('member_enrol');
		$project=M('project');
		if (IS_POST) 
		{
			if(trim($_POST['member_name']))
			{
			
				$where['name']=trim($_POST['member_name']);
			}
			if(trim($_POST['member_mbile']))
			{
				
				$where['mibile']=trim($_POST['member_mbile']);
			}
			if(trim($_POST['member_id']))
			{
				
				$where['member_id']=trim($_POST['member_id']);
			}
			if(trim($_GET['project_id']))
			{
				
				$where_p['id']=trim($_GET['project_id']);
			}
			$where_m['project_id']=$where_p['id'];
			$where_m['member_id']=$where['member_id'];
			$member_enrol_info=$member_enrol->field('member_id,member_name')->where($where_m)->find();
			if($member_enrol_info)
			{
				$data['erro']='2';
				$data['msg']='该会员已在项目组内';
				print_r(json_encode($data));die();
			}
			$member_info=$member->field('member_id,name,mobile')->where($where)->find();
			$project_info=$project->field('id,title,project_no,type')->where($where_p)->find();
			
		}
		if($member_info&&$project_info)
		{
			$save['member_id']=$member_info['member_id'];
			$save['member_name']=$member_info['name'];
			$save['project_id']=$project_info['id'];
			$save['project_no']=$project_info['project_no'];
			$save['project_type']=$project_info['type'];
			$save['baoming']='1';
			$save['state']='报名通过';
			$save['add_time']=time();
			$res=$member_enrol->add($save);

		}
		if($res)
		{
			$data['erro']='0';
			$data['msg']='添加会员进项目组成功';
		}else{
			$data['erro']='1';
			$data['msg']='添加失败';
		}
		print_r(json_encode($data));die;
	}
	//题序排列
	public function ajax_change_sort()
	{
		$ques_id=$_GET['ques_id'];
		$sort=$_GET['sort'];
		$ques=M('ques');
		$save['sort']=$sort;
		$where['ques_id']=$ques_id;
		$res=$ques->where($where)->save($save);
		if($res)
		{
			echo "成功";die;
		}else{
			echo "失败";die;
		}
	}


	//申请报酬
	public function apply_money()
	{
		
		if (IS_POST) 
		{
			$array=I("post.post");
			$project=M('project');
			$member_enrol=M('member_enrol');
			$member_pay=M('member_pay');
			$member=M('member');
			$user=M('users');

			$enrol_id=$_POST['enrol_id_apply'];	
			$apply_money=$array['apply_money'];	
			$member_enrol_info=$member_enrol->where("enrol_id=$enrol_id")->find();
			$project_id=$member_enrol_info['project_id'];
			$member_id=$member_enrol_info['member_id'];

			$project_info=$project->where("id=$project_id")->find();
			$member_info=$member->where("member_id=$member_id")->find();
			//数组
			if(!$apply_money)
			{
				$apply_money=$project_info['price'];
			}
			if($apply_money>$project_info['price'])
			{
				$data['erro']=1;
				$data['msg']='申请报酬大于项目报酬,请核实后申请';
				print_r(json_encode($data));die;
			}
			$user_info = $user->where("id=$_SESSION[ADMIN_ID]")->find();

			$save['member_id']=$member_id;
			$save['project_id']=$project_id;
			$save['apply_admin_name']=$user_info['user_login'];
			$save['apply_time']=time();
			$save['apply_money']=$apply_money;
			$save['project_name']=$project_info['title'];
			$save['member_name']=$member_info['name'];
			$save['project_price']=$project_info['price'];

			$where['member_id']=$member_id;
			$where['project_id']=$project_id;
			$pay_info=$member_pay->where($where)->find();
			if($pay_info)
			{
				$data['erro']=1;
				$data['msg']='已经申请过报酬,请不要重复申请';
				print_r(json_encode($data));die;
			}else{
				$result=$member_pay->add($save);
			}

			
			
			if ($result) {
				$this->admin_log($_SESSION['ADMIN_ID'],'申请项目酬金,姓名为'.$member_info['name'].'项目名为'.$project_info['title']);
				$data['erro']=0;
				$data['msg']='申请成功';
				print_r(json_encode($data));die;
				
			} else {
				$data['erro']=1;
				$data['msg']='申请失败';
				print_r(json_encode($data));die;
			}
		}
		//vv($_POST);
	}

	//绑定设备号
	function bangding()
	{
		if (IS_POST) 
		{
			$array=I("post.post");
			$project=M('project');
			$member_enrol=M('member_enrol');

			$enrol_id=$array['enrol_id_bangding'];	
			$device_id=$array['device_id'];	

		
			$save['device_id']=$device_id;
			$where['enrol_id']=$enrol_id;
			$member_enrol_info=$member_enrol->where($where)->find();

			$result=$member_enrol->where($where)->save($save);
		
			if ($result) {
				$this->admin_log($_SESSION['ADMIN_ID'],'绑定设备号,姓名为'.$member_enrol_info['member_name'].'设备号为'.$device_id);
				$data['erro']=0;
				$data['msg']='绑定设备成功';
				print_r(json_encode($data));die;
				
			} else {
				$data['erro']=1;
				$data['msg']='绑定设备失败';
				print_r(json_encode($data));die;
			}
		}		
	}

	function bind_device_id()
	{
		$enrol_id=$_GET['enrol_id'];
		$member_enrol=M('member_enrol');
		$where['enrol_id']=$enrol_id;
		$member_enrol_info=$member_enrol->where($where)->find();
		if($member_enrol_info['device_id'])
		{
			$result['status']=1;
			$result['device_id']=$member_enrol_info['device_id'];
		}else{
			$result['status']=0;
			$result['device_id']=$member_enrol_info['device_id'];

		}
		print_r(json_encode($result));

	}
	
	 public function airradio()
	 {
	 	$member_id=$_GET['member_id'];
	 	$project_id=$_GET['project_id'];
	 	$member_enrol=M('member_enrol');
	 	$where['member_id']=$member_id;
	 	$where['project_id']=$project_id;
	 	$enrol_info=$member_enrol->where($where)->find();
	 	$device_id=$enrol_info['device_id'];
	 	
	 	$link=mysql_connect("localhost","phpclient","phppassword"); 
		if(!$link) echo "FAILD!".mysql_error(); 
		mysql_select_db('environment',$link);
		if($device_id)
		{
			$sql="select `temperature`,`humility`,`pm25`,`pm10`,`create_time` from airradio where `device_id`='".$device_id."' order by `utc_time` desc  ";
			$res=mysql_query($sql);
			//$row=mysql_fetch_assoc($res);
			mysql_close($link);
			$data=array();
			while($row=mysql_fetch_assoc($res))
			{
				$data[]=$row;
			}
		}
		
		$this->assign('data',$data);
		$this->display();
	 }


	 	  public function airradio_history()
	 {
	 	$device_id=$_GET['device_id'];
	 	$p=$_GET['p']?$_GET['p']:'1';
	 	$start_date=strtotime($_REQUEST['start_time']);
	 	$end_date=strtotime($_REQUEST['end_time']);
	 	$size='200';

	 	if($start_date&&$end_date&&$device_id)
	 	{
	 			$db='db='.urlencode('environment');
				$end_time=date("Y-m-d".'T'."H:i:s",$end_date);
				$start_time=date("Y-m-d".'T'."H:i:s",$start_date);
				 $start_time=str_replace('CST','T', $start_time);
				 $end_time=str_replace('CST','T', $end_time);
				$start_time.='Z';
				$end_time.='Z';
				$size='200';
				$limit_start=($p-1)*$size;
				$limit_end=$p*$size;

				$q='q='.urlencode("select * from airradio where device_id='".$device_id."' and time<='".$end_time."' and time>='".$start_time."'  order by time desc limit 200 offset ".$limit_start);
				$res=$this->httpClientGet('http://116.62.244.52:8086/query?u=pm25&p=fsztdbpm25&', $db.'&'.$q);
				$data_res=json_decode($res,ture);
				$data=$data_res['results']['0']['series']['0']['values'];

				$result=array();
				foreach($data as $k=>$v)
				{
					$v['0']=str_replace('T',' ', $v['0']);
					$v['0']=str_replace('Z',' ', $v['0']);
					$result[$k]['create_time']=$v['0'];
					$result[$k]['humility']=$v['2'];
					$result[$k]['pm10']=$v['4'];
					$result[$k]['pm25']=$v['5'];
					$result[$k]['temperature']=$v['6'];
				}

				//查询数量
				// $q1='q='.urlencode("select * from airradio where device_id='".'7300050FB41E'."' and time<='".$end_time."' and time>='".$start_time."'  order by time desc ");
				// $res_count=$this->httpClientGet('http://116.62.244.52:8086/query?u=pm25&p=fsztdbpm25&', $db.'&'.$q1);
				// $data_count=json_decode($res_count,ture);
				// $data_count=$data_count['results']['0']['series']['0']['values'];

				//先默认1000	
				$count=1000;
				
				$this->assign('data',$result);
				$page = $this->page($count, 100);
				$this->assign("count",$count);
				$this->assign("page",$page->show('Admin'));
	 	}
	 	
				$this->display('airradio');

	 }

	   public function httpClientGet($url, $data = null, $cookie = null) {

        $parse = parse_url($url);
        $url .= $data;
        $host = $parse['host'];
        $header  = 'Host: '. $host. "\\r\\n";
        $header .= 'Connection: close'. "\\r\\n";
        $header .= 'Accept: */*'. "\\r\\n";
        $header .= 'User-Agent: '. ( isset($this->args['userAgent']) ? $this->args['userAgent'] : $_SERVER['HTTP_USER_AGENT'] ). "\\r\\n";
        $header .= 'DNT: 1'. "\\r\\n";
        if($cookie) $header .= 'Cookie: '. $cookie. "\\r\\n";
        if($this->referer) $header .= 'Referer: '. $this->referer. "\\r\\n";
        $options = array();
        $options['http']['method'] = 'GET';
        $options['http']['header'] = $header;
        return $this->buffer = file_get_contents($url);

    }

	
}