<?php

namespace odilov\cropper;

use Yii;
use odilov\cropper\assets\CropperAsset;
use budyaga\cropper\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class Cropper extends Widget
{

    public $isBs4;

    public function init()
    {
        $this->registerTranslations();
        parent::init();
    }

    public function run()
    {
        $this->registerClientAssets();
        return $this->render('widget', [
            'model' => $this->model,
            'widget' => $this,
            'isBs4' => $this->isBs4(),
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

        if ($this->isBs4()){

            $css = <<<CSS

               .new-photo-area {
                position: relative!important;
            }
            .new-photo-area .jcrop-holder{
                position: absolute!important;
                top: 0px!important;
                z-index: 99;
            }

CSS;
            $this->view->registerCss($css, [], 'jcrop-holder');


        }
    }

    /**
     * Register widget translations.
     */
    public static function registerTranslations()
    {
        if (!isset(Yii::$app->i18n->translations['cropper']) && !isset(Yii::$app->i18n->translations['cropper/*'])) {
            Yii::$app->i18n->translations['cropper'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'basePath' => '@odilov/cropper/messages',
                'forceTranslation' => true,
                'fileMap' => [
                    'cropper' => 'cropper.php'
                ]
            ];
        }
    }

    public function isBs4()
    {
        if ($this->isBs4 !== null){
            return $this->isBs4;
        }
        $v =  ArrayHelper::getValue(Yii::$app->params, 'bsVersion', '3');
        $ver = (string)$v;
        return substr(trim($ver), 0, 1) == '4';
    }

}
