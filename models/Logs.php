<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "logs".
 *
 * @property int $id
 * @property string $tipo_accion
 * @property string $data
 * @property string $fecha
 * @property string $data_user
 * @property string $tipo_item
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
            [['id', 'tipo_accion', 'data', 'fecha', 'data_user', 'tipo_item'], 'required'],
            [['id'], 'default', 'value' => null],
            [['id'], 'integer'],
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
            'id' => 'ID',
            'tipo_accion' => 'Tipo Accion',
            'data' => 'Data',
            'fecha' => 'Fecha',
            'data_user' => 'Data User',
            'tipo_item' => 'Tipo Item',
        ];
    }
}
