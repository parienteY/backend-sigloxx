<?php

namespace app\controllers;

use app\models\ArchivoPrivado;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\web\BadRequestHttpException;
use yii\base\ExitException;
use yii\web\ServerErrorHttpException;
use yii\filters\VerbFilter;
class ArchivoPrivadoController extends \yii\web\Controller
{
    public function init() {
        Yii::warning(getallheaders());
        parent::init();
      }
  
      /**
       * @throws ExitException
       * @throws BadRequestHttpException
       */
      public function beforeAction($action) {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if (Yii::$app->getRequest()->getMethod() === 'OPTIONS') {
          Yii::$app->getResponse()->getHeaders()->set('Allow', 'POST GET PUT');
          Yii::$app->end();
        }
  
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
      }
  
      public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
          'class' => HttpBearerAuth::class,
          'except' => ['options'],
        ];
  
        $behaviors['access'] = [
          'class' => \mdm\admin\components\AccessControl::className(),
        ];
  
        $behaviors['verbs'] = [
          'class' => VerbFilter::className(),
          'actions' => [
            
          ],
        ];
        return $behaviors;
      }
      public static function crearArchivo($uploads, $idDirectorio, $nombreDirectorio){
        $savedfiles = [];
        $time = date("Y-m-d H:i:s");
        $path = '../uploads/'.$nombreDirectorio.'/';
        if(!file_exists($path)){
          mkdir($path, 0777, true);
        }
        foreach ($uploads as $file){
            $file->saveAs($path . $file->baseName . '.' . $file->extension);
            $params = [
              "id_directorio" => $idDirectorio,
              "direccion" => '/uploads/'.$nombreDirectorio.'/'. $file->baseName . '.' . $file->extension,
              "nombre" => $file->baseName,
              "extension" => $file->extension,
              "fecha_creacion" => $time,
              "fecha_actualizacion" => $time
            ];
            $nuevoArchivo = new ArchivoPrivado($params);
            $nuevoArchivo->save();
        }
      }

      public function actionEliminar($id_archivo){
        $archivo = ArchivoPrivado::find()
        ->where(["id" => $id_archivo])
        ->one();

        if(!$archivo){
          throw new ServerErrorHttpException("El archivo indicado no existe");
        }

        if($archivo->delete()){
          unlink("..".$archivo->direccion);
          return [
            "status" => true,
            "archivo" => $archivo
          ];
        }else{
          throw new ServerErrorHttpException("Error al eliminar el archivo");
        }
      }
}
