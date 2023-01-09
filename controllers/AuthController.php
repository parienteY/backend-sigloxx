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

      protected function RegistroUsuario($body, $rol) {
        $time = date("Y-m-d H:i:s");
        $code = Yii::$app->getSecurity()->generatePasswordHash($time);
        $data = [
          "username" => $body["email"],
          "email" => $body["email"],
          "nombres" => ucwords(strtolower($body["nombres"])),
          "apellidos" => ucwords(strtolower($body["apellidos"])),
        //   "picture" => $body["picture"],
          "access_token" => $code,
          "created_at" => $time,
          "updated_at" => $time,
          "status" => 10,
        ];
        if (isset($body['password'])) {
          $pwd = Yii::$app->getSecurity()->generatePasswordHash($body['password']);
          $data['password_hash'] = $pwd;
        }
  
        $usuarioNuevo = new User($data);
        $usuarioNuevo->tag_rol = $rol === "admin" ? "ADMIN" : "SECRETARIA";
        $rolAsignado = false;
        if ($usuarioNuevo->save()) {
          switch ($rol) {
            case 'admin':
              $rolAsignado = $this->asignarRol($usuarioNuevo->id, "ADMIN");
              break;
            case 'secretaria':
              $rolAsignado = $this->asignarRol($usuarioNuevo->id, "SECRETARIA");
              break;
            default:
              break;
          }
          if ($rolAsignado === true) {
            return $usuarioNuevo;
          } else {
            return null;
          }
        } else {
          throw new ServerErrorHttpException("No se pudo registrar al usuario");
        }
      }

      public function actionRegister() {
        $r = null;
        $params = Yii::$app->getRequest()->getBodyParams();
        $user = User::findOne(['email' => $params['email']]);
        if (!$user) {
          $userCreated = $this->RegistroUsuario($params, $params['rol']);
          if ($userCreated) {
            $r = ["status" => true, "msg" => "Registro existoso",];
          } else {
            throw new ServerErrorHttpException("No se pudo registrar, intente de nuevo");
          }
        } else {
          throw new ServerErrorHttpException("El correo electronico ya esta en uso, use una diferente");
        }
        return $r;
      }

      protected function asignarRol($id, $rols) {
        $auth = Yii::$app->authManager;
        $rol = $auth->getRoles();
        if ($auth->assign($rol[$rols], $id)) {
          return true;
        } else {
          return false;
        }
      }
}
