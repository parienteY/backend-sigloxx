<?php

namespace app\controllers;

use app\models\ArchivoPublico;
use app\models\Noticia;
use app\models\Unidad;
use Yii;
use yii\web\BadRequestHttpException;
use yii\base\ExitException;


class PublicController extends \yii\web\Controller
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
  

     public function actionListarArchivosPublicos($id_unidad = null){

        if(!is_null($id_unidad)){
            $archivos = ArchivoPublico::find()->where(["id_unidad" => $id_unidad])->one();
        }else{
            $archivos = ArchivoPublico::find()->all();
        }
        return $archivos;
     }

     public function actionListarNoticias($id_noticia = null, $id_unidad){
        if(!is_null($id_noticia)){
          $response = Noticia::find()
          ->where(["id"=>$id_noticia, "id_unidad" => $id_unidad])
          ->all();
        }else{
          $respuesta = [];
          $response = Noticia::find()->where(["id_unidad" => $id_unidad])->all();
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
     public function actionInfoUnidades($id_unidad = null){
        if(!is_null($id_unidad)){
            $unidades = Unidad::find()
            ->where(["id_unidad" => $id_unidad ])
            ->all();
        }else{
            $unidades = Unidad::find()->all();
        }
        return $unidades;
     }



}
