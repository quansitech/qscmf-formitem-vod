## 阿里云视频点播服务(vod)

### 安装
```php
composer require quansitech/qscmf-formitem-vod

### 如何使用

阿里云配置 [传送门](https://help.aliyun.com/document_detail/57114.html?spm=a2c4g.11186623.6.613.3cd06a58TPsKSf)
#### env配置
```dotenv
# 阿里云点播服务
VOD_ACCESS_KEY=
VOD_ACCESS_SECRET=
UPLOADER_ROLE_RAN=
PLAY_ROLE_RAN=
FULL_ROLE_RAN=
```

#### 后台上传
```php
    public function add(){
        $builder = new \Qscmf\Builder\FormBuilder();
        $builder->setMetaTitle('测试Vod上传')
            ->addFormItem('video_id','video_vod','视频')
            ->display();
    }
```
#### 前台播放
##### 控制器
```php
    /** @var $video_id string 视频ID **/    
    $vod = new FormItem\Vod\FormType\Vod();
    $this->playAuth = $vod->getPlayAuth($video_id)['PlayAuth'];
    $this->address = $vod->getPlayAddress($video_id);
```
##### 视图
```html
<link rel="stylesheet" href="__PUBLIC__/vod/aliplayer-min.css" />
<script type="text/javascript" src="__PUBLIC__/vod/aliplayer-min.js"  charset="UTF-8"></script>
<!--     支持ie8-->
<script type="text/javascript" src=" __PUBLIC__/vod/json.min.js"></script>
<script>
    var player = new Aliplayer({
            id: "J_prismPlayer",
            autoplay: false,
            isLive:false,
            playsinline:true,
            width:"100%",
            height:"400px",
            useFlashPrism:true,            
                //播放方式二：点播用户推荐
                 vid : "{$video_id}",
                 playauth: "{$playAuth}" , 
                //自定义控制栏样式 
         skinLayout:[{"name":"controlBar","align":"blabs","x":0,"y":0,"children":[
                {"name":"timeDisplay","align":"tl","x":10,"y":24},
                {"name":"playButton","align":"tl","x":15,"y":26},
                {"name":"progress","align":"tlabs","x":0,"y":0}]},
                {"name":"infoDisplay","align":"cc"}]

            
           },function(player){
                player.on('ready',function(e) {
                });
                
                player.on('error', function(){
                });
           });       
</script>
```