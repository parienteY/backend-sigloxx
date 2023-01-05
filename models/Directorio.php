<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "directorio".
 *
 * @property int $id
 * @property string $nombre
 * @property string $fecha_creacion
 * @property string $fecha_actualizacion
 * @property string|null $descripcion
 * @property int $id_unidad
 *
 * @property ArchivoPrivado[] $archivoPrivados
 * @property ArchivoPublico[] $archivoPublicos
 * @property Unidad $unidad
 */
class Directorio extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'directorio';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre', 'fecha_creacion', 'fecha_actualizacion', 'id_unidad'], 'required'],
            [['nombre', 'descripcion'], 'string'],
            [['fecha_creacion', 'fecha_actualizacion'], 'safe'],
            [['id_unidad'], 'default', 'value' => null],
            [['id_unidad'], 'integer'],
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
            'nombre' => 'Nombre',
            'fecha_creacion' => 'Fecha Creacion',
            'fecha_actualizacion' => 'Fecha Actualizacion',
            'descripcion' => 'Descripcion',
            'id_unidad' => 'Id Unidad',
        ];
    }

    /**
     * Gets query for [[ArchivoPrivados]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getArchivoPrivados()
    {
        return $this->hasMany(ArchivoPrivado::class, ['id_directorio' => 'id']);
    }

    /**
     * Gets query for [[ArchivoPublicos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getArchivoPublicos()
    {
        return $this->hasMany(ArchivoPublico::class, ['id_directorio' => 'id']);
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
