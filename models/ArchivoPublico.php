<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "archivo_publico".
 *
 * @property int $id
 * @property int|null $id_unidad
 * @property string $direccion
 * @property string $nombre
 * @property string $extension
 * @property string $fecha_creacion
 * @property string $fecha_actualizacion
 * @property string|null $type
 * @property bool|null $visible
 *
 * @property Tiene[] $tienes
 * @property Unidad $unidad
 */
class ArchivoPublico extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'archivo_publico';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_unidad'], 'default', 'value' => null],
            [['id_unidad'], 'integer'],
            [['direccion', 'nombre', 'extension', 'fecha_creacion', 'fecha_actualizacion'], 'required'],
            [['direccion', 'nombre', 'extension', 'type'], 'string'],
            [['fecha_creacion', 'fecha_actualizacion'], 'safe'],
            [['visible'], 'boolean'],
            [['id_unidad'], 'exist', 'skipOnError' => true, 'targetClass' => Unidad::class, 'targetAttribute' => ['id_unidad' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_unidad' => 'Id Unidad',
            'direccion' => 'Direccion',
            'nombre' => 'Nombre',
            'extension' => 'Extension',
            'fecha_creacion' => 'Fecha Creacion',
            'fecha_actualizacion' => 'Fecha Actualizacion',
            'type' => 'Type',
            'visible' => 'Visible',
        ];
    }

    /**
     * Gets query for [[Tienes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTienes()
    {
        return $this->hasMany(Tiene::class, ['id_archivo' => 'id']);
    }

    /**
     * Gets query for [[Unidad]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUnidad()
    {
        return $this->hasOne(Unidad::class, ['id' => 'id_unidad']);
    }
}
