<?php
/*
 *      _______ _     _       _     _____ __  __ ______
 *     |__   __| |   (_)     | |   / ____|  \/  |  ____|
 *        | |  | |__  _ _ __ | | _| |    | \  / | |__
 *        | |  | '_ \| | '_ \| |/ / |    | |\/| |  __|
 *        | |  | | | | | | | |   <| |____| |  | | |
 *        |_|  |_| |_|_|_| |_|_|\_\\_____|_|  |_|_|
 */
/*
 *     _________  ___  ___  ___  ________   ___  __    ________  _____ ______   ________
 *    |\___   ___\\  \|\  \|\  \|\   ___  \|\  \|\  \ |\   ____\|\   _ \  _   \|\  _____\
 *    \|___ \  \_\ \  \\\  \ \  \ \  \\ \  \ \  \/  /|\ \  \___|\ \  \\\__\ \  \ \  \__/
 *         \ \  \ \ \   __  \ \  \ \  \\ \  \ \   ___  \ \  \    \ \  \\|__| \  \ \   __\
 *          \ \  \ \ \  \ \  \ \  \ \  \\ \  \ \  \\ \  \ \  \____\ \  \    \ \  \ \  \_|
 *           \ \__\ \ \__\ \__\ \__\ \__\\ \__\ \__\\ \__\ \_______\ \__\    \ \__\ \__\
 *            \|__|  \|__|\|__|\|__|\|__| \|__|\|__| \|__|\|_______|\|__|     \|__|\|__|
 */
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace Mobile\Controller;
use Common\Controller\MobilebaseController; 
/**
 * 项目过程
 */
class ProcessController extends MobilebaseController {
	
	function _initialize() {
		parent::_initialize();
		if(empty($_SESSION['member']))
		{
			// Header("Location: ".U('login/login')); 
			$this->getOpenId('m=process&a=index');
			//$this->error("你还没有登录，请先登录！",U('login/login'));
		}
		//判断信息完整度
		$member_info=$this->member_info($_SESSION['member']['member_id']);
		if(!$member_info['name'] ||!$member_info['birthday'])
		{
		 $_SESSION['member_id']=$member_info['member_id'];
		 Header("Location: ".U('login/register2')."&member_id=".$member_info['member_id']); 
		 exit;
		}
		if($member_info['wanzhengdu']<65)
		{
		Header("Location: ".U('member/perfect1')); 
		exit;
		}
	
		//$this->assign("active",'member');
		/*if(empty($_SESSION['member']))
		{
			Header("Location: ".U('login/register')); 
			//$this->error("你还没有登录，请先登录！",U('login/login'));
		}else
		{
			$member_info=$this->member_info($_SESSION['member']['member_id']);
			//vv($member_info);
			$this->assign("member_info",$member_info);
		}*/
		
	}
	
    //首页
	public function index() 
	{
		//导入类库
		vendor('jssdk.jssdk');//导入类库
		$jssdk=new \jssdk(WX_APP_ID, WX_SECRET);
		$signPackage = $jssdk->GetSignPackage();
		$this->assign("signPackage",$signPackage);
		$project=M('project');
		$process_airradio=M('process_airradio');
		
		$project_info=$project->where(array('id'=>$_GET['project_id']))->find();
			$airradio_info=$process_airradio->where(array('project_id'=>$_GET['project_id'],'is_show'=>'1'))->find();
		$this->assign("project_info",$project_info);
		$this->assign("airradio_info",$airradio_info);
		//vv($project_info);
		$member_process=M('member_process');
		$mp_where['member_id']=$_SESSION['member']['member_id'];
		$mp_where['project_id']=$_GET['project_id'];
		$mp_info=$member_process->where($mp_where)->find();
		//vv($mp_info);
		$member_enrol=M('member_enrol');
		//查询出开始日期 member_enrol的条件 where_e
		$where_e['member_id']=$_SESSION['member']['member_id'];
		$where_e['project_id']=$_GET['project_id'];
		$member_en=$member_enrol->field('enrol_start_time')->where($where_e)->find();
		$enrol_start_time=$member_en['enrol_start_time'];
		//查询开始日期结束

		if(empty($mp_info))
		{
			$sub_num=1;
		}else
		{
			$sub_num=$mp_info['sub_num']+1;
		}
		
		$new_mp=json_decode($mp_info['process_desc'],true);

		// print_r($new_mp);
		$project_process=M('project_process');
		$process_list=$project_process->where(array('project_id'=>$_GET['project_id']))->order('type asc')->select();
		$sup_num=1;
		$i=1;
		foreach($process_list as $k=>$v)
		{
			//到下一次反馈的区间
			if(is_numeric($v['start_date'])&&$enrol_start_time)
			{
				if($v['section_date'])
				{
					$time=strtotime($enrol_start_time)+($v['section_date']-1)*3600*24;
				$v['section_date']=date('Y-m-d',$time);
				}
				
			}else{
				$v['section_date']=$v['section_date'];
			}

			if(!empty($new_mp[$v['process_id']]))
			{
				$v['process_desc']=$new_mp[$v['process_id']];
			}
			
			//判断时间
			//如果是天数 则转化为日期
			if(is_numeric($v['start_date'])&&$enrol_start_time)
			{
				//说明是天数
				$numeric_start_date=1;
				$time=strtotime($enrol_start_time)+(($v['start_date']-1)*3600*24);
				$v['start_date']=date('Y-m-d',$time);
			}elseif(is_numeric($v['start_date'])&&!$enrol_start_time){
				$numeric_start_date=1;
				$v['start_date']='第'.$v['start_date'].'天';
			}
			if(time()>strtotime($v['start_date']))
			{
				$v['is_can']='go';
			}else{
				if($v['start_date'])
				$v['is_can']=$v['start_date'];
				else{
					$v['is_can']='go';
				}
			}

			//判断执行到那一步有没有重做
			$chongzuo_arr=explode(",",$mp_info['chongzuo_id']);
			if(($sup_num==$sub_num || in_array($v['process_id'],$chongzuo_arr)) && $i==1)
			{
				$i=0;
				$new_info=$v;

			}
			//重做不显示完成
			if(in_array($v['process_id'],$chongzuo_arr))
				unset($v['process_desc']);
			if($v['section_date'])
			{
				$new_space[$v['start_date']]['section_date']=$v['section_date'];
			}
			
			
			$new_list[$v['start_date']][]=$v;
			$sup_num++;
		}
		//会员开始走反馈流程的时间
		$now_time=time();
		$enrol_start=strtotime($enrol_start_time);
		//天数的时候判断
		if($now_time<=$enrol_start&&$numeric_start_date)
		{
			$new_info['name']='请在'.$enrol_start_time.'开始流程';
			$new_info['process_id']=0;
		}
		//日期的时候判断
		$enrol_start=strtotime($new_info['start_date']);
		if($now_time<=$enrol_start)
		{
			$new_info['name']='请在'.$new_info['start_date'].'开始流程';
			$new_info['process_id']=0;
		}
		//只有日期判断设置的开始时间
		if(!$enrol_start_time&&$numeric_start_date)
		{
			$new_info['name']='请等待反馈流程开始';
			$new_info['process_id']=0;
		}
		//end 流程时间
		if(empty($new_info))
		{
			$new_info['name']='任务已完成';
			$new_info['process_id']=0;
			
		}
		//间隔时间
		date_default_timezone_set('PRC');
		$pro_id=0;
		//比较出最大的流程id
		foreach($new_mp as $ke=>$va)
		{
			if($va['mp_id'] > $pro_id)
			{
				$pro_id=$va['mp_id'];
			}
		}
		
		//判断开始时间 小时
		$today_start=$new_info['start_date'].' '.$new_info['start_time'];
		$today_time=strtotime($today_start);
		if($now_time<$today_time)
		{
			$new_info['name']='请在'.$new_info['start_date'].'/'.$new_info['start_time'].'之后开始';
			$new_info['process_id']=0;
		}

		$new_info['mp_time']=$new_mp[$pro_id]['mp_time'];
		//第二步的间隔时间与第一步的完成时间对比
		if($new_info['mp_time'] > 0)
		{
			$new_info['left_time']=time()-$new_info['mp_time'];
			//和间隔时间对比  单位秒
			//比较间隔时间
			if($new_info['left_time']>$new_info['space_time']*60)
			{
				$new_info['left_time']=0;
			}else{
				$new_info['left_time']=$new_info['space_time']*60-$new_info['left_time'];
			}

		}else{
			$new_info['left_time']=0;
		}		

		//更新项目结束时间 member_enrol
			
			if($new_info['name']=='任务已完成' && $new_info['process_id']=='0')
			{
				$save['enrol_end_time']=date('Y-m-d H:i');		
				$res1=$member_enrol->where($where_e)->save($save);
				//更新时间后，状态变为审核中
				if($res1)
				{
					$state='审核中';
					$arr['state']=$state;
					$member_enrol->where($where_e)->save($arr);

				}

				$new_info['success_all']='yes';
			}
			

		$this->assign("sub_num",$sub_num);
		$this->assign("mp_info",$mp_info);
		$this->assign("new_info",$new_info);
		$this->assign("new_list",$new_list);
		$this->assign("new_space",$new_space);

		$this->display();
    }
	
