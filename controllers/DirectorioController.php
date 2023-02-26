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
use yii\helpers\FileHelper;

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
            'crear' => ["post"],
            'actualizar' => ["post"],
            'listar' => ["get"],
            'eliminar' => ["delete"]
          ],
        ];
        return $behaviors;
      }

      public function actionListar( $id_directorio = "all", $offset = 0, $limit = 10 ){
        $response = [];
        $count = 0;
        $user = Yii::$app->user->identity;
        if($id_directorio !== "all"){
          $directorio = Directorio::find()
          ->where(["id" => $id_directorio, "id_unidad" => $user->id_unidad])
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
          if($user->tag_rol === "SUPER"){
            $count = $directorios = Directorio::find()
            ->count();
            $directorios = Directorio::find()
            ->offset($offset)
            ->limit($limit)
            ->all();
          }else{
            $count = Directorio::find()
            ->where(["id_unidad" => $user->id_unidad])
            ->count();
            $directorios = Directorio::find()
            ->where(["id_unidad" => $user->id_unidad])
            ->offset($offset)
            ->limit($limit)
            ->all();
          }

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
        return [
          "total" => $count,
          "data" => $response];
      }


      public function actionCrear(){
        $unidad = null;
        $params = Yii::$app->request->getBodyParams();
        $time = date("Y-m-d H:i:s");
        $user = Yii::$app->user->identity;
        $uploads = UploadedFile::getInstancesByName("files");
        // return $uploads;
        if (empty($uploads)){
          throw new ServerErrorHttpException("No hay archivos adjuntos");
        }
        if($user->tag_rol === "SUPER"){
          $unidad = $params["unidad"];
        }else{
          $unidad = $user->id_unidad;
        }
        $parametros = [
          "nombre" => $params["nombre"],
          "fecha_creacion" => $time,
          "fecha_actualizacion" => $time,
          "descripcion" => $params["descripcion"],
          "id_unidad" => $unidad
        ];
        
        $newDirectory = new Directorio($parametros);

        if($newDirectory->save()){
          ArchivoPrivadoController::crearArchivo($uploads, $newDirectory["id"], $newDirectory["nombre"]);
          UtilController::generatedLog(["directorio" => $newDirectory, "archivos" => $uploads], "directorio", "ELIMINAR");
          return [
            "msg" => "Creado exitosamente"
          ];
        }else{
          throw new ServerErrorHttpException("No se pudo crear el directorio");
        }
        // $uploads now contains 1 or more UploadedFile instances
        
      }

      public function actionActualizar($id){
        $directorio = Directorio::find()
        ->select("nombre")
        ->where(["id" => $id])
        ->one();
        $uploads = UploadedFile::getInstancesByName("files");
        if (empty($uploads)){
          throw new ServerErrorHttpException("No hay archivos adjuntos");
        }else{
          ArchivoPrivadoController::crearArchivo($uploads, $id, $directorio->nombre);
          UtilController::generatedLog(["directorio" => $directorio, "archivos" => $uploads], "directorio", "ACTUALIZAR");
        }
      }

      public function actionEliminar($id){
        $directorio = Directorio::find()->where(["id" => $id])->one();
        $deletes = [];
        if($directorio){
              $ids_ar = $directorio->archivoPrivados;
              foreach($ids_ar as $doc){
                 if($doc->delete()){
                  array_push($deletes, $doc);
                   unlink("../web".$doc->direccion);
                 }
              }
              if($directorio->delete()){
                FileHelper::removeDirectory("../web/uploads/".$directorio->nombre);
                UtilController::generatedLog(["directorio" => $directorio, "archivos" => $deletes], "directorio", "ELIMINAR");
                return [
                  "status" => true,
                  "msg" => "La directorio ha sido eliminado"
                ];
              }else{
                throw new ServerErrorHttpException("Error al eliminar la directorio");
              }
        }
      }
    

      public function actionFiltro($search = "all", $unidad = "all"){
        $response = [];
        $connection = \Yii::$app->getDb();
        $consulta = "select d.*, u.nombre as nombre_unidad from unidad u, directorio d where d.id_unidad = u.id";
        $filterSearch = "";
        $filterUnidad = "";
        if($search !== "all"){
          $filterSearch = " and (d.nombre ILIKE '%". $search . "%' or u.nombre ILIKE '%". $search . "%' or u.descripcion ILIKE '%". $search . "%')";
        }
        if($unidad !== "all"){
          $filterUnidad = " and u.id = ".$unidad;
        }

        $consulta = $consulta. $filterSearch . $filterUnidad;
        $query = $connection->createCommand($consulta);
        $data = $query->queryAll();
        $user = Yii::$app->user->identity;

        foreach($data as $d){
          $archivos = ArchivoPrivado::find()->where(["id_directorio" => $d["id"]])->all();
          $response [] = [
            "id" => $d["id"],
            "nombre" => $d["nombre"],
            "fecha_creacion" => $d["fecha_creacion"],
            "fecha_actualizacion" => $d["fecha_actualizacion"],
            "descripcion" => $d["descripcion"],
            "id_unidad" => $user->id_unidad,
            "nombre_unidad" => $d["nombre_unidad"],
            "archivos" => $archivos
          ];
        }

        return $response;
      }
}
