<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
      'css/site.css',
    ];
    public $js = [
      'js/push.min.js'
    ];
    public $depends = [
      'yii\web\YiiAsset',
      'yii\bootstrap\BootstrapAsset',
      'macgyer\yii2materializecss\assets\MaterializeAsset',
      'macgyer\yii2materializecss\assets\MaterializePluginAsset',
      'macgyer\yii2materializecss\assets\MaterializeFontAsset',
      'diecoding\toastr\ToastrAsset',
      'yii2mod\alert\AlertAsset',
    ];
}
