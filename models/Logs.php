<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "logs".
 *
 * @property string $tipo_accion
 * @property string $data
 * @property string $fecha
 * @property string $data_user
 * @property string $tipo_item
 * @property int $id
 */
class Logs extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'logs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tipo_accion', 'data', 'fecha', 'data_user', 'tipo_item'], 'required'],
            [['tipo_accion', 'tipo_item'], 'string'],
            [['data', 'fecha', 'data_user'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'tipo_accion' => 'Tipo Accion',
            'data' => 'Data',
            'fecha' => 'Fecha',
            'data_user' => 'Data User',
            'tipo_item' => 'Tipo Item',
            'id' => 'ID',
        ];
    }
}
