<?php


namespace FormItem\Vod\Lib;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use AlibabaCloud\Sts\Sts;
use AlibabaCloud\Vod\Vod;




class VodSdk
{
    private static $_sdk;


    static function getInstance()
    {
        if (!self::$_sdk) {
            self::$_sdk = new self();
        }
        return self::$_sdk;
    }

    private function _getStsAuth($roleArn)
    {
        $sKey = 'VOD_ROLE_AUTH_' . md5($roleArn);
        if (S($sKey)) {
            return S($sKey);
        }

        //构建阿里云client时需要设置AccessKey ID和AccessKey Secret
        AlibabaCloud::accessKeyClient(env('VOD_ACCESS_KEY'), env('VOD_ACCESS_SECRET'))
            ->regionId('cn-shanghai')
            ->asDefaultClient();
        $request = Sts::v20150401()
            ->assumeRole()
            //指定角色ARN
            ->withRoleArn($roleArn)
            //RoleSessionName即临时身份的会话名称，用于区分不同的临时身份
            ->withRoleSessionName('vod')
            //设置权限策略以进一步限制角色的权限
            //以下权限策略表示拥有所有OSS的只读权限
            ->withPolicy('{
              "Statement": [
                {
                  "Action": [
                    "*"
                  ],
                  "Effect": "Allow",
                  "Resource": "*"
                }
              ],
              "Version": "1"
            }')
            ->connectTimeout(60)
            ->timeout(65)
            ->request();
        if ($request->getStatusCode() != 200) {
            E('roleArn请求失败::' . $request->getBody()->getContents());
        }
        $res = $request->get('Credentials');
        S($sKey, $res, strtotime($res['Expiration']) - time());
        return $res;
    }

    private function __construct()
    {
//        dd($this->_getStsAuth(env('PLAY_ROLE_RAN')));
//        $this->sts=$this->_initVodClient()
    }

    private function _initVodClient($accessKeyId, $accessKeySecret, $securityToken)
    {
        $regionId = 'cn-shanghai';
        return AlibabaCloud::stsClient($accessKeyId, $accessKeySecret, $securityToken)
            ->regionId($regionId)
            ->connectTimeout(1)
            ->timeout(3);
    }

    public function getPlayAuth($videoId)
    {
        $stsAuth = $this->_getStsAuth(env('PLAY_ROLE_RAN'));
        $client = $this->_initVodClient($stsAuth['AccessKeyId'], $stsAuth['AccessKeySecret'], $stsAuth['SecurityToken']);
        $client->name('play');

        $request = Vod::v20170321()->getVideoPlayAuth()
            ->client('play')
            ->withVideoId($videoId)
            ->withAuthInfoTimeout(3000)
            ->request();
        if ($request->getStatusCode() != 200) {
            E('获取播放凭证失败' . $request->getBody()->getContents());
        }
        return $request->get();
    }

    public function getPlayAddress($video_id, $definition = '')
    {
        $stsAuth = $this->_getStsAuth(env('PLAY_ROLE_RAN'));
        $client = $this->_initVodClient($stsAuth['AccessKeyId'], $stsAuth['AccessKeySecret'], $stsAuth['SecurityToken']);
        $client->name('play_address');

        // Definition 视频流清晰度，多个用逗号分隔，取值FD(流畅)，LD(标清)，SD(高清)，HD(超清)，OD(原画)，2K(2K)，4K(4K)
        $request = Vod::v20170321()->getPlayInfo()
            ->client('play_address')
            ->withVideoId($video_id)
            ->request();
        if ($request->getStatusCode() != 200) {
            E('获取播放地址失败' . $request->getBody()->getContents());
        }
        foreach ($request->get('PlayInfoList')['PlayInfo'] as $v) {
            if (!$definition) {
                return $v['PlayURL'];
            }
            if ($v['Definition'] == $definition) {
                return $v['PlayURL'];
            }
        }
        return '';
    }

    public function deleteVideo($video_ids)
    {
        $stsAuth = $this->_getStsAuth(env('FULL_ROLE_RAN'));
        $client = $this->_initVodClient($stsAuth['AccessKeyId'], $stsAuth['AccessKeySecret'], $stsAuth['SecurityToken']);
        $client->name('delete_video');

        $request = Vod::v20170321()->deleteVideo()
            ->client('delete_video')
            ->withVideoIds($video_ids)
            ->request();
        if ($request->getStatusCode() != 200) {
            E('删除视频失败' . $request->getBody()->getContents());
        }
        return true;
    }

    public function getUploadAuth($title = '视频标题', $filename = '文件名称.mov')
    {
        $stsAuth = $this->_getStsAuth(env('UPLOADER_ROLE_RAN'));
        $client = $this->_initVodClient($stsAuth['AccessKeyId'], $stsAuth['AccessKeySecret'], $stsAuth['SecurityToken']);
        $client->name('upload');

        $request = Vod::v20170321()->createUploadVideo()
            ->client('upload')
            ->withTitle($title)
            ->withFileName($filename)
            ->request();
        if ($request->getStatusCode() != 200) {
            E('获取上传凭证失败' . $request->getBody()->getContents());
        }
        return $request->get();
    }

    public function refreshUploadAuth($video_id)
    {
        $stsAuth = $this->_getStsAuth(env('FULL_ROLE_RAN'));
        $client = $this->_initVodClient($stsAuth['AccessKeyId'], $stsAuth['AccessKeySecret'], $stsAuth['SecurityToken']);
        $client->name('refresh');

        $request=Vod::v20170321()->refreshUploadVideo()
            ->withVideoId($video_id)
            ->client('refresh')
            ->request();

        if ($request->getStatusCode() != 200) {
            E('刷新上传凭证失败' . $request->getBody()->getContents());
        }
        return $request->get();
    }

    public function getTranscodeSummary($video_ids){
        $stsAuth = $this->_getStsAuth(env('FULL_ROLE_RAN'));
        $client = $this->_initVodClient($stsAuth['AccessKeyId'], $stsAuth['AccessKeySecret'], $stsAuth['SecurityToken']);
        $client->name('transcode');

        $request = Vod::v20170321()->getTranscodeSummary()
            ->client('transcode')
            ->withVideoIds($video_ids)
            ->request();
        if ($request->getStatusCode() != 200) {
            E('获取转码任务失败' . $request->getBody()->getContents());
        }
        return $request->get();
    }
}