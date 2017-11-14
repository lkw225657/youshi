<?php
// +----------------------------------------------------------------------
// | 为民生活 [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.为民生活.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace Mobile\Controller;
use Common\Controller\MobilebaseController; 
/**
 * 个人中心
 */
class UploadController extends MobilebaseController {
	
	
    public function index(){

        //echo date('Y-m-d h:i:s',);

        $this->display('index');


    }
	public function index2(){

        file_put_contents('WxError.txt','Wxerro-code');

        $this->display('index2');


    }
	
	public function upload(){
		
		$upload = new \Think\Upload();// 实例化上传类
		$upload->maxSize   =     3145728 ;// 设置附件上传大小
		$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		$upload->rootPath  =     './'.C("UPLOADPATH"); // 设置附件上传根目录
		$upload->autoSub=false;
		$upload->savePath  =     '/store/'.$_SESSION['member']['id']."/"; // 设置附件上传（子）目录
		// 上传文件 

		$info   =   $upload->upload();
		$src='./'.C("UPLOADPATH").$info['images']['savepath']."".$info['images']['savename'];
		if(!$info) {// 上传错误提示错误信息
			$msg="上传失败";
		}else{// 上传成功
		    $msg="上传成功";
		}
		echo json_encode(array('result' => $info, 'msg' => $msg, 'src' => $src, 'width' => 100, 'height' => 100, 'path' => $src));die;
		/*echo json_encode(array('result' => $info, 'msg' => $msg, 'src' => $src, 'width' => 100, 'height' => 100, 'path' => $src));
		
		if(!$info) {// 上传错误提示错误信息
			$this->error($upload->getError());
		}else{// 上传成功
		    print_r($info);
			echo $info['Filedata']['savepath']."".$info['Filedata']['savename'];
			//$this->success('上传成功！');
		}*/
	}
	
