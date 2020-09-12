<?php


namespace odilov\cropper;

use Yii;
use Imagine\Image\Box;
use yii\base\DynamicModel;
use yii\imagine\Image;
use yii\web\BadRequestHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class UploadAction extends \budyaga\cropper\actions\UploadAction
{

    public $deleteOldImage = true;

    public function run()
    {
        if (Yii::$app->request->isPost) {

            $deleteOnly = Yii::$app->request->post('deleteOnly', false);

            if ($deleteOnly){
                $oldImage =  Yii::$app->request->post('oldImage');
                return $this->deleteOldImage($oldImage);
            }


            $file = UploadedFile::getInstanceByName($this->uploadParam);
            $model = new DynamicModel(compact($this->uploadParam));
            $model->addRule($this->uploadParam, 'image', [
                'maxSize' => $this->maxSize,
                'tooBig' => Yii::t('cropper', 'TOO_BIG_ERROR', ['size' => $this->maxSize / (1024 * 1024)]),
                'extensions' => explode(', ', $this->extensions),
                'wrongExtension' => Yii::t('cropper', 'EXTENSION_ERROR', ['formats' => $this->extensions])
            ])->validate();

            if ($model->hasErrors()) {
                $result = [
                    'error' => $model->getFirstError($this->uploadParam)
                ];
            } else {
                $model->{$this->uploadParam}->name = uniqid() . mt_rand().'.' . $model->{$this->uploadParam}->extension;
                $request = Yii::$app->request;

                $width = $request->post('width', $this->width);
                $height = $request->post('height', $this->height);

                $image = Image::crop(
                    $file->tempName . $request->post('filename'),
                    intval($request->post('w')),
                    intval($request->post('h')),
                    [$request->post('x'), $request->post('y')]
                )->resize(
                    new Box($width, $height)
                );
                if (!file_exists($this->path) || !is_dir($this->path)) {
                    $result = [
                        'error' => Yii::t('cropper', 'ERROR_NO_SAVE_DIR')]
                    ;
                } else {
                    $saveOptions = ['jpeg_quality' => $this->jpegQuality, 'png_compression_level' => $this->pngCompressionLevel];
                    if ($image->save($this->path . $model->{$this->uploadParam}->name, $saveOptions)) {
                        $result = [
                            'filelink' => $this->url . $model->{$this->uploadParam}->name,
                        ];
                    } else {
                        $result = [
                            'error' => Yii::t('cropper', 'ERROR_CAN_NOT_UPLOAD_FILE')
                        ];
                    }
                }
            }
            Yii::$app->response->format = Response::FORMAT_JSON;

            return $result;
        } else {
            throw new BadRequestHttpException(Yii::t('cropper', 'ONLY_POST_REQUEST'));
        }
    }

    public function deleteOldImage($oldImage='')
    {

        if (!$this->deleteOldImage){
            return false;
        }

        $array = explode('/', $oldImage);
        $last = count($array) - 1;
        $imageName = $array[$last];

        $oldImagePath = $this->path.$imageName;
        if (is_file($oldImagePath)){
            unlink($oldImagePath);
            return true;
        }
        return false;
    }

}