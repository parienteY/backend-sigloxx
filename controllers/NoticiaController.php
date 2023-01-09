<?php

namespace app\controllers;

use app\models\Noticia;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\web\BadRequestHttpException;
use yii\base\ExitException;
use yii\filters\VerbFilter;
use yii\web\ServerErrorHttpException;

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
          $response = Noticia::find()->all();
        }

        return $response;
      }

      public function actionCrear(){
        $params = Yii::$app->request->getBodyParams();

        $nuevaNoticia = new Noticia($params);

        if($nuevaNoticia->save()){
          return [
            "status" => true,
            "noticia" => $nuevaNoticia
          ];
        }else{
          throw new ServerErrorHttpException("No se pudo crear la noticia");
        }
      }

      public function actionActualizar($id_noticia){
        $params = Yii::$app->request->getBodyParams();

        $noticia = Noticia::find()
        ->where(["id" => $id_noticia])
        ->one();

        if($noticia->update(true, $params)){
          return [
            "status" => true,
            "noticia_actualizada" => $noticia
          ];
        }else{
          throw new ServerErrorHttpException("No se pudo actualizar la noticia");
        }
      }
}
