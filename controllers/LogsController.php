<?php

namespace app\controllers;

use app\models\Logs;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\web\BadRequestHttpException;
use yii\base\ExitException;
use yii\filters\VerbFilter;
class LogsController extends \yii\web\Controller
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
           "listar" => ["get"]
          ],
        ];
        return $behaviors;
      }

      public function actionListar($offset = 0, $limit = 20){
        $data = [];
        $count = Logs::find()->count();
        $data = Logs::find()->offset($offset * $limit)->limit($limit)->orderBy(["id" => SORT_DESC])->all();
        return [
          "total" => $count,
          "data" => $data
        ];
      }

}
