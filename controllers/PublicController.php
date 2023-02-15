<?php

namespace app\controllers;

use app\models\ArchivoPublico;
use app\models\Noticia;
use app\models\Unidad;
use Yii;
use yii\web\BadRequestHttpException;
use yii\base\ExitException;
use yii\web\ServerErrorHttpException;

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
  

     public function actionListarArchivosPublicos($id_unidad = "all", $limit = 20, $offset = 0){
      $count = null;
      $response = [];
        if($id_unidad !== "all"){
            $archivos = ArchivoPublico::find()->where(["id_unidad" => $id_unidad])->all();
        }else{
            $count = ArchivoPublico::find()->count();
            $archivos = ArchivoPublico::find()->limit($limit)->offset($offset)->all();
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
            "type" => $a->type,
            "extension" => $a->extension,
            'fecha_creacion' => $a->fecha_creacion,
            "fecha_actualizacion" => $a->fecha_actualizacion,
            "nombre_unidad" => $unidad
          ];
        }

        if(!is_null($count)){
          return [
            "total" => $count,
            "data" => $response
          ];
        }else{
          return $response;
        }
        
     }

     public function actionListarNoticias($id_noticia = "all", $id_unidad = 'all'){
       $respuesta = [];
        if($id_noticia !== "all"){
          $response = Noticia::find()
          ->where(["id"=>$id_noticia])
          ->all();
        }else{
          if($id_unidad !== 'all'){
            $response = Noticia::find()->limit(10)->where(["id_unidad" => $id_unidad])->all();
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

     public function actionFiltro($search = "all", $unidad = "all", $limit = 20, $offset = 0){
      $searchUnidad = [];
      $searchWhere = [];
      $response = [];
      if($search !== "all"){
        $searchWhere = [
          'or',
          ['ilike', 'archivo_publico.nombre', $search],
        ];
      }
      if($unidad !== "all"){
        $searchUnidad = ["id_unidad" => $unidad];
      }
      $consulta = ArchivoPublico::find()->where($searchUnidad)->andFilterWhere($searchWhere);
      $count = $consulta->count();
      $archivos = $consulta->limit($limit)->offset($offset)->all();

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
          "type" => $a->type,
          'fecha_creacion' => $a->fecha_creacion,
          "fecha_actualizacion" => $a->fecha_actualizacion,
          "nombre_unidad" => $unidad
        ];
      }
      return [
        "total" => $count,
        "data" => $response
      ];
    }

    public function actionObtenerArchivo($id){
        $archivoP = ArchivoPublico::find()->where(["id" => $id])->one();
        if($archivoP){
          Yii::$app->response->sendFile("../web".$archivoP["direccion"], $archivoP["nombre"], ['inline' => false])->send();
        }else{
          throw new ServerErrorHttpException("No existe el archivo");
        }
      
    }


}
