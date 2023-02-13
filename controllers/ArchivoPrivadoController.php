<?php

namespace app\controllers;

use app\models\ArchivoPrivado;
use app\models\ArchivoPublico;
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
          Yii::$app->getResponse()->getHeaders()->set('Allow', 'POST GET PUT DELETE');
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
            "eliminar" => ["delete"]
          ],
        ];
        return $behaviors;
      }
      public static function crearArchivo($uploads, $idDirectorio, $nombreDirectorio){
        $savedfiles = [];
        $time = date("Y-m-d H:i:s");
        $path = '../web/uploads/'.$nombreDirectorio.'/';
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
              "id_directorio" => $idDirectorio,
              "direccion" => '/uploads/'.$nombreDirectorio.'/'. $basename . '.' . $file->extension,
              "nombre" => $file->baseName,
              "extension" => $file->extension,
              "type" => $file->type,
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
          unlink("../web".$archivo->direccion);
          return [
            "status" => true,
            "archivo" => $archivo
          ];
        }else{
          throw new ServerErrorHttpException("Error al eliminar el archivo");
        }
      }

      public function actionObtenerArchivo($id){
        $archivo = ArchivoPrivado::find()->where(["id" => $id])->one();
        if($archivo){
          Yii::$app->response->sendFile("../web".$archivo["direccion"], $archivo["nombre"], ['inline' => false])->send();
        }else{
          $archivoP = ArchivoPublico::find()->where(["id" => $id])->one();
          if($archivoP){
            Yii::$app->response->sendFile("../web".$archivoP["direccion"], $archivoP["nombre"], ['inline' => false])->send();
          }
        }
      }
}
