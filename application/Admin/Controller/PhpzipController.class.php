<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class PhpzipController extends AdminbaseController{
	
	function _initialize() {
		parent::_initialize();
	}
	


function addFileToZip($path,$zip){
    $handler=opendir($path); //打开当前文件夹由$path指定。
    while(($filename=readdir($handler))!==false){
        if($filename != "." && $filename != ".."){//文件夹文件名字为'.'和‘..’，不要对他们进行操作
            if(is_dir($path."/".$filename)){// 如果读取的某个对象是文件夹，则递归
                // print_r($filename);die;
                $this->addFileToZip($path."/".$filename, $zip);
            }else{ //将文件加入zip对象
                $zip->addFile($path."/".$filename);
            }
        }
    }
    @closedir($path);
}
	 function downimgzip()
	 {
	 	$project_id=$_GET['project_id'];
	 	$project=M('project');
	 	$project_info=$project->where("id=$project_id")->find();
	 	$filename = "./images.zip"; 
		$zip=new \ZipArchive();
		// die(SITE_PATH);
		if($zip->open('images.zip', $zip::OVERWRITE)=== TRUE){
		    $this->addFileToZip(SITE_PATH.'data/upload/member/'.$project_info['project_no'], $zip); //调用方法，对要打包的根目录进行操作，并将ZipArchive的对象传递给方法
		    $zip->close(); //关闭处理的zip文件
		}
		header("Cache-Control: public"); 
		header("Content-Description: File Transfer"); 
		header('Content-disposition: attachment; filename='.basename($filename)); //文件名   
		header("Content-Type: application/zip"); //zip格式的   
		header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件    
		header('Content-Length: '. filesize($filename)); //告诉浏览器，文件大小   
		@readfile($filename);
	 }
 	 function downmovezip()
	 {
	 	$project_id=$_GET['project_id'];
	 	$project=M('project');
	 	$project_info=$project->where("id=$project_id")->find();
	 	$filename = "data/upload/move.zip"; 
		$zip=new \ZipArchive();
		if($zip->open('data/upload/move.zip', $zip::OVERWRITE)=== TRUE){
		    $this->addFileToZip('data/upload/move/'.$project_info['project_no'], $zip); //调用方法，对要打包的根目录进行操作，并将ZipArchive的对象传递给方法
		    $zip->close(); //关闭处理的zip文件
		}

		header("Cache-Control: public"); 
		header("Content-Description: File Transfer"); 
		header('Content-disposition: attachment; filename='.basename($filename)); //文件名   
		header("Content-Type: application/zip"); //zip格式的   
		header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件    
		header('Content-Length: '. filesize($filename)); //告诉浏览器，文件大小   
		@readfile($filename);
 }





}
 

	
