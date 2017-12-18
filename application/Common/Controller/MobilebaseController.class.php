<?php
namespace Common\Controller;

use Common\Controller\AppframeController;

class MobilebaseController extends AppframeController {
	
	public function __construct() {
		$this->set_action_success_error_tpl();
		parent::__construct();
	}
	
	/**
	 * 保存用户登陆session
	 */
	public function setsession($id)
	{
		$model=M('member');
		$where['member_id']=$id;
		$member_info=$model->where($where)->find();
		$_SESSION['member']=$member_info;
		//vv($model->select());
		
	}

	  //如果没有_SESSion member_id 调用次方法 重新自动登录
    public function getOpenId($callurl='m=member&a=index')
    {
    	if($_GET['callurl'])
    	{
    		$callurl=$_GET['callurl'];
    	}
    	$appid = WX_APP_ID; 
		$secret = WX_SECRET;
		
		if(empty($_SESSION['user_openid'])||empty($_SESSION['member']['member_id']))
		{
			if (!isset($_GET["code"])) {
			//echo $_SESSION['page'];
			$u1 = "http://".WX_HOME_URL_HOST."/index.php?g=&".$callurl."&member_id=".$_GET['member_id'];
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
			$mem=M('member');
			$where['open_id']=trim($_SESSION['user_openid']);
			$mem_info=$mem->where($where)->find();
			if($mem_info)
			{
				$_SESSION['member']['member_id']=$mem_info['member_id'];
			}else{
				$_SESSION['member']['member_id']=$_SESSION['member_id'];
			}
    }
	
	function v_member_process($project_id,$process_desc)
	{
		$project=M('project');
		$member_process=M('member_process');
		
		$project_info=$project->where(array('id'=>$project_id))->find();
		$data['member_id']=$_SESSION['member']['member_id'];
		$data['project_id']=$project_info['id'];
		$data['project_name']=$project_info['title'];
		$data['project_type']=$project_info['type'];
		$data['add_time']=time();
		$data['sub_num']=1;
		$data['process_desc']=json_encode($process_desc);
		$member_process->add($data);
		
	}
	
	//生成订单编号
	function build_order_no(){
		return date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
	}
	
	//用户余额加减
	public function setjifen($jifen,$id)
	{
		$model=M('member');
		if(empty($id))
		{
			$id=$_SESSION['member']['id'];
		}
		
		$member_info=$this->member_info($id);
		$data['integral']=$member_info['integral']+$jifen;
		$where['member_id']=$id;
		return $model->where($where)->save($data);	
	}
	
	//获取用户信息
	public function member_info($id)
	{
		if(empty($id))
		{
			$id=$_SESSION['member']['member_id'];
		}
		$model=M('member');
		$where['member_id']=$id;
		$member_info=$model->where($where)->find();
		
		return $member_info;
	}
	
	//计算会员信息完整度
	public function member_wz($id)
	{
		//vv($this->member_info($id));
		$model=M('member');
		$where['member_id']=$id;
		$member_info=$model->field('name,sex,mobile,area_name,birthday,height,weight,blood,educa,marriage,email,housing,profession,work,month_income,family_income,jiguan,new_address,manxing,guomin,child_num')->where($where)->find();
		
		$wanzheng=0;
		foreach($member_info as $k=>$v)
		{
			if(!empty($v))
			{
				$wanzheng+=5;
			}
		}
		if($wanzheng>100)
		{
			$wanzheng=100;
		}
		if($wanzheng>80)
		{
			$data['type']=2;
		}else
		{
			$data['type']=1;
		}
		$data['wanzhengdu']=$wanzheng;
		$model->where($where)->save($data);
		return $wanzheng;
		//vv($member_info);
		
	}
	
	function _initialize() {
		parent::_initialize();
		defined('TMPL_PATH') or define("TMPL_PATH", C("SP_TMPL_PATH"));
		$site_options=get_site_options();
		$this->assign($site_options);
		$ucenter_syn=C("UCENTER_ENABLED");
		if($ucenter_syn){
		    $session_user=session('user');
			if(empty($session_user)){
				if(!empty($_COOKIE['thinkcmf_auth'])  && $_COOKIE['thinkcmf_auth']!="logout"){
					$thinkcmf_auth=sp_authcode($_COOKIE['thinkcmf_auth'],"DECODE");
					$thinkcmf_auth=explode("\t", $thinkcmf_auth);
					$auth_username=$thinkcmf_auth[1];
					$users_model=M('Users');
					$where['user_login']=$auth_username;
					$user=$users_model->where($where)->find();
					if(!empty($user)){
						$is_login=true;
						session('user',$user);
					}
				}
			}else{
			}
		}
		
		if(sp_is_user_login()){
			$this->assign("user",sp_get_current_user());
		}
		//载入积分规则
		$jifen=M('config');
		$jifen_config=$jifen->where("config_id=1")->find();
		if($jifen_config){
			$this->assign('jifen_config',$jifen_config);
		}

		
	}
	
	/**
	 * 检查用户登录
	 */
	protected function check_login(){
	    $session_user=session('user');
		if(empty($session_user)){
			$this->error('您还没有登录！',leuu('user/login/index',array('redirect'=>base64_encode($_SERVER['HTTP_REFERER']))));
		}
		
	}
	
	/**
	 * 检查用户状态
	 */
	protected function  check_user(){
	    $user_status=M('Users')->where(array("id"=>sp_get_current_userid()))->getField("user_status");
		if($user_status==2){
			$this->error('您还没有激活账号，请激活后再使用！',U("user/login/active"));
		}
		
		if($user_status==0){
			$this->error('此账号已经被禁止使用，请联系管理员！',__ROOT__."/");
		}
	}
	
	/**
	 * 发送注册激活邮件
	 */
	protected  function _send_to_active(){
		$option = M('Options')->where(array('option_name'=>'member_email_active'))->find();
		if(!$option){
			$this->error('网站未配置账号激活信息，请联系网站管理员');
		}
		$options = json_decode($option['option_value'], true);
		//邮件标题
		$title = $options['title'];
		$uid=session('user.id');
		$username=session('user.user_login');
	
		$activekey=md5($uid.time().uniqid());
		$users_model=M("Users");
	
		$result=$users_model->where(array("id"=>$uid))->save(array("user_activation_key"=>$activekey));
		if(!$result){
			$this->error('激活码生成失败！');
		}
		//生成激活链接
		$url = U('user/register/active',array("hash"=>$activekey), "", true);
		//邮件内容
		$template = $options['template'];
		$content = str_replace(array('http://#link#','#username#'), array($url,$username),$template);
	
		$send_result=sp_send_email(session('user.user_email'), $title, $content);
	
		if($send_result['error']){
			$this->error('激活邮件发送失败，请尝试登录后，手动发送激活邮件！');
		}
	}
	
	/**
	 * 加载模板和页面输出 可以返回输出内容
	 * @access public
	 * @param string $templateFile 模板文件名
	 * @param string $charset 模板输出字符集
	 * @param string $contentType 输出类型
	 * @param string $content 模板输出内容
	 * @return mixed
	 */
	public function display($templateFile = '', $charset = '', $contentType = '', $content = '', $prefix = '') {
		parent::display($this->parseTemplate($templateFile), $charset, $contentType,$content,$prefix);
	}
	
	/**
	 * 获取输出页面内容
	 * 调用内置的模板引擎fetch方法，
	 * @access protected
	 * @param string $templateFile 指定要调用的模板文件
	 * 默认为空 由系统自动定位模板文件
	 * @param string $content 模板输出内容
	 * @param string $prefix 模板缓存前缀*
	 * @return string
	 */
	public function fetch($templateFile='',$content='',$prefix=''){
	    $templateFile = empty($content)?$this->parseTemplate($templateFile):'';
		return parent::fetch($templateFile,$content,$prefix);
	}
	
	/**
	 * 自动定位模板文件
	 * @access protected
	 * @param string $template 模板文件规则
	 * @return string
	 */
	public function parseTemplate($template='') {
		
		$tmpl_path=C("SP_TMPL_PATH");
		define("SP_TMPL_PATH", $tmpl_path);
		if($this->theme) { // 指定模板主题
		    $theme = $this->theme;
		}else{
		    // 获取当前主题名称
		    $theme      =    C('SP_DEFAULT_THEME');
		    if(C('TMPL_DETECT_THEME')) {// 自动侦测模板主题
		        $t = C('VAR_TEMPLATE');
		        if (isset($_GET[$t])){
		            $theme = $_GET[$t];
		        }elseif(cookie('think_template')){
		            $theme = cookie('think_template');
		        }
		        if(!file_exists($tmpl_path."/".$theme)){
		            $theme  =   C('SP_DEFAULT_THEME');
		        }
		        cookie('think_template',$theme,864000);
		    }
		}
		
		$theme_suffix="";
		
		if(C('MOBILE_TPL_ENABLED') && sp_is_mobile()){//开启手机模板支持
		    
		    if (C('LANG_SWITCH_ON',null,false)){
		        if(file_exists($tmpl_path."/".$theme."_mobile_".LANG_SET)){//优先级最高
		            $theme_suffix  =  "_mobile_".LANG_SET;
		        }elseif (file_exists($tmpl_path."/".$theme."_mobile")){
		            $theme_suffix  =  "_mobile";
		        }elseif (file_exists($tmpl_path."/".$theme."_".LANG_SET)){
		            $theme_suffix  =  "_".LANG_SET;
		        }
		    }else{
    		    if(file_exists($tmpl_path."/".$theme."_mobile")){
    		        $theme_suffix  =  "_mobile";
    		    }
		    }
		}else{
		    $lang_suffix="_".LANG_SET;
		    if (C('LANG_SWITCH_ON',null,false) && file_exists($tmpl_path."/".$theme.$lang_suffix)){
		        $theme_suffix = $lang_suffix;
		    }
		}
		
		$theme=$theme.$theme_suffix;
		
		C('SP_DEFAULT_THEME',$theme);
		
		$current_tmpl_path=$tmpl_path.$theme."/";
		// 获取当前主题的模版路径
		define('THEME_PATH', $current_tmpl_path);
		
		$cdn_settings=sp_get_option('cdn_settings');
		if(!empty($cdn_settings['cdn_static_root'])){
		    $cdn_static_root=rtrim($cdn_settings['cdn_static_root'],'/');
		    C("TMPL_PARSE_STRING.__TMPL__",$cdn_static_root."/".$current_tmpl_path);
		    C("TMPL_PARSE_STRING.__PUBLIC__",$cdn_static_root."/public");
		    C("TMPL_PARSE_STRING.__WEB_ROOT__",$cdn_static_root);
		}else{
		    C("TMPL_PARSE_STRING.__TMPL__",__ROOT__."/".$current_tmpl_path);
		}
		
		
		C('SP_VIEW_PATH',$tmpl_path);
		C('DEFAULT_THEME',$theme);
		
		define("SP_CURRENT_THEME", $theme);
		
		if(is_file($template)) {
			return $template;
		}
		$depr       =   C('TMPL_FILE_DEPR');
		$template   =   str_replace(':', $depr, $template);
		
		// 获取当前模块
		$module   =  MODULE_NAME;
		if(strpos($template,'@')){ // 跨模块调用模版文件
			list($module,$template)  =   explode('@',$template);
		}
		
		$module =$module."/";
		
		// 分析模板文件规则
		if('' == $template) {
			// 如果模板文件名为空 按照默认规则定位
			$template = CONTROLLER_NAME . $depr . ACTION_NAME;
		}elseif(false === strpos($template, '/')){
			$template = CONTROLLER_NAME . $depr . $template;
		}
		
		$file = sp_add_template_file_suffix($current_tmpl_path.$module.$template);
		$file= str_replace("//",'/',$file);
		if(!file_exists_case($file)) E(L('_TEMPLATE_NOT_EXIST_').':'.$file);
		return $file;
	}
	
	/**
	 * 设置错误，成功跳转界面
	 */
	private function set_action_success_error_tpl(){
		$theme      =    C('SP_DEFAULT_THEME');
		if(C('TMPL_DETECT_THEME')) {// 自动侦测模板主题
			if(cookie('think_template')){
				$theme = cookie('think_template');
			}
		}
		//by ayumi手机提示模板
		$tpl_path = '';
		if(C('MOBILE_TPL_ENABLED') && sp_is_mobile() && file_exists(C("SP_TMPL_PATH")."/".$theme."_mobile")){//开启手机模板支持
			$theme  =   $theme."_mobile";
			$tpl_path=C("SP_TMPL_PATH").$theme."/";
		}else{
			$tpl_path=C("SP_TMPL_PATH").$theme."/";
		}
		
		//by ayumi手机提示模板
		$defaultjump=THINK_PATH.'Tpl/dispatch_jump.tpl';
		$action_success = sp_add_template_file_suffix($tpl_path.C("SP_TMPL_ACTION_SUCCESS"));
		$action_error = sp_add_template_file_suffix($tpl_path.C("SP_TMPL_ACTION_ERROR"));
		if(file_exists_case($action_success)){
			C("TMPL_ACTION_SUCCESS",$action_success);
		}else{
			C("TMPL_ACTION_SUCCESS",$defaultjump);
		}

		if(file_exists_case($action_error)){
			C("TMPL_ACTION_ERROR",$action_error);
		}else{
			C("TMPL_ACTION_ERROR",$defaultjump);
		}
	}
	
	
}