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
 * 个人中心
 */
class MemberController extends MobilebaseController {
	
	function _initialize() {
		parent::_initialize();
		//$this->assign("active",'member');
		$appid = WX_APP_ID; 
		$secret = WX_SECRET;
	$_SESSION['yaoqing']=$_GET['member_id'];

	if(empty($_SESSION['user_openid'])||empty($_SESSION['member']['member_id']))
	{
			if (!isset($_GET["code"])) {
			//echo $_SESSION['page'];
			$u1 = "http://".WX_HOME_URL_HOST."/index.php?g=&m=member&a=index&member_id=".$_GET['member_id'];
			$u1 = urlencode($u1);
			$url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri='.$u1.'&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect';
			// die($url);
	    	 header("Location:".$url);
	    	//$this->redirect($url);
			 exit();
			}
	
	$code = $_GET["code"]; 	
	$get_token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$secret.'&code='.$code.'&grant_type=authorization_code';
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL,$get_token_url); 
	curl_setopt($ch,CURLOPT_HEADER,0); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 ); 
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); 
	$res = curl_exec($ch); 
	curl_close($ch); 
	$json_obj = json_decode($res,true); 
	
	//根据openid和access_token查询用户信息 
	$access_token = $json_obj['access_token']; 
	$openid = $json_obj['openid']; 
	
	//openid 保存到 session
	$_SESSION['user_openid'] = $openid;				//用户openid

	
	$_SESSION['user_access_token'] = $access_token;	//Access Token
	
	//获取微信用户全部信息
	$get_user_info_url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN'; 	
	$ch = curl_init(); 
	curl_setopt($ch,CURLOPT_URL,$get_user_info_url); 
	curl_setopt($ch,CURLOPT_HEADER,0); 
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1 ); 
	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, 10); 
	$res = curl_exec($ch); 
	curl_close($ch); 
	
	//解析json 
	$user_obj = json_decode($res,true);

	$_SESSION['user_header_img'] = $user_obj['headimgurl'];
	//将邀请人的id存入到session

	}
			// file_put_contents('11openid.txt', $_SESSION['user_openid'].'-'.$_SESSION['member']['member_id'],FILE_APPEND);
		//将openid存进数据库
		//如果是老用户直接登录 新用户跳转至注册页面
			// print_r($_SESSION['user_openid']);die;
			$mem=M('member');
			$where['open_id']=trim($_SESSION['user_openid']);
			$mem_info=$mem->where($where)->find();
			if($mem_info)
			{
				$_SESSION['member']['member_id']=$mem_info['member_id'];
			}else{
				//跳转至填写手机号接受验证码的页面
			}
			//如果是之前登录但是没有存open_id的用户  存入open_id
			if($_SESSION['member']['member_id']&&!$mem_info)
			{
				$where1['member_id']=$_SESSION['member']['member_id'];
			  $old_mem=$mem->where($where1)->find();			
				if(!$old_mem['open_id'])
				{
					$save['open_id']=$_SESSION['user_openid'];
					$where1['member_id']=$_SESSION['member']['member_id'];
					$mem->where($where1)->save($save);
				}
			}
	
		if(empty($_SESSION['member']))
		{

			Header("Location: ".U('login/register')."&member_id=".$_REQUEST['member_id']); 
			//$this->error("你还没有登录，请先登录！",U('login/login'));
		}else
		{
				
				if($_SESSION['member']['member_id'])
				{
					$member_info=$this->member_info($_SESSION['member']['member_id']);
				}else{
					$member_info=$this->member_info($_SESSION['member_id']);
				}
				

				//格式化直辖市
				if($member_info['area_name'])
				{
					$area_name=mb_substr(trim($member_info['area_name']),0,3,'utf-8');
					if($area_name=='上海市'||$area_name=='重庆市'||$area_name=='天津市'||$area_name=='北京市')
					{
						$member_info['area_name']=$area_name.' '.$member_info['area_name'];
					}
					$member_info['user_header_img']=$_SESSION['user_header_img'];
				}
				if(!$member_info['name'] ||!$member_info['birthday'])
				{
					$_SESSION['member_id']=$member_info['member_id'];
					//缺失基本信息跳转后弹窗
					Header("Location: ".U('login/register2')."&member_id=".$member_info['member_id']."&member_info=lost"); 
				}
				$this->assign("member_info",$member_info);
			
		}
	}
	
	
    //首页
	public function index() 
	{
		  // unset($_SESSION['member']);
		  //  unset($_SESSION['user_openid']);
		  //  unset($_SESSION['user_header_img']);
		$this->display();
    }
	
	//了解会员权益
	public function hyquanyi()
	{
		$article=M('article');
		$article_info=$article->where(array('article_id'=>10))->find();
		$this->assign("article_info",$article_info);
		$this->display();
	}
	
	//完善信息第一步
	public function perfect1()
	{
		//血型
		$blood=blood();
		$this->assign("blood",$blood);
		
		//教育程度
		$educa=educa();
		$this->assign("educa",$educa);
		
		//婚姻状态
		$marriage=marriage();
		$this->assign("marriage",$marriage);
		$this->display();
	}
	
	public function perfect1_ajax()
	{
		//vv($_POST);
		$model=M('member');
		if (IS_POST) 
		{
			$array=I("post.post");
			if(empty($array['height']))
			{
				echo '身高不能为空！';die;
			}
			if(empty($array['weight']))
			{
				echo '体重不能为空！';die;
			}
			if(empty($array['educa']))
			{
				echo '请选择教育程度！！';die;
			}
			if(empty($array['marriage']))
			{
				echo '请选择婚姻状态！';die;
			}
			
			$where['member_id']=$_SESSION['member']['member_id'];
			$model->where($where)->save($array);
			$this->member_wz($_SESSION['member']['member_id']);
			echo '操作成功！';die;
		}
		
	}
	
	//完善信息第二步
	public function perfect2()
	{
		//住房类型
		$housing=housing();
		$this->assign("housing",$housing);
		
		//职业
		$profession=profession();
		$this->assign("profession",$profession);
		
		//工作环境
		$work=work();
		$this->assign("work",$work);
		
		//个人月收入
		$month_income=f_demand_month_income();
		$this->assign("month_income",$month_income);
		
		//家庭月收入
		$family_income=f_demand_family_income();
		$this->assign("family_income",$family_income);
		
		$this->display();
	}
	public function perfect2_ajax()
	{
		//vv($_POST);
		$model=M('member');
		if (IS_POST) 
		{
			$array=I("post.post");
			if(empty($array['profession']))
			{
				echo '请选择职业！';die;
			}
			if(empty($array['work']))
			{
				echo '请选择工作环境！';die;
			}
			if(empty($array['month_income']))
			{
				echo '请选择个人月收入！';die;
			}
			if(empty($array['family_income']))
			{
				echo '请选择家庭月收入！';die;
			}
			if(!empty($_POST['new_area_name']))
			{
				$array['new_area_name']=$_POST['new_area_name'];
				$area_id[]=$_POST['seachprov'];
				$area_id[]=$_POST['homecity'];
				$area_id[]=$_POST['seachdistrict'];
				$array['new_area_id']=implode(",",$area_id);
			}
			
			
			
			//vv($array);die;
			$where['member_id']=$_SESSION['member']['member_id'];
			$model->where($where)->save($array);
			$this->member_wz($_SESSION['member']['member_id']);
			echo '操作成功！';die;
		}
	}

	//完善信息第三步
	public function perfect3()
	{
		$member_info=$this->member_info($_SESSION['member']['member_id']);
		$child_data=json_decode($member_info['child_data'],true);
		foreach($child_data as $k=>$v)
		{
			$arr=explode("-",$v['child_birthday']);
			$child_data[$k]['child_yuar']=$arr[0];
			$child_data[$k]['child_yue']=$arr[1];
		}
		//vv($child_data);
		$this->assign("child_data",$child_data);
		$this->display();
	}
	public function perfect3_ajax()
	{
		//vv($_POST);
		$model=M('member');
		if (IS_POST) 
		{
			for($i=0;$i<$_POST['child_num'];$i++)
			{
				$child_data[$i]['child_name']=$_POST['child_name'][$i];
				$child_data[$i]['child_sex']=$_POST['child_sex'][$i];
				$child_data[$i]['child_birthday']=$_POST['child_yuar'][$i].'-'.$_POST['child_yue'][$i];
				
			}
			$array['child_num']=$_POST['child_num'];		//子女数量
			$array['child_data']=json_encode($child_data);
			$array['guomin']=$_POST['guomin'];		//过敏史
			$array['manxing']=$_POST['manxing'];		//慢性疾病史
			//vv($array);die;
			$where['member_id']=$_SESSION['member']['member_id'];
			$model->where($where)->save($array);
			$this->member_wz($_SESSION['member']['member_id']);
			$jifen=M('jifen_log');
			$jifen_info=$jifen->where(array('member_id'=>$_SESSION['member']['member_id'],'type'=>2))->find();
			
			//完整度
			$mem_wanzhengdu=$model->field('wanzhengdu')->where($where)->find();
			$result['wanzhengdu']=$mem_wanzhengdu['wanzhengdu'];
			//增加积分
			if(empty($jifen_info))
			{
				$config=M('config');
				$config_info=$config->field('wanshan_jifen')->where('config_id=1')->find();
				$jifen_data['member_id']=$_SESSION['member']['member_id'];
				$jifen_data['member_name']=$_SESSION['member']['name'];
				$jifen_data['jifen']=$config_info['wanshan_jifen'];
				$jifen_data['add_time']=time();
				$jifen_data['desc']='完善资料奖励'.$config_info['wanshan_jifen'].'分';
				$jifen_data['stage']='完善资料';
				$jifen_data['type']=2;
				$jifen->add($jifen_data);
				$this->setjifen($jifen_data['jifen'],$_SESSION['member']['member_id']);
				$result['msg']='操作成功第一次！';
				
				die(json_encode($result));
				//echo '操作成功第一次！';die;
			}
			$result['msg']='操作成功！';
			// echo '操作成功！';die;
				die(json_encode($result));
			
			
		}
	}
	
	//我邀请的朋友
	public function yaoqing()
	{
		$member=M('member');	
		$where['yaoqing']=$_SESSION['member']['member_id'];
		$member_list=$member->field('name,mobile')->where($where)->select();
		$this->assign('member_list',$member_list);
		
		$this->display();
	}
		//分享
	public function fenxiang()
	{
		
		vendor('erweima.erweima');//导入类库
		$aa=new \erweima();
		$url="http://".WX_HOME_URL_HOST."/index.php?g=Mobile&m=member&a=index&member_id=".$_SESSION['member']['member_id'];
		$filename=$aa->png($url,md5($name).'.jpg','','10');
		$this->assign('imgsrc',$filename);
		$this->display();
	}
	//关注
	public function guanzhu()
	{
		
		// vendor('erweima.erweima');//导入类库
		// $aa=new \erweima();
		// $url="http://".WX_HOME_URL_HOST."/index.php?g=Mobile&m=member&a=index&member_id=".$_SESSION['member']['member_id'];
		// $filename=$aa->png($url,md5($name).'.jpg','','10');
		// $this->assign('imgsrc',$filename);
		$this->display();
	}
	
	
	//我的积分
	public function jifen()
	{
		
		$member_info=$this->member_info($_SESSION['member']['member_id']);
		//vv($member_info);
		$member_info['user_header_img']=$_SESSION['user_header_img'];
		$this->assign("member_info",$member_info);
		$this->display();
	}
	
	//积分规则
	public function jifenguize()
	{
		$article=M('article');
		$article_info=$article->where(array('article_id'=>11))->find();
		$this->assign("article_info",$article_info);
		//$this->display();
		$this->display('hyquanyi');
	}
	
	//积分明细
	public function jifenmingxi()
	{
		
		$this->display();
	}
	public function ajax_jifenmingxi()
	{
		$jifen_log=M('jifen_log');
		
		$where['member_id']=$_SESSION['member']['member_id'];
		
		$count = M('jifen_log')->where($where)->count();
		$page = $this->page($count, 10);
		$list = M('jifen_log')->where($where)->order('jifen_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		
		if($_GET['p']>$page->Total_Pages)
		{
			$data['status']=0;
			$data['msg']='没有更多数据了！';
			echo json_encode($data);die;
		}
		//vv($list);die;
		$html='';
		foreach($list as $k=>$v)
		{
			$html.='<dl>';
				$html.='<dt>'.$v['desc'].'</dt>';
				$html.='<dd>';
					$html.='<p>'.$v['jifen'].'分</p>';
					$html.='<p>'.date("Y-m-d H:i:s",$v['add_time']).'</p>';
				$html.='</dd>';
			$html.='</dl>';
		}
		
		$data['status']=1;
		$data['msg']=$html;
		echo json_encode($data);die;
		
		
	}
	
	//积分兑换记录
	public function jifenjilu()
	{
		$jifen=M('jifen_order');
		$user_id=$_SESSION['member']['member_id'];
		$where['user_id']=$user_id;
		$order_list=$jifen->field('goods_id,goods_name,jifen,order_id,order_no,add_time')->where($where)->order("add_time desc")->select();
		foreach($order_list as $k=>$v)
		{
			$order_list[$k]['add_time']=date('Y/m/d H:i',$v['add_time']);
		}
			$this->assign('order_list',$order_list);
		
		
		$this->display();
	}
	
	//物流信息
	public function wuliu()
	{
		$order_id=(int)$_GET['order_id'];
		$user_id=$_SESSION['member']['member_id'];
		$where['user_id']=$user_id;
		$where['order_id']=$order_id;
		$order=M('jifen_order');
		$order_info=$order->where($where)->find();
		if(!$order_info['kuaidi'])
		{
			$order_info['kuaidi']='暂无快递信息。';
		}
		if(!$order_info['kuaidi_no'])
		{
			$order_info['kuaidi_no']='暂无快递信息。';
		}
		$this->assign('order_info',$order_info);
		
		$this->display();
	}

	//试用品物流信息
	public function shiyong_wuliu()
	{
		$project_id=(int)$_GET['project_id'];
		$user_id=$_SESSION['member']['member_id'];
		$where['user_id']=$user_id;
		$where['project_id']=$project_id;
		$order=M('order');
		$order_info=$order->where($where)->find();
		if(!$order_info['kuaidi'])
		{
			$order_info['kuaidi']='暂无快递信息。';
		}
		if(!$order_info['kuaidi_no'])
		{
			$order_info['kuaidi_no']='暂无快递信息。';
		}
		$this->assign('order_info',$order_info);
		
		$this->display('wuliu');
	}
	
	
	//积分商品
	public function shops()
	{
		//积分商品列表
		$goods=M('goods');
		$where['on_sale']='1';
		$goods_list=$goods->where()->select();

		$this->assign("goods_list",$goods_list);
		
		$this->display();
	}
	
	//商品详情
	public function shops_cont()
	{
		$goods_id=$_GET['goods_id'];
		$where['goods_id']=$goods_id;
		$goods=M('goods');
		$goods_info=$goods->where($where)->find();
		if($goods_info['photos1'])
			$photos[]=$goods_info['photos1'];
		if($goods_info['photos2'])
			$photos[]=$goods_info['photos2'];
		if($goods_info['photos3'])
			$photos[]=$goods_info['photos3'];
		if($goods_info['photos4'])
			$photos[]=$goods_info['photos4'];

		$goods_info['html']=htmlspecialchars_decode($goods_info['date']);
		$this->assign("photos",$photos);
		$this->assign("goods_info",$goods_info);
		$this->display();
	}
		//我的收货地址
	public function member_queren()
	{
		$shouhuo=M('shouhuo');
		$member=M('member');
		$member_info=$member->field('name,mobile,new_area_name,new_address')->where(array('member_id'=>$_SESSION['member']['member_id']))->find();
		$shouhuo_info=$shouhuo->where(array('member_id'=>$_SESSION['member']['member_id']))->find();
		$default_address=$member_info['new_area_name'].$member_info['new_address'];
		if($shouhuo_info)
		{
			$shouhuo_info['address']=json_decode($shouhuo_info['address'],true);
		}else{
		$default['address']=$default_address;
		$default['is_ok']=1;
		$shouhuo_info['address'][]=$default;
		$shouhuo_info['phone']=$member_info['mobile'];
		$shouhuo_info['name']=$member_info['name'];
		}
		$this->assign("shouhuo_info",$shouhuo_info);
		print_r($shouhuo_info);
	
		$this->display();
	}
	//检查积分
	public function check_jifen()
	{
		$goods=M('goods');
		$member=M('member');
		$member_info=$member->where(array('member_id'=>$_SESSION['member']['member_id']))->find();
		$goods_info=$goods->where(array('goods_id'=>$_GET['goods_id']))->find();
		if($member_info['integral'] >= $goods_info['jifen'])
		{
			//积分充足
			$result['erro']=0;
		}else{
			//积分不足
			$result['erro']=1;
		}
		if($goods_info['num']>=1)
		{
			$result['msg']='';
		}else{
			$result['msg']='库存不足，不能兑换';
		}
		die(json_encode($result));

	}
		//添加积分兑换订单
	public function add_jifen_order()
	{
		$goods=M('goods');
		$order=M('jifen_order');
		$member=M('member');
		$data['user_id']=$_SESSION['member']['member_id'];
		$member_info=$member->where(array('member_id'=>$data['user_id']))->find();
		$data['user_name']=$member_info['name'];
		$data['name']=$_POST['name'];
		$data['phone']=$_POST['phone'];
		foreach($_POST['address'] as $k=>$v)
		{
			if($_POST['is_ok'][$k]==1)
			{
				$data['address']=$v;
			}
		}
		
			$goods_info=$goods->where(array('goods_id'=>$_GET['goods_id']))->find();
			$data['order_no']=$this->build_order_no();
			$data['goods_id']=$goods_info['goods_id'];
			$data['goods_name']=$goods_info['goods_name'];
			$data['jifen']=$goods_info['jifen'];
			$data['add_time']=time();
			//数量默认为1
			$data['num']=1;
			$is_ok=$order->add($data);

			//添加过积分订单 对积分和积分记录进行操作
			$data_member['integral']=$member_info['integral']-$goods_info['jifen'];
			$mem_is_ok=$member->where(array('member_id'=>$member_info['member_id']))->save($data_member);
			$jifen=M('jifen_log');
			//订单操作和会员积分操作完成后插入积分记录
			if($is_ok&&$mem_is_ok)
			{
				$jifen_data['member_id']=$data['user_id'];
				$jifen_data['member_name']=$data['user_name'];
				$jifen_data['jifen']='-'.$data['jifen'];
				$jifen_data['add_time']=time();
				$jifen_data['desc']="积分订单，订单号".$data['order_no'];
				$jifen_data['stage']='积分订单扣除';
				$is_ok=$jifen->add($jifen_data);
				//库存修改
				$save['num']=$goods_info['num']-1;
				$goods->where("goods_id=$data[goods_id]")->save($save);
			}
			//

			$url='index.php?g=&m=member&a=shops_cont&status=buy&goods_id='.$goods_info['goods_id'];
			echo $url;die;
			if($is_ok)
			{
				echo '成功';
			}
		
		
	}
	
	//我的收藏
	public function shoucang()
	{
		$shoucang=M('shoucang');
		$shoucang_list=$shoucang->where()->select();
		//vv($project_list[0]);
		$this->assign("shoucang_list",$shoucang_list);
		$this->assign("type",$_GET['type']);
		$this->assign("state",$_GET['state']);
		$this->display();
	}
	public function ajax_shoucang()
	{
		$member_enrol=M('shoucang');
		
		$where['member_id']=$_SESSION['member']['member_id'];
		
		$count = M('shoucang')->where($where)->count();
		$page = $this->page($count, 10);
		$list = M('shoucang')->where($where)->order('shoucang_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		
		if($_GET['p']>$page->Total_Pages)
		{
			$data['status']=0;
			$data['msg']='没有更多数据了！';
			echo json_encode($data);die;
		}
		//vv($list);die;
		$html='';
		foreach($list as $k=>$v)
		{
			if($v['type']==1)
			{
				$v['name']='文章';
				$html.='<li><span>'.$v['name'].'</span><a href="#">'.$v['title'].'</a>';
				$html.='<img src="themes/simplebootx/Public/mobile/images/shanchu.png" onClick="del_shoucang(this,'.$v['shoucang_id'].')"></li>';
			}else
			{
				if($v['project_type']==4)
				{
					$v['name']='项目组';
					$html.='<li class="member_yaoqinglist_curr2"><span>'.$v['name'].'</span><a href="'.U('project/project_show',array('id'=>$v['con_id'])).'">'.$v['title'].'</a><img src="themes/simplebootx/Public/mobile/images/shanchu.png" onClick="del_shoucang(this,'.$v['shoucang_id'].')"></li>';
				}else
				{
					$v['name']='项目';
					$html.='<li class="member_yaoqinglist_curr1"><span>'.$v['name'].'</span><a href="'.U('project/project_show',array('id'=>$v['con_id'])).'">'.$v['title'].'</a><img src="themes/simplebootx/Public/mobile/images/shanchu.png" onClick="del_shoucang(this,'.$v['shoucang_id'].')"></li>';
				}
			}
			
		}
		
		$data['status']=1;
		$data['msg']=$html;
		echo json_encode($data);die;
	}
	
	
	//我的收货地址
	public function queren()
	{
		$shouhuo=M('shouhuo');
		$member=M('member');
		$member_info=$member->field('name,mobile,new_area_name,new_address')->where(array('member_id'=>$_SESSION['member']['member_id']))->find();
		$shouhuo_info=$shouhuo->where(array('member_id'=>$_SESSION['member']['member_id']))->find();
		$default_address=$member_info['new_area_name'].$member_info['new_address'];
		if($shouhuo_info)
		{
			$shouhuo_info['address']=json_decode($shouhuo_info['address'],true);
		}else{
		$default['address']=$default_address;
		$default['is_ok']=1;
		$shouhuo_info['address'][]=$default;
		$shouhuo_info['phone']=$member_info['mobile'];
		$shouhuo_info['name']=$member_info['name'];
		}
		$this->assign("shouhuo_info",$shouhuo_info);
		//print_r($shouhuo_info);
	
		$this->display();
	}
	public function add_shouhuo()
	{
		$shouhuo=M('shouhuo');
		$shouhuo_info=$shouhuo->where(array('member_id'=>$_SESSION['member']['member_id']))->find();
		$data['member_id']=$_SESSION['member']['member_id'];
		$data['name']=$_POST['name'];
		$data['phone']=$_POST['phone'];
		
		$data['name']=$_POST['name'];
		foreach($_POST['address'] as $k=>$v)
		{
			$address[$k]['address']=$v;
			$address[$k]['is_ok']=$_POST['is_ok'][$k];
		}
		$data['address']=json_encode($address);
		if(empty($shouhuo_info))
		{
			$shouhuo->add($data);
		}else
		{
			$shouhuo->where(array('member_id'=>$_SESSION['member']['member_id']))->save($data);
		}
		
		//vv($data);
		echo '编辑成功！';
	}
	
	
	//添加订单
	public function add_order()
	{
		$project=M('project');
		$order=M('order');
		$data['member_id']=$_SESSION['member']['member_id'];
		$data['name']=$_POST['name'];
		$data['phone']=$_POST['phone'];
		foreach($_POST['address'] as $k=>$v)
		{
			if($_POST['is_ok'][$k]==1)
			{
				$data['address']=$v;
			}
		}
		
		if($_GET['type']==3 ||$_GET['type']==1)
		{
			$project_info=$project->where(array('id'=>$_GET['project_id']))->find();
			$data['order_no']=$this->build_order_no();
			$data['project_id']=$project_info['id'];
			$data['project_name']=$project_info['title'];
			$data['add_time']=time();
			$is_ok=$order->add($data);
			
			$member_enrol=M('member_enrol');
			$me_where['member_id']=$_SESSION['member']['member_id'];
			$me_where['project_id']=$project_info['id'];
			$me_where['project_type']=$project_info['type'];
			$member_enrol->where($me_where)->save(array('state'=>'扫码签收试用品'));
			
			$url='index.php?g=&m=project&a=project_show&id='.$project_info['id'];
			echo $url;die;
			if($is_ok)
			{
				echo '成功';
			}
		}
		
	}
	
	//分享
	// public function fenxiang()
	// {
		
	// 	$this->display();
	// }
	
	//上传头像
	public function set_thumb()
	{
		$this->display();
	}
	
	//退出登陆
	public function loginout() {
		unset($_SESSION['member']);
		$this->success("退出成功！",U('login/login'));
    	//$this->display();
    }


}


