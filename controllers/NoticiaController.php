<?php

namespace app\controllers;

use app\models\ArchivoPublico;
use app\models\Noticia;
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
            "listar" => ["get"],
            "crear" => ["post"],
            "actualizar" => ["put"]
          ],
        ];
        return $behaviors;
      }

      public function actionListar($id_noticia = null){
        if(!is_null($id_noticia)){
          $response = Noticia::find()
          ->where(["id"=>$id_noticia])
          ->all();
        }else{
          $respuesta = [];
          $response = Noticia::find()->all();
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
        }

        return $respuesta;
      }

      public function actionCrear(){
        $params = Yii::$app->request->getBodyParams();
        $uploads = UploadedFile::getInstancesByName("files");
        $time = date("Y-m-d H:i:s");
        $body = [
          "titulo" => $params["titulo"],
          "subtitulo" => $params["subtitulo"],
          "foto" => $params["foto"],
          "id_unidad" => $params["id_unidad"],
          "fecha_creacion" => $time,
          "fecha_actualizacion" => $time
          
        ];
        if(!is_null($uploads)){
          $body["archivos_adjuntos"] = ArchivoPublicoController::crearAdjunto($uploads);
        }

        $nuevaNoticia = new Noticia($body);
        if($nuevaNoticia->save()){
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
}
