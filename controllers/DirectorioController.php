<?php

namespace app\controllers;

use app\models\Directorio;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\web\BadRequestHttpException;
use yii\base\ExitException;
use app\models\UploadForm;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use yii\web\ServerErrorHttpException;
use app\models\User;
class DirectorioController extends \yii\web\Controller
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
            'crear' => ["post"],
            'actualizar' => ["post"]
          ],
        ];
        return $behaviors;
      }

      public function actionCrear(){
        $params = Yii::$app->request->getBodyParams();
        $time = date("Y-m-d H:i:s");

        $uploads = UploadedFile::getInstancesByName("files");
        // return $uploads;
        if (empty($uploads)){
          throw new ServerErrorHttpException("No hay archivos adjuntos");
        }
        $parametros = [
          "nombre" => $params["nombre"],
          "fecha_creacion" => $time,
          "fecha_actualizacion" => $time,
          "descripcion" => $params["descripcion"],
          "id_unidad" => $params["id_unidad"]
        ];
        
        $newDirectory = new Directorio($parametros);

        if($newDirectory->save()){
          ArchivoPublicoController::crearArchivo($uploads, $newDirectory["id"], $newDirectory["nombre"]);
        }else{
          throw new ServerErrorHttpException("No se pudo crear el directorio");
        }
        // $uploads now contains 1 or more UploadedFile instances
        
      }

      public function actionActualizar($id_directorio){
        $directorio = Directorio::find()
        ->select("nombre")
        ->where(["id" => $id_directorio])
        ->one();
        $uploads = UploadedFile::getInstancesByName("files");
        // return $uploads;
        if (empty($uploads)){
          throw new ServerErrorHttpException("No hay archivos adjuntos");
        }else{
          ArchivoPublicoController::crearArchivo($uploads, $id_directorio, $directorio->nombre);
        }
      }

}
