<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "archivo_privado".
 *
 * @property int $id
 * @property int $id_directorio
 * @property string|null $nombre
 * @property string $direccion
 * @property string $extension
 * @property string|null $fecha_creacion
 * @property string|null $fecha_actualizacion
 *
 * @property Directorio $directorio
 * @property Tiene[] $tienes
 */
class ArchivoPrivado extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'archivo_privado';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_directorio', 'direccion', 'extension'], 'required'],
            [['id_directorio'], 'default', 'value' => null],
            [['id_directorio'], 'integer'],
            [['nombre', 'direccion', 'extension'], 'string'],
            [['fecha_creacion', 'fecha_actualizacion'], 'safe'],
            [['id_directorio'], 'exist', 'skipOnError' => true, 'targetClass' => Directorio::class, 'targetAttribute' => ['id_directorio' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_directorio' => 'Id Directorio',
            'nombre' => 'Nombre',
            'direccion' => 'Direccion',
            'extension' => 'Extension',
            'fecha_creacion' => 'Fecha Creacion',
            'fecha_actualizacion' => 'Fecha Actualizacion',
        ];
    }

    /**
     * Gets query for [[Directorio]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDirectorio()
    {
        return $this->hasOne(Directorio::class, ['id' => 'id_directorio']);
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
}
