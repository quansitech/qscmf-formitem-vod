<?php
namespace FormItem\Vod;

use Bootstrap\Provider;
use Bootstrap\RegisterContainer;
use FormItem\Vod\Controller\VodController;
use FormItem\Vod\FormType\Vod;

class VodProvider implements Provider{

    public function register(){
        RegisterContainer::registerFormItem('video_vod', Vod::class);

        RegisterContainer::registerSymLink(WWW_DIR . '/Public/vod', __DIR__ . '/../asset/vod');

        RegisterContainer::registerController('extends', 'vod', VodController::class);

    }
}