	public function diaocha()
	{
		$ques=M('ques');
		$project_id=$_GET['project_id'];
		$sort=$_GET['sort'];
		$where['project_id']=$project_id;
		$where['type']=$_GET['type'];
		$turn_to=$_GET['turn_to'];
		$num=$_GET['num'];
		$where['num']=$_GET['num'];
		if($turn_to)
		$where['ques_id']=$turn_to;
		//$where['sort']=$sort;
		 if($turn_to > 0)
		 {
		 $ques_list=$ques->where($where)->select();
		 }else{
			$ques_list=$ques->where($where)->order('sort asc')->limit($sort-1,1)->select();
		}
		// print_r($where);
		//$ques_list=$ques->where($where)->order('sort asc')->limit($sort-1,1)->select();
		$ques_info=$ques_list[0];
		if(!empty($ques_info))
		{
			$zimu=array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t');
			foreach($zimu as $k=>$v)
			{
				if(!empty($ques_info[$v]))
				{
					$ques_info['answer'][$v]=/*strtoupper($v).":".*/$ques_info[$v];
				}else
				{
					break;
				}
			}
			
			//vv($ques_info);
			$new_daan=$this->member_daan($ques_info['project_id'],$ques_info['ques_id'],$ques_info['type']);
			if($ques_info['class']=='问答题')
			{
				$ques_info['new_daan']=$new_daan;
			}else
			{
				$ques_info['new_daan']=explode("|",$new_daan);
			}
			$this->assign("ques_info",$ques_info);
			$this->display();
		}else
		{
			//如果没有试题 跳到显示所有试题页面
			$member_ques=M('member_ques');
			$where_mq['member_id']=$_SESSION['member']['member_id'];
			$where_mq['project_id']=$project_id;
			$where_mq['type']=$_GET['type'];
			$member_ques_info=$member_ques->where($where_mq)->find();
			$paper_desc=json_decode($member_ques_info['paper_desc'],true);
			//vv($member_ques_info);
			
			$where2['project_id']=$project_id;
			$where2['type']=$_GET['type'];
			$num=$_GET['num'];
			$where2['num']=$_GET['num'];
			$ques_list=$ques->where($where2)->select();
			
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
			}
			//vv($ques_arr);
			//项目的积分和鼓励金
			$project_model=M('project');
			$p_info=$project_model->field('integral,price')->where("id=$project_id")->find();
			$this->assign("project_info",$p_info);
			$this->assign("ques_info",$ques_list[0]);
			$this->assign("ques_arr",$ques_arr);

			$this->display('diaocha_show');
			//echo '123';
		}
	}
	
	//获取用户选的答案
	public function member_daan($project_id,$ques_id,$type)
	{
		$member_ques=M('member_ques');
		$where_mq['member_id']=$_SESSION['member']['member_id'];
		$where_mq['project_id']=$project_id;
		$where_mq['type']=$type;
		$member_ques_info=$member_ques->where($where_mq)->find();
		//vv($member_ques_info);
		if(empty($member_ques_info))
		{
			return '';
		}else
		{
			$paper_desc=json_decode($member_ques_info['paper_desc'],true);
			//vv($paper_desc[$ques_id]);
			return $paper_desc[$ques_id]['ques_daan'];
			
		}
		//vv($member_ques_info);
		//vv();
	}
	
	public function ques_ajax()
	{
		$ques=M('ques');
		$project=M('project');
		$member_ques=M('member_ques');
		
		if (IS_POST) 
		{
			
			$ques_info=$ques->where(array('ques_id'=>$_POST['ques_id']))->find();
			$project_info=$project->where(array('id'=>$ques_info['project_id']))->find();
			//判断问题是否回答正确
			//$ques_isok=$this->ques_isok($ques_info['ques_id'],$_POST['ques']);
			//die;
			$array['ques_id']=$ques_info['ques_id'];	//问题id
			$array['ques_class']=$ques_info['class'];	//问题分类
			
			if($ques_info['class']=='问答题')
			{
				$array['ques_daan']=$_POST['ques'];			//问题答案
			}else
			{
				$array['ques_daan']=implode("|",$_POST['ques']);			//问题答案
			}
			
			//$array['ques_daan']=implode("|",$_POST['ques']);			//问题答案
			//$array['ques_ok']=$ques_isok;	 					//是否回答正确 0.未正确 1.回答正确
			
			$new_array[$array['ques_id']]=$array;
			$where_mq['member_id']=$_SESSION['member']['member_id'];
			$where_mq['project_id']=$project_info['id'];
			$where_mq['type']=$ques_info['type'];
			$member_ques_info=$member_ques->where($where_mq)->find();
			//vv($ques_info);die;
			//逻辑跳转的where条件
			$where['project_id']=$ques_info['project_id'];
			$where['type']=4;
			$where['num']=$ques_info['num'];
			//逻辑跳转
			$turn_to=0;
			//如果没有跳转逻辑则返回下一题的ques_id
			
			if($ques_info['turn_to'])
			{
				$ques_info['turn_to']=unserialize($ques_info['turn_to']);	

			}
			if($ques_info['turn_to']&&$ques_info['class']=='单选')
			{
				$turn_to=$ques_info['turn_to'][$_POST[ques][0]];
			}
			if($turn_to){
				//
			}
			else{
			//如果没有跳转逻辑则返回下一题的ques_id			
				$sort=$ques_info['sort'];
				$ques_list=$ques->where($where)->order('sort asc')->select();
				foreach ($ques_list as $key => $value) {
					if($value['ques_id']==$ques_info['ques_id'])
					{
						$ques_info_next[]=$ques_list[$key+1];
					}
				}
				//当ques_id < 下一个ques_id时，返回下一题 否则返回字符
				//file_put_contents('444.txt',var_export($ques_info_next[0],true));
				if($ques_info['sort'] < $ques_info_next[0]['sort'])
				{
				   $turn_to=$ques_info_next[0]['ques_id'];
				}else{
					//返回字符串 供判断回答是否结束
					$turn_to='thisall';
				}

			}


			// print_r($turn_to);die;
			// print_r($turn_to);die;
			if(empty($member_ques_info))// || empty($member_ques_info['paper_desc']))
			{
				
				$data['member_id']=$_SESSION['member']['member_id'];
				$data['paper_type']=2;
				$data['paper_desc']=json_encode($new_array);
				$data['project_id']=$project_info['id'];
				$data['project_name']=$project_info['title'];
				$data['project_type']=$project_info['type'];
				$data['add_time']=time();
				$data['type']=$ques_info['type'];
				//vv($data);die;
				$member_ques->add($data);
				$result['msg']='操作成功！';
				$result['turn_to']=$turn_to;
				$result['num']=$ques_info['num'];
				die(json_encode($result));
			}else
			{
				//vv(json_decode($member_ques_info['paper_desc'],true));die;
				$new_paper_desc=$new_array+json_decode($member_ques_info['paper_desc'],true);
				//vv($new_paper_desc);die;
				//die;
				$member_ques->where(array('member_ques_id'=>$member_ques_info['member_ques_id']))->save(array('paper_desc'=>json_encode($new_paper_desc)));
				
				$result['msg']='操作成功！';
				$result['turn_to']=$turn_to;
				$result['num']=$ques_info['num'];
				die(json_encode($result));
				
			}
			//die;
		}
		
		
		
	}
	
	public function quesok_ajax()
	{
		$member_ques=M('member_ques');
		$member_process=M('member_process');
		$project_process=M('project_process');
		$where['project_id']=$_GET['project_id'];
		
		$where['type']=$_GET['type'];
		$where['member_id']=$_SESSION['member']['member_id'];
		$ques_info=$member_ques->where($where)->find();
		//vv($ques_info);
		$process_id=$_GET['process_id'];
		$isok=$member_ques->where($where)->save(array('status'=>1));
		
		if($ques_info['project_type']==1 || $ques_info['project_type']==3)
		{
			//修改提交
			$mp_where['member_id']=$_SESSION['member']['member_id'];
			$mp_where['project_id']=$_GET['project_id'];
			//$mp_where['ques_type']=$_GET['type'];
			$mp_info=$member_process->where($mp_where)->find();
			//vv($mp_info['process_desc']);
			//vv($mp_where);
			$pp_where['project_id']=$_GET['project_id'];
			$pp_info=$project_process->where($pp_where)->limit($mp_info['sub_num'],1)->select();
			//vv($pp_info[0]);die;
			// if($process_id)
			// {
			// 	$process_id=$process_id;
			// }else{
			// 	$process_id=$pp_info[0]['process_id'];
			// }
			$mp_data[$process_id]['mp_id']=$process_id;
			$mp_data[$process_id]['mp_desc']='执行成功';
			$mp_data[$process_id]['mp_type']=$_GET['type'];
			$mp_data[$process_id]['mp_mq_id']=$ques_info['member_ques_id'];
			$mp_data[$process_id]['mp_time']=time();
			
			//流程写入之后开始改变报名者的状态
			if(empty($mp_info))
			{
				$isok=$this->v_member_process($_GET['project_id'],$mp_data);
			}else
			{
				$new_desc=$mp_data+json_decode($mp_info['process_desc'],true);
				$isok=$member_process->where($mp_where)->save(array('process_desc'=>json_encode($new_desc),'sub_num'=>count($new_desc)));	//修改信息
			}
			
			
			$chongzuo_arr=explode(",",$mp_info['chongzuo_id']);

			if(in_array($process_id,$chongzuo_arr))
			{
				foreach($chongzuo_arr as $k=>$v)
				{
					if($v==$process_id)
					{
						unset($chongzuo_arr[$k]);
					}
				}
				$member_process->where($mp_where)->save(array('process_desc'=>json_encode($new_desc),'chongzuo_id'=>implode(",",$chongzuo_arr)));
			}
			// print_r(($new_desc));print_r($mp_where);print_r($res1);die;
			$url='index.php?g=&m=Process&a=index&project_id='.$ques_info['project_id'];;
		}
			// $member_enrol=M('member_enrol');
			// $me_where['member_id']=$_SESSION['member']['member_id'];
			// $me_where['project_id']=$ques_info['project_id'];
			// $me_where['project_type']=$ques_info['project_type'];
			// $member_enrol->where($me_where)->save(array('state'=>'审核中'));
			// $url='index.php?g=&m=project&a=project_show&id='.$ques_info['project_id'];;

		//vv(json_decode($mp_info['process_desc'],true));
		//vv($new_desc);
		if($isok)
		{
			echo $url;die;
			echo '提交成功';
		}else
		{
			echo $url;die;
			echo '提交失败';
		}
	}
	
	//签到
	public function qiandao_ajax()
	{

		$member_process=M('member_process');
		$project_process=M('project_process');
		$project=M('project');
		$member_enrol=M('member_enrol');

		//根据新传的项目id 进行
		$project_id=$_GET['project_id'];
		//取出所有流程
		$process_list=$project_process->where("project_id=$project_id")->order('process_id asc,type asc')->select();
		//项目详情
		$project_info=$project->where("id=$project_id")->find();

		//取出用户的流程
		$where_m['project_id']=$project_id;
		$where_m['member_id']=$_SESSION['member']['member_id']?$_SESSION['member']['member_id']:$_SESSION['member_id'];
		$member_process_list=$member_process->where($where_m)->find();
		//取出完整的流程记录
		$member_process_desc=json_decode($member_process_list['process_desc'],true);
		//报名信息
		$enrol_info=$member_enrol->where($where_m)->find();
		//查看记录流程个数
		$count=count($member_process_desc);
		if($count)
		{
			$count=$count;
		}else{
			$count=0;
		}

		//根据个数查看第n个记录是否是扫描任务
		$name=$process_list[$count]['name'];
		$process_id=$process_list[$count]['process_id'];
		$process_type=$process_list[$count]['type'];

		//如果之前没有记录shengcheng数组
		$data['member_id']=$_SESSION['member']['member_id'];
		$data['project_id']=$project_info['id'];
		$data['project_name']=$project_info['title'];
		$data['project_type']=$project_info['type'];
		$data['add_time']=time();
		$data['sub_num']=1;
		//流程中的签到扫描 判断二维码扫描时间
		if($enrol_info['state']=='项目进行中' && $name=='签到扫描')
		{
	
				//符合时间继续
				$process_desc[$process_id]['mp_id']=$process_id;
				$process_desc[$process_id]['mp_desc']='签到成功';
				$process_desc[$process_id]['mp_time']=time();
				$data['process_desc']=json_encode($process_desc);
				//$member_process->add($data);


				$mp_where['member_id']=$_SESSION['member']['member_id'];
				$mp_where['project_id']=$project_info['id'];
				$mp_info=$member_process->where($mp_where)->find();
				// print_r($process_desc);die;
				//流程写入之后开始改变报名者的状态
				if(empty($mp_info['process_desc']))
				{
					$isok=$this->v_member_process($data['project_id'],$process_desc);
				}else
				{
					$new_desc=$process_desc+json_decode($mp_info['process_desc'],true);
					$isok=$member_process->where($mp_where)->save(array('process_desc'=>json_encode($new_desc),'sub_num'=>count($new_desc)));	//修改信息
				}
				//重做修改
				if($isok)
				{
					$chongzuo=explode(',',$mp_info['chongzuo_id']);
					if($chongzuo)
					{
						foreach($chongzuo as $k => $v)
						{
							if($_GET['process_id']==$v)
							{
								unset($chongzuo[$k]);
							}else{
								$chongzuo_id=",".$v;
							}
						}
						//更新重做id
						$isok=$member_process->where($mp_where)->save(array('chongzuo_id'=>$chongzuo_id));
					}
				}

				if ($count+1 == count($process_list)) {
					Header("Location: ".U('process/index',array('project_id'=>$project_info['id'],'type'=>$process_type)));
				} else {
					$data_retun['title']=$project_info['title'];
					$data_retun['msg']='签到扫描成功，请遵照流程进行下一步。';
					$data_retun['url']=U('process/index',array('project_id'=>$project_info['id'],'type'=>$process_type));
					$this->assign("data",$data_retun);
					$this->display('qiandao_ajax');
				}
		}else{
			//提示扫描正确的二维码
				$data_retun['title']=$project_info['title'];
				$data_retun['msg']='请扫描正确的二维码（签到扫描）！';
				$data_retun['url']=U('process/index',array('project_id'=>$project_info['id'],'type'=>$process_type));
				$this->assign("data",$data_retun);
				$this->display();
		}




		// $pp_info=$project_process->where(array('process_id'=>$_GET['process_id']))->find();
		// $project_info=$project->where(array('id'=>$pp_info['project_id']))->find();
		// $data['member_id']=$_SESSION['member']['member_id'];
		// $data['project_id']=$project_info['id'];
		// $data['project_name']=$project_info['title'];
		// $data['project_type']=$project_info['type'];
		// $data['add_time']=time();
		// $data['sub_num']=1;
		// //流程中的签到扫描 判断二维码扫描时间
		// $where_q['project_id']=$project_info['id'];
		// $where_q['member_id']=$_SESSION['member']['member_id'];
		// $member_enrol=M('member_enrol');
		// $enrol_info=$member_enrol->where($where_q)->find();
		// $enrol_start_time=$enrol_info['enrol_start_time'];
		// if($enrol_info['baoming']=='1' && $enrol_info['state']=='项目进行中')
		// {
		// 	//时间正确执行以下操作
		// 	//目前时间
		// 	$date_now=date('Y-m-d',time());
		// 	//流程时间
		// 	if(is_numeric($pp_info['start_date']))
		// 	{
		// 		//如果是天数
		// 		$process_date=strtotime($enrol_start_time)+($pp_info['start_date']-1)*3600*24;

		// 	}else{
		// 		$process_date=$pp_info['start_date'];
		// 	}
		// 	if(date('Y-m-d',$process_date)!=$date_now)
		// 	{
		// 		//如果扫码不是今天的 就提示错误
		// 		//提示扫描正确的二维码
		// 		$data_retun['title']=$project_info['title'];
		// 		$data_retun['msg']='请扫描正确的二维码（签到扫描）！';
		// 		$data_retun['url']=U('process/index',array('project_id'=>$pp_info['project_id'],'type'=>$pp_info['type']));
		// 		$this->assign("data",$data_retun);
		// 		$this->display();

		// 	}else{
		// 		//符合时间继续
		// 		$process_desc[$_GET['process_id']]['mp_id']=$_GET['process_id'];
		// 		$process_desc[$_GET['process_id']]['mp_desc']='签到成功';
		// 		$process_desc[$_GET['process_id']]['mp_time']=time();
		// 		$data['process_desc']=json_encode($process_desc);
		// 		//$member_process->add($data);


		// 		$mp_where['member_id']=$_SESSION['member']['member_id'];
		// 		$mp_where['project_id']=$project_info['id'];
		// 		$mp_info=$member_process->where($mp_where)->find();
		// 		//流程写入之后开始改变报名者的状态
		// 		if(empty($mp_info))
		// 		{
		// 			$isok=$this->v_member_process($data['project_id'],$process_desc);
		// 		}else
		// 		{
		// 			$new_desc=$process_desc+json_decode($mp_info['process_desc'],true);
		// 			$isok=$member_process->where($mp_where)->save(array('process_desc'=>json_encode($new_desc),'sub_num'=>count($new_desc)));	//修改信息
		// 		}
		// 		//重做修改
		// 		if($isok)
		// 		{
		// 			$chongzuo=explode(',',$mp_info['chongzuo_id']);
		// 			if($chongzuo)
		// 			{
		// 				foreach($chongzuo as $k => $v)
		// 				{
		// 					if($_GET['process_id']==$v)
		// 					{
		// 						unset($chongzuo[$k]);
		// 					}else{
		// 						$chongzuo_id=",".$v;
		// 					}
		// 				}
		// 				//更新重做id
		// 				$isok=$member_process->where($mp_where)->save(array('chongzuo_id'=>$chongzuo_id));
		// 			}
		// 		}
		// 		Header("Location: ".U('process/index',array('project_id'=>$pp_info['project_id'],'type'=>$pp_info['type']))); 
		// 	}
			
				
		// }else{
		// 	//提示扫描正确的二维码
		// 		$data_retun['title']=$project_info['title'];
		// 		$data_retun['msg']='请扫描正确的二维码（签到扫描）！';
		// 		$data_retun['url']=U('process/index',array('project_id'=>$pp_info['project_id'],'type'=>$pp_info['type']));
		// 		$this->assign("data",$data_retun);
		// 		$this->display();
		// }


	}


	//使用扫描收货
	public function shouhuo_ajax()
	{
		//试用品
	// 	$member_process=M('member_process');
	// 	$project_process=M('project_process');
	// 	$project=M('project');
	 
	// 	$member_enrol_info=$member_enrol->where(array('project_id'=>$_GET['project_id'],'member_id'=>$_SESSION['member']['member_id']))->find();
	// 	$project_id=$member_enrol_info['project_id'];
	// 	//找到使用扫描的process_id
	// 	$p=$project_process->where(array('project_id'=>$project_id,'name'=>'使用扫描'))->find();
	 //	$process_id=$p['process_id'];
		
	 //	$pp_info=$project_process->where(array('process_id'=>$process_id))->find();
	// 	$project_info=$project->where(array('id'=>$project_id))->find();
	// 	$data['member_id']=$_SESSION['member']['member_id'];
	// 	$data['project_id']=$project_info['id'];
	// 	$data['project_name']=$project_info['title'];
	// 	$data['project_type']=$project_info['type'];
	// 	$data['add_time']=time();
	// 	$data['sub_num']=1;
		
	// 	$process_desc[$process_id]['mp_id']=$process_id;
	// 	$process_desc[$process_id]['mp_desc']='使用扫描成功';
	// 	$process_desc[$process_id]['mp_time']=time();
	// 	$data['process_desc']=json_encode($process_desc);
	// //	$res=$member_process->add($data);
	// 	$mp_where['member_id']=$_SESSION['member']['member_id'];
	// 	$mp_where['project_id']=$project_id;
	// 	$mp_info=$member_process->where($mp_where)->find();
	// 	//流程写入之后开始改变报名者的状态
	// 	if(empty($mp_info))
	// 	{
	// 		$isok=$this->v_member_process($project_id,$process_desc);
	// 	}else
	// 	{
	// 		$new_desc=$process_desc+json_decode($mp_info['process_desc'],true);
	// 		$isok=$member_process->where($mp_where)->save(array('process_desc'=>json_encode($new_desc),'sub_num'=>count($new_desc)));	//修改信息
	// 	}
		// if($isok)
		// {
			$member_enrol=M('member_enrol');
			
			$member_enrol_info=$member_enrol->where(array('project_id'=>$_GET['project_id'],'member_id'=>$_SESSION['member']['member_id']))->find();
			if($member_enrol_info['baoming']=='1'&&$member_enrol_info['state']=='扫码签收试用品')
			{
			$da['state']='项目进行中';
			//今天的时间
			$da['enrol_start_time']=date('Y-m-d',strtotime('+1 day'));
			$member_enrol->where(array('enrol_id'=>$member_enrol_info['enrol_id']))->save($da);
			//Header("Location: ".U('process/index',array('project_id'=>$member_enrol_info['project_id'],'type'=>$member_enrol_info['project_type']))); 

			$data_retun['title']=$project_info['title'];
			$data_retun['msg']='产品签收成功，请遵照流程试用并反馈。';
			$data_retun['url']=U('process/index',array('project_id'=>$member_enrol_info['project_id'],'type'=>$member_enrol_info['project_type']));
			$this->assign("data",$data_retun);
			$this->display('qiandao_ajax');
			}else{
				//提示扫描正确的二维码
				$data_retun['title']=$project_info['title'];
				$data_retun['msg']='请扫描正确的二维码（扫码签收试用品）！';
				$data_retun['url']=U('project/project_show',array('id'=>$member_enrol_info['project_id']));
				$this->assign("data",$data_retun);
				$this->display('qiandao_ajax');
			}
			
		// }
		// //重做修改
		// if($isok)
		// {
		// 	$chongzuo=explode(',',$mp_info['chongzuo_id']);
		// 	if($chongzuo)
		// 	{
		// 		foreach($chongzuo as $k => $v)
		// 		{
		// 			if($_POST['process_id']==$v)
		// 			{
		// 				unset($chongzuo[$k]);
		// 			}else{
		// 				$chongzuo_id=",".$v;
		// 			}
		// 		}
		// 		//更新重做id
		// 		$isok=$member_process->where($mp_where)->save(array('chongzuo_id'=>$chongzuo_id));
		// 	}
		// }

		
		
		// echo '操作成功！';
		// die;
		
		/*vv($data);
		vv($project_info);
		vv($pp_info);
		echo $_GET['process_id'];*/
	}
	
	//使用扫描   注释于20170829
	// public function shiyong_ajax()
	// {
	// 	$member_process=M('member_process');
	// 	$project_process=M('project_process');
	// 	$project=M('project');
	// 	$mp_where['member_id']=$_SESSION['member']['member_id'];
	// 	$mp_where['project_id']=$_GET['project_id'];
	// 	//项目流程信息
	// 	$pp_info=$project_process->where(array('process_id'=>$_GET['process_id']))->find();
	// 	//参加流程信息
	// 	$mp_info=$member_process->where($mp_where)->find();
	// 	$where_p['id']=$_GET['project_id'];
	// 	$project_info=$project->field('title,id,type')->where($where_p)->find();
	// 	//流程中的签到扫描 判断二维码扫描时间
	// 	$where_q['project_id']=$project_info['id'];
	// 	$where_q['member_id']=$_SESSION['member']['member_id'];
	// 	$member_enrol=M('member_enrol');
	// 	$enrol_info=$member_enrol->where($where_q)->find();
	// 	if($enrol_info['baoming']=='1' && $enrol_info['state']=='项目进行中')
	// 	{
	// 		$mp_data[$_GET['process_id']]['mp_id']=$_GET['process_id'];
	// 		$mp_data[$_GET['process_id']]['mp_desc']='使用扫描成功';
	// 		//$mp_data[$_POST['process_id']]['mp_type']=$_POST['process_id'];
	// 		//$mp_data[$_POST['process_id']]['mp_mq_id']=$ques_info['member_ques_id'];
	// 		$mp_data[$_GET['process_id']]['mp_time']=time();
			
	// 		if(empty($mp_info))
	// 		{
	// 			$isok=$this->v_member_process($_GET['project_id'],$mp_data);
	// 		}else
	// 		{
	// 			$new_desc=$mp_data+json_decode($mp_info['process_desc'],true);
	// 			$isok=$member_process->where($mp_where)->save(array('process_desc'=>json_encode($new_desc),'sub_num'=>count($new_desc)));	//修改信息
	// 		}

	// 		Header("Location: ".U('process/index',array('project_id'=>$_GET['project_id'],'type'=>$project_info['type']))); 
	// 	}else{
	// 		//提示扫描正确的二维码
	// 			$data_retun['title']=$project_info['title'];
	// 			$data_retun['msg']='请扫描正确的二维码（使用扫描）！';
	// 			$data_retun['url']=U('process/index',array('project_id'=>$_GET['project_id'],'type'=>$project_info['type']));
	// 			$this->assign("data",$data_retun);
	// 			$this->display('qiandao_ajax');
	// 	}

	// }
	// 
	// 
	// 
	// 
	
	public function shiyong_ajax()
	{

		$member_process=M('member_process');
		$project_process=M('project_process');
		$project=M('project');
		$member_enrol=M('member_enrol');

		//根据新传的项目id 进行
		$project_id=$_GET['project_id'];
		//取出所有流程
		$process_list=$project_process->where("project_id=$project_id")->order('type asc,process_id asc')->select();
		//项目详情
		$project_info=$project->where("id=$project_id")->find();

		//取出用户的流程
		$where_m['project_id']=$project_id;
		$where_m['member_id']=$_SESSION['member']['member_id']?$_SESSION['member']['member_id']:$_SESSION['member_id'];
		$member_process_list=$member_process->where($where_m)->find();
		//取出完整的流程记录
		$member_process_desc=json_decode($member_process_list['process_desc'],true);
		//报名信息
		$enrol_info=$member_enrol->where($where_m)->find();
		//查看记录流程个数
		$count=count($member_process_desc);
		if($count)
		{
			$count=$count;
		}else{
			$count=0;
		}

		//根据个数查看第n个记录是否是扫描任务
		$name=$process_list[$count]['name'];
		$process_id=$process_list[$count]['process_id'];
		$process_type=$process_list[$count]['type'];

		//如果之前没有记录shengcheng数组
		$data['member_id']=$_SESSION['member']['member_id'];
		$data['project_id']=$project_info['id'];
		$data['project_name']=$project_info['title'];
		$data['project_type']=$project_info['type'];
		$data['add_time']=time();
		$data['sub_num']=1;
		//流程中的签到扫描 判断二维码扫描时间
		if($enrol_info['state']=='项目进行中' && $name=='使用扫描')
		{
	
				//符合时间继续
				$process_desc[$process_id]['mp_id']=$process_id;
				$process_desc[$process_id]['mp_desc']='使用扫描成功';
				$process_desc[$process_id]['mp_time']=time();
				$data['process_desc']=json_encode($process_desc);
				//$member_process->add($data);


				$mp_where['member_id']=$_SESSION['member']['member_id'];
				$mp_where['project_id']=$project_info['id'];
				$mp_info=$member_process->where($mp_where)->find();
				// print_r($process_desc);die;
				//流程写入之后开始改变报名者的状态
				if(empty($mp_info['process_desc']))
				{
					$isok=$this->v_member_process($data['project_id'],$process_desc);
				}else
				{
					$new_desc=$process_desc+json_decode($mp_info['process_desc'],true);
					$isok=$member_process->where($mp_where)->save(array('process_desc'=>json_encode($new_desc),'sub_num'=>count($new_desc)));	//修改信息
				}
				//重做修改
				if($isok)
				{
					$chongzuo=explode(',',$mp_info['chongzuo_id']);
					if($chongzuo)
					{
						foreach($chongzuo as $k => $v)
						{
							if($_GET['process_id']==$v)
							{
								unset($chongzuo[$k]);
							}else{
								$chongzuo_id=",".$v;
							}
						}
						//更新重做id
						$isok=$member_process->where($mp_where)->save(array('chongzuo_id'=>$chongzuo_id));
					}
				}

				if ($count+1 == count($process_list)) {
					Header("Location: ".U('process/index',array('project_id'=>$project_info['id'],'type'=>$process_type)));
				} else {
					$data_retun['title']=$project_info['title'];
					$data_retun['msg']='使用扫描成功，请遵照流程进行下一步。';
					$data_retun['url']=U('process/index',array('project_id'=>$project_info['id'],'type'=>$process_type));
					$this->assign("data",$data_retun);
					$this->display('qiandao_ajax');
				}
		}else{
			//提示扫描正确的二维码
				$data_retun['title']=$project_info['title'];
				$data_retun['msg']='请扫描正确的二维码（使用扫描）！';
				$data_retun['url']=U('process/index',array('project_id'=>$project_info['id'],'type'=>$process_type));
				$this->assign("data",$data_retun);
				$this->display();
		}




		// $pp_info=$project_process->where(array('process_id'=>$_GET['process_id']))->find();
		// $project_info=$project->where(array('id'=>$pp_info['project_id']))->find();
		// $data['member_id']=$_SESSION['member']['member_id'];
		// $data['project_id']=$project_info['id'];
		// $data['project_name']=$project_info['title'];
		// $data['project_type']=$project_info['type'];
		// $data['add_time']=time();
		// $data['sub_num']=1;
		// //流程中的签到扫描 判断二维码扫描时间
		// $where_q['project_id']=$project_info['id'];
		// $where_q['member_id']=$_SESSION['member']['member_id'];
		// $member_enrol=M('member_enrol');
		// $enrol_info=$member_enrol->where($where_q)->find();
		// $enrol_start_time=$enrol_info['enrol_start_time'];
		// if($enrol_info['baoming']=='1' && $enrol_info['state']=='项目进行中')
		// {
		// 	//时间正确执行以下操作
		// 	//目前时间
		// 	$date_now=date('Y-m-d',time());
		// 	//流程时间
		// 	if(is_numeric($pp_info['start_date']))
		// 	{
		// 		//如果是天数
		// 		$process_date=strtotime($enrol_start_time)+($pp_info['start_date']-1)*3600*24;

		// 	}else{
		// 		$process_date=$pp_info['start_date'];
		// 	}
		// 	if(date('Y-m-d',$process_date)!=$date_now)
		// 	{
		// 		//如果扫码不是今天的 就提示错误
		// 		//提示扫描正确的二维码
		// 		$data_retun['title']=$project_info['title'];
		// 		$data_retun['msg']='请扫描正确的二维码（签到扫描）！';
		// 		$data_retun['url']=U('process/index',array('project_id'=>$pp_info['project_id'],'type'=>$pp_info['type']));
		// 		$this->assign("data",$data_retun);
		// 		$this->display();

		// 	}else{
		// 		//符合时间继续
		// 		$process_desc[$_GET['process_id']]['mp_id']=$_GET['process_id'];
		// 		$process_desc[$_GET['process_id']]['mp_desc']='签到成功';
		// 		$process_desc[$_GET['process_id']]['mp_time']=time();
		// 		$data['process_desc']=json_encode($process_desc);
		// 		//$member_process->add($data);


		// 		$mp_where['member_id']=$_SESSION['member']['member_id'];
		// 		$mp_where['project_id']=$project_info['id'];
		// 		$mp_info=$member_process->where($mp_where)->find();
		// 		//流程写入之后开始改变报名者的状态
		// 		if(empty($mp_info))
		// 		{
		// 			$isok=$this->v_member_process($data['project_id'],$process_desc);
		// 		}else
		// 		{
		// 			$new_desc=$process_desc+json_decode($mp_info['process_desc'],true);
		// 			$isok=$member_process->where($mp_where)->save(array('process_desc'=>json_encode($new_desc),'sub_num'=>count($new_desc)));	//修改信息
		// 		}
		// 		//重做修改
		// 		if($isok)
		// 		{
		// 			$chongzuo=explode(',',$mp_info['chongzuo_id']);
		// 			if($chongzuo)
		// 			{
		// 				foreach($chongzuo as $k => $v)
		// 				{
		// 					if($_GET['process_id']==$v)
		// 					{
		// 						unset($chongzuo[$k]);
		// 					}else{
		// 						$chongzuo_id=",".$v;
		// 					}
		// 				}
		// 				//更新重做id
		// 				$isok=$member_process->where($mp_where)->save(array('chongzuo_id'=>$chongzuo_id));
		// 			}
		// 		}
		// 		Header("Location: ".U('process/index',array('project_id'=>$pp_info['project_id'],'type'=>$pp_info['type']))); 
		// 	}
			
				
		// }else{
		// 	//提示扫描正确的二维码
		// 		$data_retun['title']=$project_info['title'];
		// 		$data_retun['msg']='请扫描正确的二维码（签到扫描）！';
		// 		$data_retun['url']=U('process/index',array('project_id'=>$pp_info['project_id'],'type'=>$pp_info['type']));
		// 		$this->assign("data",$data_retun);
		// 		$this->display();
		// }


	}



		//签到
	// public function shiyong_ajax()
	// {

	// 	$member_process=M('member_process');
	// 	$project_process=M('project_process');
	// 	$project=M('project');
	// 	$pp_info=$project_process->where(array('process_id'=>$_GET['process_id']))->find();
	// 	$project_info=$project->where(array('id'=>$pp_info['project_id']))->find();
	// 	$data['member_id']=$_SESSION['member']['member_id'];
	// 	$data['project_id']=$project_info['id'];
	// 	$data['project_name']=$project_info['title'];
	// 	$data['project_type']=$project_info['type'];
	// 	$data['add_time']=time();
	// 	$data['sub_num']=1;
	// 	//流程中的签到扫描 判断二维码扫描时间
	// 	$where_q['project_id']=$project_info['id'];
	// 	$where_q['member_id']=$_SESSION['member']['member_id']?$_SESSION['member']['member_id']:$_SESSION['member_id'];

	// 	$member_enrol=M('member_enrol');
	// 	$enrol_info=$member_enrol->where($where_q)->find();
	// 	//shijian
	// 	$enrol_start_time=$enrol_info['enrol_start_time'];
	// 	if(($enrol_info['baoming']=='1'||$enrol_info['baoming']=='2') && $enrol_info['state']=='项目进行中')
	// 	{
	// 		//时间正确执行以下操作
	// 		//目前时间
	// 		$date_now=date('Y-m-d',time());
	// 		//流程时间
	// 		if(is_numeric($pp_info['start_date']))
	// 		{
	// 			//如果是天数
	// 			$process_date=strtotime($enrol_start_time)+($pp_info['start_date']-1)*3600*24;

	// 		}else{
	// 			$process_date=$pp_info['start_date'];
	// 			$process_date=strtotime($pp_info['start_date']);
	// 		}
	// 		if($process_date>time())
	// 		{
	// 			//如果扫码不是今天的 就提示错误
	// 			//提示扫描正确的二维码
	// 			$data_retun['title']=$project_info['title'];
	// 			$data_retun['msg']='请扫描正确的二维码（使用扫描）！';
	// 			$data_retun['url']=U('process/index',array('project_id'=>$pp_info['project_id'],'type'=>$pp_info['type']));
	// 			$this->assign("data",$data_retun);
	// 			$this->display();

	// 		}else{
	// 			//符合时间继续
	// 			$process_desc[$_GET['process_id']]['mp_id']=$_GET['process_id'];
	// 			$process_desc[$_GET['process_id']]['mp_desc']='使用扫描成功';
	// 			$process_desc[$_GET['process_id']]['mp_time']=time();
	// 			$data['process_desc']=json_encode($process_desc);
	// 			//$member_process->add($data);


	// 			$mp_where['member_id']=$_SESSION['member']['member_id'];
	// 			$mp_where['project_id']=$project_info['id'];
	// 			$mp_info=$member_process->where($mp_where)->find();
	// 			//流程写入之后开始改变报名者的状态
	// 			if(empty($mp_info))
	// 			{
	// 				$isok=$this->v_member_process($data['project_id'],$process_desc);
	// 			}else
	// 			{
	// 				$new_desc=$process_desc+json_decode($mp_info['process_desc'],true);
	// 				$isok=$member_process->where($mp_where)->save(array('process_desc'=>json_encode($new_desc),'sub_num'=>count($new_desc)));	//修改信息
	// 			}
	// 			//重做修改
	// 			if($isok)
	// 			{
	// 				$chongzuo=explode(',',$mp_info['chongzuo_id']);
	// 				if($chongzuo)
	// 				{
	// 					foreach($chongzuo as $k => $v)
	// 					{
	// 						if($_GET['process_id']==$v)
	// 						{
	// 							unset($chongzuo[$k]);
	// 						}else{
	// 							$chongzuo_id=",".$v;
	// 						}
	// 					}
	// 					//更新重做id
	// 					$isok=$member_process->where($mp_where)->save(array('chongzuo_id'=>$chongzuo_id));
	// 				}
	// 			}
	// 			Header("Location: ".U('process/index',array('project_id'=>$pp_info['project_id'],'type'=>$pp_info['type']))); 
	// 		}
			
				
	// 	}else{
	// 		//提示扫描正确的二维码
	// 			$data_retun['title']=$project_info['title'];
	// 			$data_retun['msg']='请扫描正确的二维码（使用扫描）！';
	// 			$data_retun['url']=U('process/index',array('project_id'=>$pp_info['project_id'],'type'=>$pp_info['type']));
	// 			$this->assign("data",$data_retun);
	// 			$this->display();
	// 	}



	// }
	
	
	//照片上传
	public function photo()
	{
		$project_process=M('project_process');
		$process_info=$project_process->where(array('process_id'=>$_GET['id']))->find();
		
		$this->assign("process_info",$process_info);
		$this->assign("id",$_GET);
		// print_r($process_info);
		$this->display();
	}
	
	public function photo_ajax()
	{
		$member_process=M('member_process');
		$mp_where['member_id']=$_SESSION['member']['member_id'];
		$mp_where['project_id']=$_POST['project_id'];
		$mp_info=$member_process->where($mp_where)->find();
		
		$mp_data[$_POST['process_id']]['mp_id']=$_POST['process_id'];
		$mp_data[$_POST['process_id']]['mp_desc']=$_POST['thumb'];
		//$mp_data[$_POST['process_id']]['mp_type']=$_POST['process_id'];
		//$mp_data[$_POST['process_id']]['mp_mq_id']=$ques_info['member_ques_id'];
		$mp_data[$_POST['process_id']]['mp_time']=time();
		
		
		if(empty($mp_info))
		{
			$isok=$this->v_member_process($_POST['project_id'],$mp_data);
		}else
		{
			$new_desc=$mp_data+json_decode($mp_info['process_desc'],true);
			$isok=$member_process->where($mp_where)->save(array('process_desc'=>json_encode($new_desc),'sub_num'=>count($new_desc)));	//修改信息
		}
		//重做修改
		if($isok)
		{
			$chongzuo=explode(',',$mp_info['chongzuo_id']);
			if($chongzuo)
			{
				foreach($chongzuo as $k => $v)
				{
					if($_POST['process_id']==$v)
					{
						unset($chongzuo[$k]);
					}else{
						$chongzuo_id=",".$v;
					}
				}
				//更新重做id
				$isok=$member_process->where($mp_where)->save(array('chongzuo_id'=>$chongzuo_id));
			}
		}
		
		//vv($_GET);
		if($isok)
		{
			echo '提交成功';
		}else
		{
			echo '提交失败';
		}
	}
	
	//视频上传
	public function video()
	{
		$project_process=M('project_process');
		$process_info=$project_process->where(array('process_id'=>$_GET['id']))->find();
		//vv($process_info);
		$this->assign("process_info",$process_info);
		$this->assign("id",$_GET);
		$this->display();
	}
	public function video_ajax()
	{
		$member_process=M('member_process');
		$mp_where['member_id']=$_SESSION['member']['member_id'];
		$mp_where['project_id']=$_POST['project_id'];
		//$mp_where['ques_type']=$_GET['type'];
		$mp_info=$member_process->where($mp_where)->find();
		
		foreach($_POST['move'] as $k=>$v)
		{
			$move[$k]['move']=$v;
			$move[$k]['move_type']=$_POST['move_type'][$k];
		}
		
		$mp_data[$_POST['process_id']]['mp_id']=$_POST['process_id'];
		$mp_data[$_POST['process_id']]['mp_desc']=$move;
		$mp_data[$_POST['process_id']]['mp_time']=time();
		
		if(empty($mp_info))
		{
			$isok=$this->v_member_process($_POST['project_id'],$mp_data);
		}else
		{
			$new_desc=$mp_data+json_decode($mp_info['process_desc'],true);
			$isok=$member_process->where($mp_where)->save(array('process_desc'=>json_encode($new_desc),'sub_num'=>count($new_desc)));	//修改信息
		}
		
		//重做修改
		if($isok)
		{
			$chongzuo=explode(',',$mp_info['chongzuo_id']);
			if($chongzuo)
			{
				foreach($chongzuo as $k => $v)
				{
					if($_POST['process_id']==$v)
					{
						unset($chongzuo[$k]);
					}else{
						$chongzuo_id=",".$v;
					}
				}
				//更新重做id
				$isok=$member_process->where($mp_where)->save(array('chongzuo_id'=>$chongzuo_id));
			}
		}
		
		//vv($_GET);
		if($isok)
		{
			echo '提交成功';
		}else
		{
			echo '提交失败';
		}
	}
	
	
	

	
	
	//产品列表
	public function project_list()
	{
		$project=M('project');
		$project_list=$project->where()->select();
		//vv($project_list[0]);
		$this->assign("project_list",$project_list);
		$this->display();
	}
	
	//项目详情
	public function project_show()
	{
		$project=M('project');
		$project_info=$project->where(array('id'=>$_GET['id']))->find();
		//vv($project_info);

		$this->assign("project_info",$project_info);
		$this->display();
	}


	//环境监测数据
	public function airradio()
	{
		$member_enrol=M('member_enrol');
		$where['member_id']=$_SESSION['member']['member_id'];
		$where['project_id']=$_GET['project_id'];
		$enrol_info=$member_enrol->field('device_id')->where($where)->find();
		$device_id=$enrol_info['device_id'];
		$link=mysql_connect("localhost","phpclient","phppassword"); 
		if(!$link) echo "FAILD!".mysql_error(); 
		mysql_select_db('environment',$link);
		if($device_id)
		{
			$sql="select `temperature`,`humility`,`pm25`,`pm10` from airradio where `device_id`='".$device_id."' order by `utc_time` desc limit 1 ";
			$res=mysql_query($sql);
			$row=mysql_fetch_assoc($res);
		}
		
		mysql_close($link);

		$this->assign("row",$row);
		$this->assign("device_id",$device_id);
		
		$this->display();
	}
		//环境监测数据
	public function airradio_ajax()
	{

		$member_process=M('member_process');
		$mp_where['member_id']=$_SESSION['member']['member_id'];
		$mp_where['project_id']=$_POST['project_id'];
		$mp_info=$member_process->where($mp_where)->find();
		//记录提交的值
		$desc['temperature']=$_POST['temperature'];
		$desc['humility']=$_POST['humility'];
		$desc['pm25']=$_POST['pm25'];
		$desc['pm10']=$_POST['pm10'];

		$mp_data[$_POST['process_id']]['mp_id']=$_POST['process_id'];
		$mp_data[$_POST['process_id']]['mp_desc']=$desc;
		$mp_data[$_POST['process_id']]['mp_time']=time();
		
		if(empty($mp_info))
		{
			$isok=$this->v_member_process($_POST['project_id'],$mp_data);
		}else
		{
			$new_desc=$mp_data+json_decode($mp_info['process_desc'],true);
			$isok=$member_process->where($mp_where)->save(array('process_desc'=>json_encode($new_desc),'sub_num'=>count($new_desc)));	//修改信息
		}
		
		//重做修改
		if($isok&&$mp_info)
		{
			$chongzuo=explode(',',$mp_info['chongzuo_id']);
			if($chongzuo)
			{
				foreach($chongzuo as $k => $v)
				{
					if($_POST['process_id']==$v)
					{
						unset($chongzuo[$k]);
					}else{
						$chongzuo_id=",".$v;
					}
				}
				//更新重做id
				$isok=$member_process->where($mp_where)->save(array('chongzuo_id'=>$chongzuo_id));
			}
		}

		if($isok)
		{
			echo '提交成功';
		}else
		{
			echo '提交失败';
		}
	}


}


