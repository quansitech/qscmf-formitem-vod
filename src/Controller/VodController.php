<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace FormItem\Vod\Controller;


use FormItem\Vod\Lib\VodSdk;
use Think\Controller;

/**
 * Description of VodController
 *
 * @author XY
 */
class VodController  extends Controller {
    //put your code here
    
    public function callback(){
        $body = file_get_contents('php://input');
        $res = json_decode($body, true);
        \Think\Log::write('点播回调开始 begin');
        
        if($res['status'] == 'fail'){
             \Think\Log::write('点播回调失败 fail');
        }else{
             \Think\Log::write('点播回调成功 success');
            $this->_handle($res);
        }
    }
    
    private function _handle($body){
        switch($body['EventType']){
            case 'FileUploadComplete':
            \Think\Log::write('视频上传成功ID:' . $body['VideoId']);    
            break;    
        
            case 'TranscodeComplete':
            \Think\Log::write('视频转码成功ID:' . $body['VideoId']);    
            S('transcode_' .$body['VideoId'], '1',100);  
            break;        
        }
    }

    public function getUploadAuth(){

        $token = I('post.token');

        if($token != '1234'){
            return false;
        }

        $vod = VodSdk::getInstance();

        $res = $vod->getUploadAuth();

        $this->ajaxReturn($res);
    }

    public function getPlayAuth(){

        $token = I('post.token');
        $video_id = I('post.video_id');
        if($token != '1234'){
            return false;
        }

        $vod = VodSdk::getInstance();

        $res = $vod->getPlayAuth($video_id);

        $this->ajaxReturn($res);
    }

    public function checkTranscode($video_id){

        $res=VodSdk::getInstance()->getTranscodeSummary($video_id);
        if ($res['TranscodeSummaryList'][0]['TranscodeStatus']==='CompleteAllSucc'){
            $this->ajaxReturn(array('status' =>1));
        }else{
            $this->ajaxReturn(array('status' => 2));
        }
    }

    public function refreshPlayAuth(){

        $token = I('post.token');
        $video_id = I('post.video_id');
        if($token != '1234'){
            return false;
        }

        $vod = VodSdk::getInstance();

        $res = $vod->refreshUploadAuth($video_id);

        $this->ajaxReturn($res);

    }

    public function deleteVideo(){

        $token = I('post.token');
        $video_id = I('post.video_id');
        if($token != '1234'){
            return false;
        }

        $vod = VodSdk::getInstance();

        $res = $vod->deleteVideo($video_id);

        $this->ajaxReturn($res);

    }

}
