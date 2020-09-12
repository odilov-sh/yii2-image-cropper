<?php

namespace odilov\cropper;

use Yii;
use odilov\cropper\assets\CropperAsset;
use budyaga\cropper\Widget;
use yii\helpers\Json;

class Cropper extends Widget
{

    public function run()
    {
        $this->registerClientAssets();
        return $this->render('widget', [
            'model' => $this->model,
            'widget' => $this
        ]);
    }

    /**
     * Register widget asset.
     */
    public function registerClientAssets()
    {
        $view = $this->getView();
        $assets = CropperAsset::register($view);

        if ($this->noPhotoImage == '') {
            $this->noPhotoImage = $assets->baseUrl . '/img/nophoto.png';
        }

        $settings = array_merge([
            'url' => $this->uploadUrl,
            'name' => $this->uploadParameter,
            'maxSize' => $this->maxSize / 1024,
            'allowedExtensions' => explode(', ', $this->extensions),
            'size_error_text' => Yii::t('cropper', 'TOO_BIG_ERROR', ['size' => $this->maxSize / (1024 * 1024)]),
            'ext_error_text' => Yii::t('cropper', 'EXTENSION_ERROR', ['formats' => $this->extensions]),
            'accept' => 'image/*',
        ], $this->pluginOptions);

        if(is_numeric($this->aspectRatio)) {
            $settings['aspectRatio'] = $this->aspectRatio;
        }

        if ($this->onCompleteJcrop)
            $settings['onCompleteJcrop'] = $this->onCompleteJcrop;

        $view->registerJs(
            'jQuery("#' . $this->options['id'] . '").parent().find(".new-photo-area").cropper(' . Json::encode($settings) . ', ' . $this->width . ', ' . $this->height . ');',
            $view::POS_READY
        );
    }

}
