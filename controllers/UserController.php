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
          Yii::$app->getResponse()->getHeaders()->set('Allow', 'POST GET PUT DELETE');
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
            "listar" => ["get"],
            "registro" => ["post"],
            "actualizar" => ["put"],
            "eliminar" => ["delete"]
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
        $unidad = "";
        if(isset($user->unidad->id)){
          $unidad = $user->unidad->id;
        }
        $roles = $this->getRoles($user->id);
        $permisos = $this->getPermisos($user->id);
        return [
          "nombres" => $user->nombres,
          "apellidos" => $user->apellidos,
          "email" => $user->email,
          "picture" => $user->picture,
          "roles" => $roles,
          "id_unidad" => $unidad,
          "permisos" => $permisos,
        ];
      }

      public function actionListar(){
        $usuarios = User::find()
        ->where(["removed" =>  null])
        ->orderBy(["id" => SORT_DESC])
        ->all();
        $response = [];
        foreach ($usuarios as $u) {
          $response []= [
            "id" => $u->id,
            "email" => $u->email,
            "nombres" => $u->nombres,
            "apellidos" => $u->apellidos,
            "picture" => $u->picture,
            "ci" => $u->ci,
            "created_at" => $u->created_at,
            "tag_rol" => $u->tag_rol,
            "updated_at" => $u->updated_at,
            "unidad" => $u->unidad
          ];
        }
        return $response;
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

      protected function RegistroUsuario($body, $rol = 'administrador') {
        $time = date("Y-m-d H:i:s");
        $code = Yii::$app->getSecurity()->generatePasswordHash($time);
        $data = [
          "username" => $body["email"],
          "email" => $body["email"],
          "nombres" => ucwords(strtolower($body["nombres"])),
          "apellidos" => ucwords(strtolower($body["apellidos"])),
          "access_token" => $code,
          "created_at" => $time,
          "updated_at" => $time,
          "status" => 10,
          "id_unidad" => $body["unidad"]
        ];
        if (isset($body['password'])) {
          $pwd = Yii::$app->getSecurity()->generatePasswordHash($body['password']);
          $data['password_hash'] = $pwd;
        }
        if(isset($body['picture'])){
          $data["picture"] = $body["picture"];
        }
        $usuarioNuevo = new User($data);
        $usuarioNuevo->tag_rol = $rol === "administrador" ? "ADM" : "SCRE";
        $rolAsignado = false;
        if ($usuarioNuevo->save()) {
          switch ($rol) {
            case 'administrador':
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
          $userCreated = $this->RegistroUsuario($params);
          if ($userCreated) {
            UtilController::generatedLog($userCreated, "usuario", "CREAR");
            $r = ["status" => true, "msg" => "Registro existoso",];
          } else {
            throw new ServerErrorHttpException("No se pudo registrar, intente de nuevo");
          }
        } else {
          throw new ServerErrorHttpException("El correo electronico ya esta en uso, use una diferente");
        }
        return $r;
      }

      public function actionActualizar($id = null) {
        $r = null;
        $params = Yii::$app->request->getBodyParams();
        $params['updated_at'] = date("Y-m-d H:i:s");
        $user = User::findOne(Yii::$app->user->identity->id);
  
        if (!is_null($id) ) {
          if ($model = User::findOne(['id' => $id, 'removed' => null])) {
            if ($model->load($params, '') && $model->save()) {
              UtilController::generatedLog(["user" => $user, "params" => $params], "usuario", "ACTUALIZAR");
              $r = ["status" => false, "msg" => "Se actualizo el usuario",];
            } else {
              throw new ServerErrorHttpException("Algo salio mal, vuelva a intentarlo");
            }
          } else {
            throw new ServerErrorHttpException("El usuario no existe");
          }
        } else {
          if ($user && $user->load($params, '') && $user->save()) {
            $r = ["status" => false, "msg" => "Se actualizo la cuenta",];
          } else {
            throw new ServerErrorHttpException("Algo salio mal, vuelva a intentarlo");
          }
        }
        return $r;
      }


      public function actionEliminar($id) {
        $r = null;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->identity->id);
        $removed["removed"] = date("Y-m-d H:i:s");
        if ($model = User::findOne(['id' => $id, 'removed' => null])) {
          $roles2 = Yii::$app->authManager->getRolesByUser($model->id);
            if ($model->load($removed, '') && $model->save()) {
              UtilController::generatedLog([$model], "usuario", "ELIMINAR");
              $r = ["status" => false, "msg" => "Se elimino el usuario",];
            } else {
              throw new ServerErrorHttpException("Hubo un error al tratar de eliminar el usuario");
            }
        } else {
          throw new ServerErrorHttpException("No existe el usuario");
        }
        return $r;
      }

      public function actionFiltro($search = "all", $unidad = "all", $rol = "all"){
        $searchUnidad = [];
        $searchWhere = [];
        $searchRol = [];

        if($search !== "all"){
          $searchWhere = [
            'or',
            ['ilike', 'user.email', $search],
            ['ilike', 'user.nombres', $search],
            ['ilike', 'user.apellidos', $search],
          ];
        }
        if($unidad !== "all"){
          $searchUnidad = ["id_unidad" => $unidad];
        }

        if($rol !== "all"){
          $searchRol = ["tag_rol" => $rol];
        }

        $users = User::find()->where($searchUnidad)->andWhere($searchRol)->andFilterWhere($searchWhere)->orderBy(["id" => SORT_DESC])->all();

        $response = [];
        foreach ($users as $u) {
          $response []= [
            "id" => $u->id,
            "email" => $u->email,
            "nombres" => $u->nombres,
            "apellidos" => $u->apellidos,
            "picture" => $u->picture,
            "ci" => $u->ci,
            "created_at" => $u->created_at,
            "tag_rol" => $u->tag_rol,
            "updated_at" => $u->updated_at,
            "unidad" => $u->unidad
          ];
        }
        return $response;
      }
}
