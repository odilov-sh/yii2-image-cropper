<?php

namespace odilov\cropper\assets;

use yii\web\AssetBundle;

/**
 * Widget asset bundle
 */
class CropperAsset extends AssetBundle
{
    public $sourcePath = '@odilov/cropper/web/';

    public $css = [
        'css/cropper.css'
    ];

    public $js = [
        'js/cropper.js'
    ];

    public $depends = [
        'yii\web\JqueryAsset',
        'budyaga\cropper\assets\JcropAsset',
        'budyaga\cropper\assets\SimpleAjaxUploaderAsset',
    ];
}
