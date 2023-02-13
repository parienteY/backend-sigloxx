<?php

  namespace app\controllers;

  use app\models\Beneficio;
  use app\models\Log;
use app\models\Logs;
use app\models\User;
  use Exception;
  use yii\console\Controller;
  use Yii;


  class UtilController extends Controller {
   

    static function generatedLog($data, $data_type, $action_type) {
      $userModel = [];
      $user = Yii::$app->user->identity;
      $roles = Yii::$app->authManager->getRolesByUser($user->id);
      $userModel["id"] = $user->id;
      $userModel["nombres"] = $user->nombres;
      $userModel["apellidos"] = $user->apellidos;
      $userModel["email"] = $user->email;
      $userModel['roles'] = $roles;
      $userModel['ci'] = $user->ci;
      $userModel['sesion_status'] = $user->sesion_status;
      $log = new Logs();
      $log->data_user = $userModel;
      $log->data = $data;
      $log->fecha = date("Y-m-d H:i:s");
      $log->tipo_item = $data_type;
      $log->tipo_accion = $action_type;

      if ($log->save()) {
        return $log;
      }else{
        return $log->getErrors();
      }
    }
  }
