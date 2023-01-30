<?php

namespace app\controllers;
use Yii;
use app\models\User;
use yii\web\Response;
use yii\filters\auth\HttpBearerAuth;
use yii\web\BadRequestHttpException;
use yii\base\ExitException;
use yii\web\ServerErrorHttpException;
use yii\filters\VerbFilter;
class AuthController extends \yii\web\Controller
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
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->getRequest()->getMethod() === 'OPTIONS') {
          Yii::$app->getResponse()->getHeaders()->set('Allow', 'POST GET PUT');
          Yii::$app->end();
        }
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
      }
        
      

      public function actionLogin() {
        $r = null;
        $params = Yii::$app->getRequest()->getBodyParams();
        $user = User::find()->where(['email' => $params['email'], 'removed' => null])->one();
        if ($user && Yii::$app->getSecurity()->validatePassword($params['password'], $user->password_hash)) {
          $r = [
            "msg" => "Inicio de sesion exitoso",
            "token" => $user->access_token,
          ];
          $user->load(['sesion_status' => 'online'], '');
          $user->save();
        } else {
          Yii::$app->response->statusCode = 500;
          $r = ["status" => $this->response->statusCode, "msg" => "Credenciales invalidas, vuelva a intentarlo."];
        }
        return $r;
      }

     
}
