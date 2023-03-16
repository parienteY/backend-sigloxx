<?php

namespace app\controllers;

use app\models\ArchivoPublico;
use app\models\Noticia;
use app\models\Unidad;
use app\models\User;
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
            $archivos = ArchivoPublico::find()->where(["id_unidad" => $id_unidad, "visible" => true])->orderBy(["id" => SORT_DESC])->all();
        }else{
            $count = ArchivoPublico::find()->where(["visible" => true])->count();
            $archivos = ArchivoPublico::find()->where(["visible" => true])->limit($limit)->offset($offset)->orderBy(["id" => SORT_DESC])->all();
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

     public function actionListarNoticias($id_noticia = "all", $id_unidad = 'all', $offset = 0, $limit = 10){
       $respuesta = [];
       $count = 0;
        if($id_noticia !== "all"){
          $response = Noticia::find()
          ->where(["id"=>$id_noticia, "visible" => true])
          ->all();
        }else{
          if($id_unidad !== 'all'){
            $count = Noticia::find()->limit(10)->where(["id_unidad" => $id_unidad, "visible" => true])->count();
            $response = Noticia::find()->limit(10)->where(["id_unidad" => $id_unidad, "visible" => true])->offset($offset)->limit($limit)->orderBy(["id" => SORT_DESC])->all();
          }else{
            $count = Noticia::find()->where(["visible" => true])->count();
            $response = Noticia::find()->where(["visible" => true])->offset($offset)->limit($limit)->orderBy(["id" => SORT_DESC])->all();
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
        return [
          "total" => $count,
          "data" => $respuesta];
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
        $searchUnidad = ["id_unidad" => $unidad, "visible" => true];
      }
      $consulta = ArchivoPublico::find()->where($searchUnidad)->andFilterWhere($searchWhere);
      $count = $consulta->count();
      $archivos = $consulta->limit($limit)->offset($offset)->orderBy(["id" => SORT_DESC])->all();

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

    public function actionGetJefe($id_unidad){
      $user = User::find()->select("id, email, nombres, apellidos, id_unidad")->where(["ci" => $id_unidad])->one();
      $unidad = Unidad::find()->where(["id" => $id_unidad])->one();

      return [
        $user,
        $unidad
      ];
    }


}
