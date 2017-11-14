<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class MainController extends AdminbaseController {
	
    public function index(){
    	
    // 	$mysql= M()->query("select VERSION() as version");
    // 	$mysql=$mysql[0]['version'];
    // 	$mysql=empty($mysql)?L('UNKNOWN'):$mysql;
    	
    // 	//server infomaions
    // 	$info = array(
    // 			L('OPERATING_SYSTEM') => PHP_OS,
    // 			L('OPERATING_ENVIRONMENT') => $_SERVER["SERVER_SOFTWARE"],
    // 	        L('PHP_VERSION') => PHP_VERSION,
    // 			L('PHP_RUN_MODE') => php_sapi_name(),
				// L('PHP_VERSION') => phpversion(),
    // 			L('MYSQL_VERSION') =>$mysql,
    // 			L('PROGRAM_VERSION') => THINKCMF_VERSION . "&nbsp;&nbsp;&nbsp; [<a href='http://www.thinkcmf.com' target='_blank'>ThinkCMF</a>]",
    // 			L('UPLOAD_MAX_FILESIZE') => ini_get('upload_max_filesize'),
    // 			L('MAX_EXECUTION_TIME') => ini_get('max_execution_time') . "s",
    // 			L('DISK_FREE_SPACE') => round((@disk_free_space(".") / (1024 * 1024)), 2) . 'M',
    // 	);
    // 	$this->assign('server_info', $info);
    //     
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
            // print_r($project_test);
            // print_r($project_diaocha);
            // print_r($project_shiyong);
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
                    //组内项目数量
                    $longterm_list[$k]['in_project_num']=$project->where("longterm=$v[id]")->count();
            }

            $this->assign('project_info',$project_info);
            $this->assign('config_info',$config_info);
            $this->assign('member_info',$member_info);
            $this->assign('longterm_list',$longterm_list);
       // print_r($project_info);
        $this->display();
    	
    }
}