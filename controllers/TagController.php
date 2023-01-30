<?php

namespace app\controllers;

use app\models\Tag;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\web\BadRequestHttpException;
use yii\base\ExitException;
use yii\filters\VerbFilter;
use yii\web\ServerErrorHttpException;

class TagController extends \yii\web\Controller
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
           "crear" => ["post"],
           "actualizar" => ["post"]
          ],
        ];
        return $behaviors;
      }

      public function actionCrear(){
        $params = Yii::$app->request->getBodyParams();

        $nuevoTag = new Tag($params);

        if($nuevoTag->save()){
          return [
            "status" => true,
            "data" => $nuevoTag
          ];
        }else{
          throw new ServerErrorHttpException("No se pudo crear el tag");
        }
      }
      public function actionActualizar($nombre){
        $params = Yii::$app->request->getBodyParams();

        $tag = Tag::find()
        ->where(["nombre" => $nombre])
        ->one();

        if($tag){
          $tag->nombre = $params["nombre"];
          $tag->save();
          return [
            "status" => true,
            "data" => $tag
          ];
        }else{
          throw new ServerErrorHttpException("No se pudo encontrar el tag indicado");
        }
      }



}
