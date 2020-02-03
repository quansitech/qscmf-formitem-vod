<?php
namespace FormItem\Vod\FormType;

use Illuminate\Support\Str;
use Qscmf\Builder\FormType\FormType;

class Vod implements FormType{

    public function build($form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $view->assign('gid', Str::uuid());
        $content = $view->fetch(__DIR__ . '/video_vod.html');
        return $content;
    }
}