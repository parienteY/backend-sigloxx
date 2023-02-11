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
  

     public function actionListarArchivosPublicos($id_unidad = "all"){
      $response = [];
        if($id_unidad !== "all"){
            $archivos = ArchivoPublico::find()->where(["id_unidad" => $id_unidad])->one();
        }else{
            $archivos = ArchivoPublico::find()->all();
        }
        foreach($archivos  as $a){
          
          if(!is_null($a->unidad)){
            $unidad = $a->unidad->nombre;
          }else{
            $unidad = null;
          }

          $response []= [
            "id" => $a->id,
            "id_unidad" => $a->id_unidad,
            "direccion" => $a->direccion,
            "nombre" => $a->nombre,
            "extension" => $a->extension,
            'fecha_creacion' => $a->fecha_creacion,
            "fecha_actualizacion" => $a->fecha_actualizacion,
            "nombre_unidad" => $unidad
          ];
        }
        return $response;
     }

     public function actionListarNoticias($id_noticia = "all", $id_unidad = 'all'){
       $respuesta = [];
        if($id_noticia !== "all"){
          $response = Noticia::find()
          ->where(["id"=>$id_noticia])
          ->all();
        }else{
          if($id_unidad !== 'all'){
            $response = Noticia::find()->where(["id_unidad" => $id_unidad])->all();
          }else{
            $response = Noticia::find()->all();
          }
        }
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
        return $respuesta;
      }
     public function actionInfoUnidades($id_unidad = "all"){
        if($id_unidad !== "all"){
            $unidades = Unidad::find()
            ->where(["id_unidad" => $id_unidad ])
            ->all();
        }else{
            $unidades = Unidad::find()->all();
        }
        return $unidades;
     }



}
