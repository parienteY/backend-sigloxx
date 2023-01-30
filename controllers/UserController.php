<?php

namespace app\controllers;

use app\models\AuthItem;
use app\models\User;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\web\BadRequestHttpException;
use yii\base\ExitException;
use yii\filters\VerbFilter;
use yii\web\ServerErrorHttpException;

class UserController extends \yii\web\Controller
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
            "cuanta" => ["get"],
            "lista" => ["get"],
            "registro" => ["post"]
          ],
        ];
        return $behaviors;
      }
      protected function getPermisos($id = null) {
        $array = Yii::$app->authManager->getPermissionsByUser($id);
        $permisos = null;
        foreach ($array as $p) {
          if (!str_contains($p->name, '/')) {
            $permisos[] = $p->name;
          }
        }
        return $permisos;
      }
  
      protected function getRoles($id = null) {
        $roles = null;
        $roleArray = Yii::$app->authManager->getRolesByUser($id);
        foreach ($roleArray as $role) {
          $rl = AuthItem::find()->where(['name' => $role->name])->select(['name'])->one();
          $roles[] = $rl;
        }
        return $roles;
      }
  
      public function actionCuenta() {
        $r = null;
        $user = Yii::$app->user->identity;
        $user = User::findOne($user->id);
        $roles = $this->getRoles($user->id);
        $permisos = $this->getPermisos($user->id);
        return [
          "nombres" => $user->nombres,
          "apellidos" => $user->apellidos,
          "email" => $user->email,
          "picture" => $user->picture,
          "roles" => $roles,
          "permisos" => $permisos,
        ];
      }

      public function actionListar(){
        return User::find()
        ->all();
        
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
        $usuarioNuevo->tag_rol = $rol === "admin" ? "ADM" : "SCRE";
        $rolAsignado = false;
        if ($usuarioNuevo->save()) {
          switch ($rol) {
            case 'admin':
              $rolAsignado = $this->asignarRol($usuarioNuevo->id, "ADM");
              break;
            case 'secretaria':
              $rolAsignado = $this->asignarRol($usuarioNuevo->id, "SCRE");
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
          $usuarioNuevo->delete();
          throw new ServerErrorHttpException("No se pudo registrar al usuario");
        }
      }
      public function actionRegistro() {
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
}
