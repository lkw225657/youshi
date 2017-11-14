<?php
/**
 * 后台首页
 */
namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class IndexController extends AdminbaseController {
	
	public function _initialize() {
	    empty($_GET['upw'])?"":session("__SP_UPW__",$_GET['upw']);//设置后台登录加密码	    
		parent::_initialize();
		$this->initMenu();
	}
	
    /**
     * 后台框架首页
     */
    public function index() {
        $this->load_menu_lang();
    	
        $this->assign("menus", D("Common/Menu")->menu_json());
        $project=M('project');
        $config=M('config');
        $member=M('member');
        //微信关注人数
        $config_info=$config->find();  

        //会员数
        $member_info['count']=$member->where()->count();
        $member_info['man_count']=$member->where("sex='男'")->count();
        $member_info['woman_count']=$member->where("sex='女'")->count();
        $time=time();
        $member_list=$member->field('birthday')->select();
        //18-30  30-50 50以上
        $i=0;
        $y=0;
        $k=0;
        foreach ($member_list as $key => $value) {
            $age=($time-$value['birthday'])/(60*60*24)/365;
            $age=trim($age,'-');
            if($age>18&&$age<30)
            {
                //18-30
                $i+=1;
            }
            if($age>30&&$age<50)
            {
                //30-50
                $y+=1;
            }
            if($age>50)
            {
                //50-
                $k+=1;
            }
           
        }
        $member_info['age_1']=$i;
        $member_info['age_2']=$y;
        $member_info['age_3']=$k;
        //地区
        $mem_add_list=$member->field('area_name')->select();
        $sh=0;
        $bj=0;
        $hn=0;
        foreach ($mem_add_list as $k => $v) {
            
            $adr=trim($v['area_name']);
            $adr=mb_substr($adr,0,3,'utf-8');
            if($adr=='上海市')
            {
                //18-30
                $sh+=1;
            }
           if($adr=='河南省')
            {
                //18-30
                $hn+=1;
            }
           if($adr=='北京市')
            {
                //18-30
                $bj+=1;
            }
           
        }
        //北京
        $member_info['bj']=$bj;
        //河南
        $member_info['hn']=$hn;
        //上海
        $member_info['sh']=$sh;
        //项目数据
        $project_info['test_num']=$project->where("type=1")->count();
        $project_info['diaocha_num']=$project->where("type=2")->count();
        $project_info['shiyong_num']=$project->where("type=3")->count();
        $project_info['longterm_num']=$project->where("type=4")->count();
        //参与项目男女数量
        $member_enrol=M('member_enrol');
        //ceshi
        $member_info['test_man']=$member_enrol->join("v_member ON v_member_enrol.member_id=v_member.member_id")->where("v_member_enrol.project_type=1 AND v_member.sex='男'")->count();
        $member_info['test_women']=$member_enrol->join("v_member ON v_member_enrol.member_id=v_member.member_id")->where("v_member_enrol.project_type=1 AND v_member.sex='女'")->count();
        //diaocha
        $member_info['diaocha_man']=$member_enrol->join("v_member ON v_member_enrol.member_id=v_member.member_id")->where("v_member_enrol.project_type=2 AND v_member.sex='男'")->count();
        $member_info['diaocha_women']=$member_enrol->join("v_member ON v_member_enrol.member_id=v_member.member_id")->where("v_member_enrol.project_type=2 AND v_member.sex='女'")->count();
        //shiyong
        $member_info['shiyong_man']=$member_enrol->join("v_member ON v_member_enrol.member_id=v_member.member_id")->where("v_member_enrol.project_type=3 AND v_member.sex='男'")->count();
        $member_info['shiyong_women']=$member_enrol->join("v_member ON v_member_enrol.member_id=v_member.member_id")->where("v_member_enrol.project_type=3 AND v_member.sex='女'")->count();

        $member_info['longterm_man']=$member_enrol->join("v_member ON v_member_enrol.member_id=v_member.member_id")->where("v_member_enrol.project_type=4 AND v_member.sex='男'")->count();
        $member_info['longterm_women']=$member_enrol->join("v_member ON v_member_enrol.member_id=v_member.member_id")->where("v_member_enrol.project_type=4 AND v_member.sex='女'")->count();

        $member_info['test_count']=$member_info['test_man']+$member_info['test_women'];
        $member_info['diaocha_count']=$member_info['diaocha_man']+$member_info['diaocha_women'];
        $member_info['shiyong_count']=$member_info['shiyong_man']+$member_info['shiyong_women'];
        //$member_info['test_count']=$member_info['test_man']+$member_info['test_women'];
        //长期项目组的数据
            $longterm_list=$project->where("type=4")->select();
            foreach($longterm_list as $k=>$v)
            {
                $longterm_list[$k]['add_time']=date('Y-m-d',$longterm_list[$k]['add_time']);
                   if(!$v['project_desc'])
                   {
                    $longterm_list[$k]['project_desc']='暂无';
                   }
                //组内发布数量
             
                    $longterm_list[$k]['in_num']=$project->where("longterm=$v[id] and is_zw=0")->count();
                    if($longterm_list[$k]['in_num'])
                    {
                        //
                    }else{
                        $longterm_list[$k]['in_num']=0;
                    }

                    //组内用户数量
                    $longterm_list[$k]['in_baoming_num']=$member_enrol->join(" v_project ON v_project.longterm=v_member_enrol.project_id")->where("longterm=$v[id]")->count();
                
            }

            $this->assign('project_info',$project_info);
            $this->assign('config_info',$config_info);
            $this->assign('member_info',$member_info);
            $this->assign('longterm_list',$longterm_list);
       // print_r($project_info);
        $this->display();
       	$this->display();
    }
    public function index_drive()
    {
        $project=M('project');
        $config=M('config');
        $member=M('member');
        //微信关注人数
        $config_info=$config->find();  

        //会员数
        $member_info['count']=$member->where()->count();
        $member_info['man_count']=$member->where("sex='男'")->count();
        $member_info['woman_count']=$member->where("sex='女'")->count();
        $time=time();
        $member_list=$member->field('birthday')->select();
        //18-30  30-50 50以上
        $age1=0;$age4=0;
        $age2=0;$age5=0;
        $age3=0;$age6=0;
        foreach ($member_list as $key => $value) {
            $age=($time-$value['birthday'])/(60*60*24)/365;
            $age=trim($age,'-');
            if($age<18)
            {
                $age1+=1;
            }
            if($age>=18&&$age<30)
            {
                //18-30
                $age2+=1;
            }
            if($age>=30&&$age<40)
            {
                //30-50
                $age3+=1;
            }
            if($age>=40&&$age<50)
            {
                //30-50
                $age4+=1;
            }
            if($age>=50&&$age<60)
            {
                //30-50
                $age5+=1;
            }
            if($age>=60)
            {
                //50-
                $age6+=1;
            }
           
        }
        $member_info['age_1']=$age1;
        $member_info['age_2']=$age2;
        $member_info['age_3']=$age3;
        $member_info['age_4']=$age4;
        $member_info['age_5']=$age5;
        $member_info['age_6']=$age6;
        //地区
        $mem_add_list=$member->field('area_name')->select();
        $sh=0;
        $bj=0;
        $hn=0;
        foreach ($mem_add_list as $k => $v) {
            
            $adr=trim($v['area_name']);
            $adr=mb_substr($adr,0,3,'utf-8');
            if($adr=='上海市')
            {
                //18-30
                $sh+=1;
            }
           if($adr=='河南省')
            {
                //18-30
                $hn+=1;
            }
           if($adr=='北京市')
            {
                //18-30
                $bj+=1;
            }
           
        }
        //北京
        $member_info['bj']=$bj;
        //河南
        $member_info['hn']=$hn;
        //上海
        $member_info['sh']=$sh;
        //项目数据
        $project_info['test_num']=$project->where("type=1")->count();
        $project_info['diaocha_num']=$project->where("type=2")->count();
        $project_info['shiyong_num']=$project->where("type=3")->count();
        $project_info['longterm_num']=$project->where("type=4")->count();
        //参与项目男女数量
        $member_enrol=M('member_enrol');
        //ceshi
         $project_test=$project->field(array("count(class)"=>"num","class"))->where("type=1")->group('class')->select();
        $project_diaocha=$project->field(array("count(class)"=>"num","class"))->where("type=2")->group('class')->select();
        $project_shiyong=$project->field(array("count(class)"=>"num","class"))->where("type=3")->group('class')->select();
         $this->assign('project_test',$project_test);
            $this->assign('project_diaocha',$project_diaocha);
            $this->assign('project_shiyong',$project_shiyong);
        //$member_info['test_count']=$member_info['test_man']+$member_info['test_women'];
        //长期项目组的数据
            $longterm_list=$project->where("type=4")->select();
            foreach($longterm_list as $k=>$v)
            {
                $longterm_list[$k]['add_time']=date('Y-m-d',$longterm_list[$k]['add_time']);
                   if(!$v['project_desc'])
                   {
                    $longterm_list[$k]['project_desc']='暂无';
                   }
                //组内发布数量
             
                    $longterm_list[$k]['in_num']=$project->where("longterm=$v[id] and is_zw=0")->count();
                    if($longterm_list[$k]['in_num'])
                    {
                        //
                    }else{
                        $longterm_list[$k]['in_num']=0;
                    }

                    //组内用户数量
                    $longterm_list[$k]['in_baoming_num']=$member_enrol->join(" v_project ON v_project.longterm=v_member_enrol.project_id")->where("longterm=$v[id]")->count();
                
            }

            $this->assign('project_info',$project_info);
            $this->assign('config_info',$config_info);
            $this->assign('member_info',$member_info);
            $this->assign('longterm_list',$longterm_list);
       // print_r($project_info);
    	$this->display();
    }
     public function set_jifen()
    {
    		$drive=M('config');
    	$config=$drive->where()->find();
    	$this->assign('config',$config);
    	$this->display();
    }
     public function set_drive()
    {
    	$drive=M('config');
    	$config=$drive->where()->find();
    	$this->assign('config',$config);
    	$this->display();
    }
    //设计微信关注人数
    public function save_drive()
    {
    	$guanzhu=(int)$_POST['guanzhu'];
    	$new_add_num=(int)$_POST['new_add_num'];
    	$save['guanzhu']=$guanzhu;
    	$save['new_add_num']=$new_add_num;
    	// print_r($save);
    	$config=M('config');
    	$config->where(array('config_id'=>'1'))->save($save);
    	$this->success("操作成功！",'index.php?g=Admin&m=index&a=set_drive');
    }
    //设置积分
     public function save_jifen()
    {
    	$register_jifen=(int)$_POST['register_jifen'];
    	$fenxiang_jifen=(int)$_POST['fenxiang_jifen'];
    	$baoming_jifen=(int)$_POST['baoming_jifen'];
    	$wanshan_jifen=(int)$_POST['wanshan_jifen'];
    	$save['register_jifen']=$register_jifen;
    	$save['fenxiang_jifen']=$fenxiang_jifen;
    	$save['baoming_jifen']=$baoming_jifen;
    	$save['wanshan_jifen']=$wanshan_jifen;
    	// print_r($save);
    	$config=M('config');
    	$config->where(array('config_id'=>'1'))->save($save);
    	$this->success("操作成功！",'index.php?g=Admin&m=index&a=set_jifen');
    }
    
    private function load_menu_lang(){
        $default_lang=C('DEFAULT_LANG');
        
        $langSet=C('ADMIN_LANG_SWITCH_ON',null,false)?LANG_SET:$default_lang;
        
	    $apps=sp_scan_dir(SPAPP."*",GLOB_ONLYDIR);
	    $error_menus=array();
	    foreach ($apps as $app){
	        if(is_dir(SPAPP.$app)){
	            if($default_lang!=$langSet){
	                $admin_menu_lang_file=SPAPP.$app."/Lang/".$langSet."/admin_menu.php";
	            }else{
	                $admin_menu_lang_file=SITE_PATH."data/lang/$app/Lang/".$langSet."/admin_menu.php";
	                if(!file_exists_case($admin_menu_lang_file)){
	                    $admin_menu_lang_file=SPAPP.$app."/Lang/".$langSet."/admin_menu.php";
	                }
	            }
	            
	            if(is_file($admin_menu_lang_file)){
	                $lang=include $admin_menu_lang_file;
	                L($lang);
	            }
	        }
	    }
    }

}

