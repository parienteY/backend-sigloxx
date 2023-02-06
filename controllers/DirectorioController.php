<?php

namespace app\controllers;

use app\models\ArchivoPrivado;
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
            'actualizar' => ["post"],
            'listar' => ["get"]
          ],
        ];
        return $behaviors;
      }

      public function actionListar($id_unidad, $id_directorio = "all"){

        if($id_directorio !== "all"){
          $directorio = Directorio::find()
          ->where(["id" => $id_directorio, "id_unidad" => $id_unidad])
          ->one();
          $response = [
            "id" => $directorio->id,
            "nombre" => $directorio->nombre,
            "fecha_creacion" => $directorio->fecha_creacion,
            "descripciom" => $directorio->descripcion,
            "archivos" => $directorio->archivoPrivados,
            "nombre_unidad" => $directorio->unidad->nombre,
            "id_unidad" => $directorio->id_unidad
          ];
        }else{
          $directorios = Directorio::find()
          ->where(["id_unidad" => $id_unidad])
          ->all();

          foreach($directorios as $a){
            $response []= [
              "id" => $a->id,
              "nombre" => $a->nombre,
              "fecha_creacion" => $a->fecha_creacion,
              "descripciom" => $a->descripcion,
              "archivos" => $a->archivoPrivados,
              "id_unidad" => $a->id_unidad,
              "nombre_unidad" => $a->unidad->nombre
            ];
          }
        }
        return $response;
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
          ArchivoPrivadoController::crearArchivo($uploads, $newDirectory["id"], $newDirectory["nombre"]);
          return [
            "msg" => "Creado exitosamente"
          ];
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
          ArchivoPrivadoController::crearArchivo($uploads, $id_directorio, $directorio->nombre);
        }
      }

}
