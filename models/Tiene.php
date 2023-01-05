<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tiene".
 *
 * @property string $nombre
 * @property int $id_archivo
 * @property int $id_unidad
 *
 * @property ArchivoPrivado $archivo
 * @property ArchivoPublico $archivo0
 * @property Tag $nombre0
 * @property Unidad $unidad
 */
class Tiene extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tiene';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre', 'id_archivo', 'id_unidad'], 'required'],
            [['nombre'], 'string'],
            [['id_archivo', 'id_unidad'], 'default', 'value' => null],
            [['id_archivo', 'id_unidad'], 'integer'],
            [['id_archivo'], 'exist', 'skipOnError' => true, 'targetClass' => ArchivoPrivado::class, 'targetAttribute' => ['id_archivo' => 'id']],
            [['id_archivo'], 'exist', 'skipOnError' => true, 'targetClass' => ArchivoPublico::class, 'targetAttribute' => ['id_archivo' => 'id']],
            [['nombre'], 'exist', 'skipOnError' => true, 'targetClass' => Tag::class, 'targetAttribute' => ['nombre' => 'nombre']],
            [['id_unidad'], 'exist', 'skipOnError' => true, 'targetClass' => Unidad::class, 'targetAttribute' => ['id_unidad' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'nombre' => 'Nombre',
            'id_archivo' => 'Id Archivo',
            'id_unidad' => 'Id Unidad',
        ];
    }

    /**
     * Gets query for [[Archivo]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getArchivo()
    {
        return $this->hasOne(ArchivoPrivado::class, ['id' => 'id_archivo']);
    }

    /**
     * Gets query for [[Archivo0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getArchivo0()
    {
        return $this->hasOne(ArchivoPublico::class, ['id' => 'id_archivo']);
    }

    /**
     * Gets query for [[Nombre0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNombre0()
    {
        return $this->hasOne(Tag::class, ['nombre' => 'nombre']);
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
