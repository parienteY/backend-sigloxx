<?php

namespace app\controllers;

use app\models\ArchivoPublico;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\web\BadRequestHttpException;
use yii\base\ExitException;
use yii\filters\VerbFilter;
use yii\web\ServerErrorHttpException;
use yii\web\UploadedFile;

class ArchivoPublicoController extends \yii\web\Controller
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
            "eliminar" => ["post"],
            "crear" => ["post"],
          ],
        ];
        return $behaviors;
      }


      public static function actionCrear(){
        $savedfiles = [];
        $uploads = UploadedFile::getInstancesByName("files");
        $time = date("Y-m-d H:i:s");
        $path = '../web/uploads/public/';
        $user = Yii::$app->user->identity;
        if(!file_exists($path)){
          mkdir($path, 0777, true);
        }
        foreach ($uploads as $file){
          if(!file_exists($path . $file->baseName . '.' . $file->extension)){
            $file->saveAs($path . $file->baseName . '.' . $file->extension);
            $basename = $file->baseName;
          }else{
            $file->saveAs($path . $file->baseName . '(1).' . $file->extension);
            $basename = $file->baseName. '(1)';
          }
            $file->saveAs($path . $file->baseName . '.' . $file->extension);
            $params = [
              "direccion" => '/uploads/public/'. $basename . '.' . $file->extension,
              "id_unidad" => $user->id_unidad,
              "nombre" => $file->baseName,
              "extension" => $file->extension,
              "type" => $file->type,
              "fecha_creacion" => $time,
              "fecha_actualizacion" => $time
            ];
            $nuevoArchivo = new ArchivoPublico($params);
            $nuevoArchivo->save();
            array_push($savedfiles, $params);
        }
        UtilController::generatedLog($savedfiles, "archivo_publico", "CREAR");

        return [
          "status" => true,
          "msg" => "Archivos creados"
        ];
      }

      public static function eliminarAdjunto($id){
        $archivo = ArchivoPublico::find()->where(['id' => $id])->one();

        if($archivo->delete()){
          unlink("../web".$archivo->direccion);
          return true;
        }else{
          return false;
        }
      }

      public static function crearAdjunto($uploads){
        $savedfiles = [];
        $time = date("Y-m-d H:i:s");
        $path = '../web/uploads/noticias/';
        if(!file_exists($path)){
          mkdir($path, 0777, true);
        }
        foreach ($uploads as $file){
          if(!file_exists($path . $file->baseName . '.' . $file->extension)){
            $file->saveAs($path . $file->baseName . '.' . $file->extension);
            $basename = $file->baseName;
          }else{
            $file->saveAs($path . $file->baseName . '(1).' . $file->extension);
            $basename = $file->baseName. '(1)';
          }
            $params = [
              "direccion" => '/uploads/noticias/'. $basename . '.' . $file->extension,
              "nombre" => $file->baseName,
              "extension" => $file->extension,
              "type" => $file->type,
              "fecha_creacion" => $time,
              "fecha_actualizacion" => $time
            ];
            $nuevoArchivo = new ArchivoPublico($params);
            if($nuevoArchivo->save()){
              array_push($savedfiles, $nuevoArchivo->id);
            }
        }
        return [
          "archivos" => $savedfiles
        ];
      }


      public function actionEliminar($id_archivo){
        $archivo = ArchivoPublico::find()
        ->where(["id" => $id_archivo])
        ->one();

        if(!$archivo){
          throw new ServerErrorHttpException("El archivo indicado no existe");
        }

        if($archivo->delete()){
          unlink("../web".$archivo->direccion);
          return [
            "status" => true,
            "archivo" => $archivo
          ];
        }else{
          throw new ServerErrorHttpException("Error al eliminar el archivo");
        }
      }

}
