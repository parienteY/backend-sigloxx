<?php

namespace app\controllers;

use app\models\ArchivoPublico;
use app\models\Noticia;
use PHPUnit\Framework\Error\Notice;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\web\BadRequestHttpException;
use yii\base\ExitException;
use yii\filters\VerbFilter;
use yii\web\ServerErrorHttpException;
use yii\web\UploadedFile;

class NoticiaController extends \yii\web\Controller
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
            "crear" => ["post"],
            "actualizar" => ["post"],
            "eliminar" => ["delete"],
            "eliminar-adjunto" => ["delete"],
            "agregar-adjuntos" => ["post"]
          ],
        ];
        return $behaviors;
      }

     

      public function actionCrear(){
        $params = Yii::$app->request->getBodyParams();
        $uploads = UploadedFile::getInstancesByName("files");
        $user = Yii::$app->user->identity;
        $time = date("Y-m-d H:i:s");
        $unidad=null;
        
        if($user->tag_rol === "SUPER"){
          $unidad = $params['unidad'];
        }else{
          $unidad = $user->id_unidad;
        }
        $body = [
          "titulo" => $params["titulo"],
          "subtitulo" => $params["subtitulo"],
          "foto" => $params["foto"],
          "id_unidad" => $unidad,
          "fecha_creacion" => $time,
          "fecha_actualizacion" => $time,
          "visible" => true
        ];
        if(!is_null($uploads)){
          $body["archivos_adjuntos"] = ArchivoPublicoController::crearAdjunto($uploads);
        }

        $nuevaNoticia = new Noticia($body);
        if($nuevaNoticia->save()){
          UtilController::generatedLog($nuevaNoticia, "noticia", "CREAR");
          return [
            "status" => true,
            "noticia" => $nuevaNoticia
          ];
        }else{
          return $nuevaNoticia->getErrors();
        }
      }

      public function actionActualizar($id_noticia){
        $params = Yii::$app->request->getBodyParams();

        $noticia = Noticia::find()
        ->where(["id" => $id_noticia])
        ->one();

        if($noticia){
          if($noticia->load($params, '') && $noticia->save()){
            UtilController::generatedLog($noticia, "noticia", "ACTUALIZAR");
            return [
              "status" => true,
              "noticia_actualizada" => $noticia
            ];
          }else{
            throw new ServerErrorHttpException("No se pudo actualizar la noticia");
          }
        }else{
          throw new ServerErrorHttpException("No se pudo encontro la noticia");
        }
      }

      public function actionEliminar($id){
        $noticia = Noticia::find()->where(["id" => $id])->one();
        if($noticia){
            if (!is_null($noticia->archivos_adjuntos)) {
              $ids_ar = $noticia->archivos_adjuntos["archivos"];
              foreach($ids_ar as $doc){
                $archivo = ArchivoPublico::find()->where(["id" => $doc])->one();
                if($archivo){
                 if($archivo->delete()){
                   unlink("../web".$archivo->direccion);
                 }
                }
              }
              if($noticia->delete()){
                UtilController::generatedLog(["noticia" => $noticia, "archivos" => $noticia->archivos_adjuntos], "noticia", "ELIMINAR");
                return [
                  "status" => true,
                  "msg" => "La noticia ha sido eliminada"
                ];
              }else{
                throw new ServerErrorHttpException("Error al eliminar la noticia");
              }
            }
        }else{
          throw new ServerErrorHttpException("Noticia no encontrada");
        }
      }

      public function actionEliminarAdjunto($id, $id_adjunto){
        $eliminado = ArchivoPublicoController::eliminarAdjunto($id_adjunto);

        if($eliminado){
          $noticia = Noticia::find()->where(["id" => $id])->one();
          if($noticia){
            $ids = $noticia->archivos_adjuntos["archivos"];
            if (($clave = array_search($id_adjunto, $ids)) !== false) {
              unset($ids[$clave]);
            }
            $noticia->archivos_adjuntos = [
              "archivos" => $ids
            ];
            if($noticia->save()){
              UtilController::generatedLog(["noticia" => $noticia, "archivos" => $eliminado], "noticia", "ELIMINAR_ADJUNTOS");
              return [
                "status" => true,
                "msg" => "Archivo eliminado"
              ];
            }
          }
        }else{
          throw new ServerErrorHttpException("Error al eliminar el archivo");
        }
      }

      public function actionAgregarAdjuntos($id){
        $uploads = UploadedFile::getInstancesByName("files");
        if(!is_null($uploads)){
          $noticia = Noticia::find()->where(["id" => $id])->one();
          if($noticia){
            $adjuntos = ArchivoPublicoController::crearAdjunto($uploads);
            $adjuntosNoticia = $noticia->archivos_adjuntos;
            $merged = array_merge($adjuntos["archivos"], $adjuntosNoticia["archivos"]);
            $noticia->archivos_adjuntos = [
              "archivos" => $merged
            ];
            if($noticia->save()){
              UtilController::generatedLog(["noticia" => $noticia, "archivos" => $uploads], "noticia", "ACTUALIZAR_ADJUNTOS");
              return [
                "status" => true,
                "msg" => "Archivos anadidos"
              ];
            }else{
              throw new ServerErrorHttpException("No ha anadido un archivo");
            }
          }else{
            throw new ServerErrorHttpException("Noticia no encontrada");
          }
        }else{
          throw new ServerErrorHttpException("No ha anadido un archivo");
        }
      }

      public function actionFiltro($search = "all", $unidad = "all", $limit = 10, $offset = 0){
        $user = Yii::$app->user->identity;
        $searchUnidad = [];
        $searchWhere = [];
        $response = [];
        $respuesta = [];
        if($search !== "all"){
          $searchWhere = [
            'or',
            ['ilike', 'noticia.titulo', $search],
            ['ilike', 'noticia.subtitulo', $search],
          ];
        }
        if($unidad !== "all" && $user->tag_rol === "SUPER"){
          $searchUnidad = ["id_unidad" => $unidad];
        }

        if($user->tag_rol !== "SUPER"){
          $searchUnidad = ["id_unidad" => $user->id_unidad];
        }

        $response = Noticia::find()->where($searchUnidad)->andFilterWhere($searchWhere)->offset($offset)->limit($limit)->orderBy(["id" => SORT_DESC])->all();
        $count = Noticia::find()->where($searchUnidad)->andFilterWhere($searchWhere)->count();

        foreach($response as $res){
          if (!is_null($res->archivos_adjuntos)) {
            $ids_ar = $res->archivos_adjuntos["archivos"];
            $archivos = ArchivoPublico::find()
              ->where(["id" => $ids_ar])
              ->all();
              $respuesta [] = [
                "id" => $res->id,
                "titulo" => $res->titulo,
                "subtitulo" => $res->subtitulo,
                "foto" => $res->foto,
                "archivos_adjuntos" => $archivos,
                "id_unidad" => $res->unidad->nombre,
                "fecha_actualizacion" => $res->fecha_actualizacion
              ];
          }
        }
        return [
          "total" => $count,
          "data" => $respuesta
        ];
        
      }

      public function actionCambiarEstado($id){

        $noticia = Noticia::find()
          ->where(["id" => $id])
          ->one();

        if($noticia->load(["visible" => !$noticia->visible], '') && $noticia->save()){
          return [
            "status" => true,
          ];
        }else{
          throw new ServerErrorHttpException("No se pudo actualizar el noticia");
        }
      }

      public function actionListarOcultos($search = "all", $unidad = "all", $limit = 10, $offset = 0){
        $user = Yii::$app->user->identity;
        $searchUnidad = [];
        $searchWhere = [];
        $response = [];
        $respuesta = [];
        if($search !== "all"){
          $searchWhere = [
            'or',
            ['ilike', 'noticia.titulo', $search],
            ['ilike', 'noticia.subtitulo', $search],
          ];
        }
        if($unidad !== "all" && $user->tag_rol === "SUPER"){
          $searchUnidad = ["id_unidad" => $unidad, "visible" => true];
        }

        if($user->tag_rol !== "SUPER"){
          $searchUnidad = ["id_unidad" => $user->id_unidad, "visible" => true];
        }

        $response = Noticia::find()->where($searchUnidad)->andFilterWhere($searchWhere)->offset($offset)->limit($limit)->orderBy(["id" => SORT_DESC])->all();
        $count = Noticia::find()->where($searchUnidad)->andFilterWhere($searchWhere)->count();

        foreach($response as $res){
          if (!is_null($res->archivos_adjuntos)) {
            $ids_ar = $res->archivos_adjuntos["archivos"];
            $archivos = ArchivoPublico::find()
              ->where(["id" => $ids_ar])
              ->all();
              $respuesta [] = [
                "id" => $res->id,
                "titulo" => $res->titulo,
                "subtitulo" => $res->subtitulo,
                "foto" => $res->foto,
                "archivos_adjuntos" => $archivos,
                "id_unidad" => $res->unidad->nombre,
                "fecha_actualizacion" => $res->fecha_actualizacion
              ];
          }
        }
        return [
          "total" => $count,
          "data" => $respuesta
        ];
        
      }
}
