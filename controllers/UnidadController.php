<?php

namespace app\controllers;

use app\models\Unidad;
use app\models\User;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\web\BadRequestHttpException;
use yii\base\ExitException;
use yii\web\ServerErrorHttpException;
use yii\filters\VerbFilter;
class UnidadController extends \yii\web\Controller
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

      public function actionListar($id_unidad = "all"){
        if($id_unidad !== "all"){
          $unidades = Unidad::find()
          ->where(["id" => $id_unidad])
          ->one();
        }else{
          $unidades = Unidad::find()
          ->all();
        }

        return $unidades;
      }

      public function actionActualizar($id_unidad){
        $params = Yii::$app->request->getBodyParams();

        $unidad = Unidad::find()
          ->where(["id" => $id_unidad])
          ->one();

        if($unidad->update(true, $params)){
          return [
            "status" => true,
            "unidad_actualizada" => $unidad
          ];
        }else{
          throw new ServerErrorHttpException("No se pudo actualizar la unidad");
        }
      }

      
}