	//上传头像
	public function touxiang(){
		
		$upload = new \Think\Upload();// 实例化上传类
		$upload->maxSize   =     3145728 ;// 设置附件上传大小
		$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		$upload->rootPath  =     './'.C("UPLOADPATH"); // 设置附件上传根目录
		$upload->autoSub=false;
		$upload->savePath  =     '/head/'.$_SESSION['member']['id']."/"; // 设置附件上传（子）目录
		// 上传文件 
		$info   =   $upload->upload();
		$src='./'.C("UPLOADPATH").$info['image']['savepath']."".$info['image']['savename'];
		if(!$info) {// 上传错误提示错误信息
			$msg="上传失败";
		}else{// 上传成功
		    $msg="上传成功";
		}
		echo json_encode(array('result' => $info, 'msg' => $msg, 'src' => $src, 'width' => 100, 'height' => 100, 'path' => $src));die;
		echo json_encode(array('result' => $info, 'msg' => $msg, 'src' => $src, 'width' => 100, 'height' => 100, 'path' => $src));
	}
	public function caiqie()
	{
		$image = new \Think\Image(); 
		$image->open("./".$_GET['src']);
		//echo $width = $image->width(); // 返回图片的宽度
		//将图片裁剪为400x400并保存为corp.jpg
		$src='./'.C("UPLOADPATH")."/head/".$_SESSION['member']['id']."/".$_SESSION['member']['id']."_".time().".jpg";
		$success=$image->crop($_GET['w'], $_GET['h'],$_GET['x'],$_GET['y'])->save($src);
		
		$msg = $success ? '上传成功！' : '上传失败！';
        echo json_encode(array('result' => $success, 'msg' => $msg,'src'=>$src,'path'=>$src));
		$User = M('member');
		$User->execute('update v_member set thumb="'.$src.'" where id = ' . $_SESSION['member']['id']);
		
	}
	
	
	//把base64图片保存
	public function tobase()
	{
		$new_file="data/upload/member/";//.$_SESSION['member']['id']."/".$img_name;
		
		$base64_image_content=$_REQUEST['base'];
		//保存base64字符串为图片
		//匹配出图片的格式
		if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
		  $type = $result[2];
		  $new_file = $new_file.time().mt_rand(1,100).".{$type}";
		  if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))){
			$data['status']=1;
			$data['msg']='保存成功';
			$data['path']=$new_file;
			M('member')->where(array('member_id'=>$_SESSION['member']['member_id']))->save(array('head_pic'=>$new_file));
			//vv($data);
			echo json_encode($data);return;
			
			//echo '新文件保存成功：', $new_file;
		  }
		 
		}else{
			$data['status']=0;
			$data['msg']='保存失败';
			$data['src']=$new_file;
			echo json_encode($data);return;
		}
		//echo '111';
	}
	
	
	//把base64图片保存
	public function tobasewx()
	{
		$new_file="data/upload/";//.$_SESSION['member']['id']."/".$img_name;
		
		$base64_image_content=$_REQUEST['base'];
		//保存base64字符串为图片
		//匹配出图片的格式
		if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
		  $type = $result[2];
		  $new_file = $new_file.time().mt_rand(1,100).".{$type}";
		  if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))){
			$data['status']=1;
			$data['msg']='保存成功';
			$data['path']=$new_file;
			//vv($data);
			echo json_encode($data);return;
			
			//echo '新文件保存成功：', $new_file;
		  }
		
		}else{
			$data['status']=0;
			$data['msg']='保存失败';
			$data['src']=$new_file;
			echo json_encode($data);return;
		}
		//echo '111';
	}
	
		//上次视频
	public function move()
	{
		$project_id=$_GET['project_id'];
		$member_id=$_SESSION['member']['member_id'];
		$member=M('member');
		$project=M('project');
		$project_info=$project->field('project_no')->where("id=$project_id")->find();
		$project_no=$project_info['project_no'];
		$where['member_id']=$_SESSION['member']['member_id'];
		$member_info=$member->field('member_id')->where($where)->find();
		//注意运行环境中上传文件最大限制
		$upload = new \Think\Upload();// 实例化上传类
		$upload->maxSize = 209715200;// 设置附件上传大小
		//'swf', 'wmv', 'asf', 'wma', 'mp3', 'asx', 'mid', 'midi', 'rm', 'ra', 'rmvb', 'mp4', 'mov', 'avi', 'wav', 'ram', 'mpg', 'mpeg', 'flv';
		$upload->exts = array('swf', 'wmv', 'asf', 'wma', 'mp3', 'asx', 'mid', 'midi', 'rm', 'ra', 'rmvb', 'mp4', 'mov', 'avi', 'wav', 'ram', 'mpg', 'mpeg', 'flv');//设置附件上传类型
		$upload->rootPath  =     './'.C("UPLOADPATH"); // 设置附件上传根目录
		$upload->autoSub = false; //拒绝创建子目录
		$upload->savePath  = '/move/'.$project_no.'/'.$member_info['member_id'].'/';
		$upload->saveName  =$project_no." ".$member_info['member_id']." ".date('Y-m-d',time())." ".rand(0,100);//命名规则
		// 上传文件 
		$info = $upload->upload();
		foreach($info as $k=>$v)
		{
			$info[$k]['file']=C("UPLOADPATH").'move/'.$project_no.'/'.$member_info['member_id'].'/';
			$info[$k]['url']=C("UPLOADPATH").'move/'.$project_no.'/'.$member_info['member_id'].'/'.$v['savename'];
		}
		
		//vv($info); 
		echo json_encode($info);
	}

	//上传图片
	public function upload_photos()
	{
		$project_id=$_GET['project_id'];
		$member_id=$_SESSION['member']['member_id'];
		$member=M('member');
		$project=M('project');
		$project_info=$project->field('project_no')->where("id=$project_id")->find();
		$project_no=$project_info['project_no'];
		$where['member_id']=$_SESSION['member']['member_id'];
		$member_info=$member->field('member_id')->where($where)->find();
		//注意运行环境中上传文件最大限制
		$upload = new \Think\Upload();// 实例化上传类
		$upload->maxSize = 209715200;// 设置附件上传大小
		//'swf', 'wmv', 'asf', 'wma', 'mp3', 'asx', 'mid', 'midi', 'rm', 'ra', 'rmvb', 'mp4', 'mov', 'avi', 'wav', 'ram', 'mpg', 'mpeg', 'flv';
		$upload->exts = array('jpeg', 'jpg', 'png');//设置附件上传类型
		$upload->rootPath  =     './'.C("UPLOADPATH"); // 设置附件上传根目录
		$upload->autoSub = false; //拒绝创建子目录
		$upload->savePath  = '/member/'.$project_no.'/'.$member_info['member_id'].'/';
		$upload->saveName  =$project_no." ".$member_info['member_id']." ".date('Y-m-d',time())." ".rand(0,100);//命名规则
		// 上传文件 
		$info = $upload->upload();
		foreach($info as $k=>$v)
		{
			$info[$k]['file']=C("UPLOADPATH").'member/'.$project_no.'/'.$member_info['member_id'].'/';
			$info[$k]['url']=C("UPLOADPATH").'member/'.$project_no.'/'.$member_info['member_id'].'/'.$v['savename'];
		}
		
		//vv($info); 
		echo json_encode($info);
	}
	
	

    public function test(){
		$upload = new \Think\Upload();// 实例化上传类
		$upload->maxSize   =     3145728 ;// 设置附件上传大小
		$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		$upload->rootPath  =     './uploads/'; // 设置附件上传根目录
		$upload->savePath  =     ''; // 设置附件上传（子）目录
		// 上传文件 
		$info   =   $upload->upload();
		if(!$info) {// 上传错误提示错误信息
			$this->error($upload->getError());
		}else{// 上传成功
		    //print_r($info);
			//echo $info['Filedata']['savepath']."".$info['Filedata']['savename'];
			$this->success('上传成功！');
		}
	}
}