<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class AjaxController extends AdminbaseController{
	
	function _initialize() {
		parent::_initialize();
	}
	
	//设置is
	public function set_is()
	{
		//vv($_POST);
		$model=M($_POST['from']);
		echo $model->where(array($_POST['id_name']=>$_POST['id']))->save(array($_POST['name']=>$_POST['is']));
	}
	
	public function ajax_selectques_info()
	{
		//性别表（sexs）
		$sexs=sexs();		//获取标签
		$this->assign("sexs",$sexs);
		
		$id=$_REQUEST['id'];
		$model=M('ques');
		$where['ques_id']=$id;
		$selectques_info=$model->where($where)->find();
		$zimu=array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t');
		//修改的时候逻辑跳转
		$where_queslist['project_id']=$selectques_info['project_id'];
		$where_queslist['type']=$selectques_info['type'];
		$where_queslist['num']=$selectques_info['num'];
		$ques_list=$model->where($where_queslist)->order('sort asc')->select();
		//跳转字段反序列化
		if($selectques_info['turn_to'])
		{
			$selectques_info['turn_to']=unserialize($selectques_info['turn_to']);
		}

		foreach($zimu as $k=>$v)
		{
			//echo $v.'111';
			if(!empty($selectques_info[$v]))
			{
				$html.='<div>';
				$html.='<b style=" font-size:14px !important;width: 15px; display: inline-block; text-align:center" class="zimu_b">'.strtoupper($v).'</b>';
				$html.='：<input type="text" name="post['.strtoupper($v).']" value="'.$selectques_info[$v].'" class="zimu_input" placeholder="请输入答案内容" style="width: 180px;">';
				$html.='<label class="radio inline">';
					if (strpos($selectques_info['daan'], strtoupper($v)) === FALSE) {
						$html.='<input type="checkbox" name="post[daan][]" class="daan_input" value="'.strtoupper($v).'" />符合';
					} else {
						$html.='<input type="checkbox" name="post[daan][]" class="daan_input" checked value="'.strtoupper($v).'" />符合';
					}
				$html.='</label>';
				$html.='<a class="btn" onClick="del(this)" href="javascript:">移除</a>';
				//逻辑跳转
				$html.='&nbsp;&nbsp;&nbsp;跳转至<select name="post[turn_to][]" display="inline"  style="width:120px">';
				$html.='<option value="">请选择</option>';
                            foreach ($ques_list as $key => $value){
                            	if($selectques_info['turn_to'][$v]==$value['ques_id'])
                            	{
                            		$html.='<option selected value='.$value['ques_id'].'>'.$value['name'].'</option>';
                            	}else{
                            		$html.='<option value='.$value['ques_id'].'>'.$value['name'].'</option>';
                            	}
                            
                            }
							
                           
				$html.='</select><br>';
				$html.='</div>';
				$zimu_arr[$v]=$selectques_info[$v];
			}
		}
		$selectques_info['answer']=$zimu_arr;
		$selectques_info['html']=$html;
		echo json_encode($selectques_info);die;
	}
	
	//添加选择题
	public function ajax_select()
	{
		$model=M('ques');
		$num=$_GET['num'];
		if (IS_POST) 
		{
			$array=I("post.post");
			$array['daan']=implode("|",$array['daan']);		//答案
			//vv($array);die;
			$zimu=array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t');
			foreach($zimu as $k=>$v)
			{
				if(empty($array[strtoupper($v)]))
				{
					$array[strtoupper($v)]='';
				}
			}
			//逻辑跳转
			foreach($array['turn_to'] as  $key => $val)
			{
				if($val)
				{
					$str_arr[$zimu[$key]]=$val;
				}
			}
			if($str_arr)
			$array['turn_to']=serialize($str_arr);

			//sort增加
			$where['project_id']=$array['project_id'];
			$where['type']=$array['type'];
			$ques_sort=$model->field('sort')->where($where)->order('sort desc')->limit('0','1')->find();
			if($ques_sort&&$ques_sort['sort']>0&&empty($_POST['ques_id']))
			{
				$array['sort']=$ques_sort['sort']+1;
			}else{
				$array['sort']=1;
			}
			if(!empty($_POST['ques_id']))
			{
				unset($array['sort']);
			}
			if($num)
			{
				$array['num']=$num;
			}
			//名字
			$project_id=$array['project_id'];
			$project=M('project');
			$project_info=$project->field('project_no,type')->where("id=$project_id")->find();
			if($array['type']=='1')
			$array['ques_nam']=$project_info['project_no']."-甄选表";
			if($array['type']=='2')
			$array['ques_nam']=$project_info['project_no']."-现场筛选表单";
			if($array['type']=='5')
			$array['ques_nam']=$project_info['project_no']."-调查问卷";

			
			//vv($array);die;
			if(empty($_POST['ques_id']))
			{
				$array['add_time']=time();
				$result=$model->add($array);
				$select_info=$model->where(array('ques_id'=>$result))->find();
				if($result)
					//操作记录
				$this->admin_log($_SESSION['ADMIN_ID'],'添加选择题,'.$array['ques_nam']);
			}else
			{
				$result=$model->where(array('ques_id'=>$_POST['ques_id']))->save($array);
				$select_info=$model->where(array('ques_id'=>$_POST['ques_id']))->find();
				if($result)
					//操作记录
				$this->admin_log($_SESSION['ADMIN_ID'],'修改选择题,'.$array['ques_nam']);
			}
			if ($result) {
				$this->success("操作成功！");
			} else {
				$this->error("操作失败！");
			}
			//print_r($article);
		}
		//$this->display();
	}
	
	//添加问答题
	public function ajax_answer()
	{
		$model=M('answerques');
		if (IS_POST) 
		{
			$array=I("post.post");
			
			if(empty($_POST['id']))
			{
				$array['add_time']=time();
				$result=$model->add($array);
				$answer_info=$model->where(array('answer_id'=>$result))->find();
				if($result)
					//操作记录
				$this->admin_log($_SESSION['ADMIN_ID'],'添加问答题,'.$array['ques_nam']);
			}else
			{
				$result=$model->where(array('answer_id'=>$_POST['id']))->save($array);
				$answer_info=$model->where(array('answer_id'=>$_POST['answer_id']))->find();
				if($result)
					//操作记录
				$this->admin_log($_SESSION['ADMIN_ID'],'添加问答题,'.$array['ques_nam']);
			}
			
			if ($result || !empty($answer_info)) {
				$html.='<tr>';
					$html.='<th width="80">问答题</th>';
					$html.='<td style="line-height:45px;" >';
					$html.='<b style="font-size:18px; font-weight:bold">A'.$answer_info['answer_id'].'.'.$answer_info['name'];
					$html.='<a class="btn btn-danger" style="float:right; margin:0 10px 0 0" onClick="del(this)" href="javascript:">移除</a>';
					$html.='<a class="btn btn-danger" style="float:right; margin:0 10px 0 0" onClick="answer_up(this)" href="javascript:">上移</a>';
					$html.='<a class="btn btn-danger" style="float:right; margin:0 10px 0 0" onClick="answer_down(this)" href="javascript:">下移</a>';
					$html.='</b>';
					$html.='<div id="ss">';
					$html.='<input type="hidden" name="answer[]" value="'.$answer_info['answer_id'].'">';
					$html.='<textarea name="" id="description" style="width: 50%; height: 100px;" disabled placeholder="请填写备注（备注可以写问答答案提示）">'.$answer_info['answer_desc'].'</textarea>';
					$html.='</div>';
					$html.='</td>';
				$html.='</tr>';
				echo $html;die;
				$this->success("操作成功！");
			} else {
				$this->error("操作失败！");
			}
			//print_r($article);
		}
	}
	
	
	//ajax获取项目编号
	public function ajax_project_no()
	{
		//$project_type_code=;
		if($_POST['type']!=4)
		{
			//获取项目类型
			$class=M('class');
			$class_info=$class->where(array('parent_id'=>2,'name'=>$_POST['project_type']))->find();
			
			$project_class_info=$class->where(array('parent_id'=>3,'name'=>$_POST['project_class']))->find();
			
			//获取客户
			$kehu_info=$class->where(array('parent_id'=>1,'name'=>$_POST['kehu']))->find();
			
			//判断客户的第几个项目
			$project=M('project');
			$project_num=$project->where(array('kehu'=>$_POST['kehu'],'type'=>$_POST['type']))->count();
			//防止重复的项目编号，先把编号插入到数据库 先生成的先插入，查询数据库去查重
			//查询客户的最后一个项目编号
			$project_no=$project->field("project_no")->where(array('kehu'=>$_POST['kehu'],'type'=>$_POST['type']))->order("id desc")->limit(1)->find();
			if($project_no&&$project_no['project_no'])
			$project_num=substr($project_no['project_no'],6);
			

			echo project_type_code($_POST['type']).$class_info['code'].date("y",time()).$kehu_info['code'].sprintf("%02d",$project_num+1);
		}else
		{
			//获取项目类型
			$class=M('class');
			$class_info=$class->where(array('parent_id'=>3,'name'=>$_POST['project_class']))->find();
			
			//判断客户的第几个项目
			$project=M('project');
			$project_num=$project->where(array('class'=>$_POST['project_class']))->count();
			//vv($_POST);
			//vv($project_num);
			echo project_type_code($_POST['type']).date("y",time()).$class_info['code'].sprintf("%02d",$project_num+1);
			
		}
	}
	
	//日期ajax
	//ajax area
	public function ajax_area()
	{
		$area_id=$_REQUEST['area_id'];
		$area_parent_id=$_REQUEST['area_parent_id'];
		$area=M('area');
		$where['area_parent_id']=$area_parent_id;
		//$where['area_id']=$area_id;
		
		$area_info=$area->field('area_id,area_name')->where($where)->select();
		$area_str='<option value="">请选择</option>';
		foreach($area_info as $k=>$v)
		{
			if($area_id==$v['area_id'])
			{
				$selected='selected';
			}else
			{
				$selected='';
			}
			$area_str.='<option value="'.$v['area_id'].'" '.$selected.'>'.$v['area_name'].'</option>';
			
		}
		echo $area_str;die;
	}

	//从问卷库中调取问卷
	public function ajax_select_list()
	{
		$project=M('project');
		$type=$_REQUEST['type'];
		$project_list=$project->select();
		$html="<select name='project_id' onclick='select_ques_list(this.value)' id='project_id'>";
		foreach($project_list as $k=>$v)
		{
			$html.="<option  value=".$v['id'].">".$v['title']."</option>";
		}
		$html.="</select>";
		echo $html;
	}
	//根据项目调取问卷 
	//type=1  甄别问卷
	public function ajax_ques_list()
	{
		$ques=M('ques');
		$type=$_REQUEST['type'];
		$project_id=$_REQUEST['project_id'];
		//
		$where['project_id']=$project_id;
		$where['type']=$type;
		$ques_list=$ques->where($where)->order('sort desc')->group('num')->select();
		$html="<select name='post[sel_project_id]' id='ques_id'>";
		foreach($ques_list as $k=>$v)
		{
			$html.="<option value=".$v['project_id'].",".$v['num'].">".$v['ques_nam'].$v['num']."</option>";
		}
		$html.="</select>";
		echo $html;

	}
	//选择库中的所有项目的题目进行添加
	public function ajax_select_add()
	{
		$model=M('ques');
		$array=I("post.post");
		//选择的项目id
		$project_arr=explode(',',$array['sel_project_id']);
		$project_id=$project_arr[0];
		$num=$project_arr[1];
		//选择的问题id
		$type=$_REQUEST['type'];
		$where['project_id']=$project_id;
		$where['type']=$type;
		if($num)
		{
			$where['num']=$num;
		}
		$ques_list=$model->where($where)->select();
		//去除跳转的字段 和ques_id
		$zimu=array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t');
		//查询是否有题目
		
		$where_q['project_id']=$array['project_id'];
		$where_q['type']=$type;
		// $ques_info_1=$model->field('sort,type')->where($where_q)->order('sort desc')->limit(1)->find();
		// if($ques_info_1['sort'])
		// {
		// 	$sort=$ques_info_1['sort'];
		// }else{
		// 	$sort=0;
		// }
		//名字
			$project_id=$array['project_id'];
			$project=M('project');
			$project_info=$project->field('project_no,type')->where("id=$project_id")->find();
			if($type=='1')
			$ques_name=$project_info['project_no']."-甄选表";
			if($type=='2')
			$ques_name=$project_info['project_no']."-现场筛选表单";
			if($type=='5')
			$ques_name=$project_info['project_no']."-调查问卷";
			if($type=='4')
			$ques_name=$project_info['project_no']."-反馈问卷";
		
		$ques_info_k=$model->field('num')->where($where_q)->order('num desc')->limit(1)->find();
		$max_num=$ques_info_k['num'];
		foreach ($ques_list as $key => $v) {
			// ++$sort;
			$ques_info=$v;
			foreach ($v as $k => $value) {
				if(in_array($k,$zimu))
				{
				unset($ques_info[$k]);
				$ques_info[strtoupper($k)]=$v[$k];
				}
			}
			$ques_info['project_id']=$array['project_id'];
			$ques_info['add_time']=time();
			// $ques_info['sort']=$sort;
			$ques_info['ques_nam']=$ques_name;

			if($ques_info['type']=='4')
			{

				$ques_info['num']=$max_num+1;
			}
			unset($ques_info['ques_id']);
			unset($ques_info['turn_to']);
			$result=$model->add($ques_info);
			//print_r($v);
		}

		if($result)
		{
			//操作记录
			$project=M('project');
			$where['id']=$array['project_id'];
			$res=$project->field('title')->where($where)->find();
			$this->admin_log($_SESSION['ADMIN_ID'],'选择库中的所有项目的题目进行添加,'.$res['title']);

		$this->success("操作成功！");	
		}
		else{
		$this->error("操作失败！");
		}
		
	}
	//删除问题
	public function ajax_select_del()
	{
		$model=M('ques');
		$ques_id=$_REQUEST['ques_id'];
		//delete删除返回影响的行数
	
			$where['ques_id']=$ques_id;
		$res=$model->field('ques_nam')->where($where)->find();
		$result=$model->where(array('ques_id'=>$ques_id))->delete();
		if($result)
		{
			//操作记录
		$this->admin_log($_SESSION['ADMIN_ID'],'删除问题,'.$res['ques_nam']);
		$this->success("操作成功！");	
		}
		else{
		$this->error("操作失败！");
		}

	}

		//删除流程
	public function ajax_del_process()
	{
		$model=M('project_process');
		$project=M('project');
		$ques=M('ques');
		$process_id=$_REQUEST['process_id'];
		//delete删除返回影响的行数
		$res=$model->field('ques_type,project_id,name')->where(array('process_id'=>$process_id))->find();
		$res1=$project->field('title')->where(array('id'=>$res['project_id']))->find();

		$result=$model->where(array('process_id'=>$process_id))->delete();
		if($res['name']=='问卷反馈')
		{
			$result=$ques->where(array('project_id'=>$res['project_id'],'num'=>$res['ques_type'],'type'=>'4'))->delete();
		}
		if($res['name']=='现场测试')
		{
			$result=$ques->where(array('project_id'=>$res['project_id'],'num'=>$res['ques_type'],'type'=>'2'))->delete();
		}
		if($result)
		{
			$this->admin_log($_SESSION['ADMIN_ID'],'删除项目流程,流程：'.$res['name'].",项目名：".$res1['title']);
		$this->success("操作成功！");	
		}
		else{
		$this->error("操作失败！");
		}

	}

	//修改项目状态
	public function ajax_toggle()
	{
		$model=M('project');
		$project_id=$_REQUEST['project_id'];
		$status=$_REQUEST['status'];

			$array['project_status']=$status;
		
		$res=$model->where(array('id'=>$project_id))->save($array);
		if($res)
		{
		
		$result['msg']='操作成功！';	
		}
		else{
		//$result['project_id']=$project_id;
		$result['msg']='操作失败！';	
		}
		
		$result['status']=$status;
		
		$result['project_id']=$project_id;
		die(json_encode($result));
	}	

		//选择题下移
	function select_down()
	{
		$ques_id=$_GET['ques_id'];
		$ques=M('ques');
		$where['ques_id']=$ques_id;
		$save['sort']=1;
		$ques_info=$ques->where($where)->find();
		// $save['sort']=$ques_info['sort']-1;
		// if($save['sort']=='0')
		// {
		// 	$result['msg']='移不动';
		// }
		$where_e['project_id']=$ques_info['project_id'];
		$where_e['type']=$ques_info['type'];
		// $where_e['class']=$ques_info['class'];
		$ques_list=$ques->where($where_e)->order('sort asc')->select();
		foreach($ques_list as $k=>$v)
		{
			if($v['ques_id']==$ques_id)
			{
				$save1['sort']=$ques_list[$k+1]['sort'];
				$save2['sort']=$v['sort'];
				$where2['ques_id']=$ques_list[$k+1]['ques_id'];
			}
		}
		$res1=$ques->where($where)->save($save1);
		$res2=$ques->where($where2)->save($save2);
		if($res1&&$res2)
		{
			$result['msg']='成功';
		}
		die($result['msg']);

	}	
	//选择题下移
	function select_up()
	{
		$ques_id=$_GET['ques_id'];
		$ques=M('ques');
		$where['ques_id']=$ques_id;
		$save['sort']=1;
		$ques_info=$ques->where($where)->find();
		$save['sort']=$ques_info['sort']-1;
		if($save['sort']=='0')
		{
			$result['msg']='移不动了';
		}
		$where_e['project_id']=$ques_info['project_id'];
		$where_e['type']=$ques_info['type'];
		// $where_e['class']=$ques_info['class'];
		$ques_list=$ques->where($where_e)->order('sort asc')->select();
		foreach($ques_list as $k=>$v)
		{
			if($v['ques_id']==$ques_id)
			{
				$save1['sort']=$ques_list[$k-1]['sort'];
				$save2['sort']=$v['sort'];
				$where2['ques_id']=$ques_list[$k-1]['ques_id'];
			}
		}
		$res1=$ques->where($where)->save($save1);
		$res2=$ques->where($where2)->save($save2);
		if($res1&&$res2)
		{
			$result['msg']='成功';
		}
		die($result['msg']);

	}	
}