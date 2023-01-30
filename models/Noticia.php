<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "noticia".
 *
 * @property string $titulo
 * @property string|null $subtitulo
 * @property string $foto
 * @property string|null $archivos_adjuntos
 * @property int $id_unidad
 * @property int $id
 * @property string|null $fecha_creacion
 * @property string|null $fecha_actualizacion
 *
 * @property Unidad $unidad
 */
class Noticia extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'noticia';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['titulo', 'foto', 'id_unidad'], 'required'],
            [['titulo', 'subtitulo', 'foto'], 'string'],
            [['archivos_adjuntos', 'fecha_creacion', 'fecha_actualizacion'], 'safe'],
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
            'titulo' => 'Titulo',
            'subtitulo' => 'Subtitulo',
            'foto' => 'Foto',
            'archivos_adjuntos' => 'Archivos Adjuntos',
            'id_unidad' => 'Id Unidad',
            'id' => 'ID',
            'fecha_creacion' => 'Fecha Creacion',
            'fecha_actualizacion' => 'Fecha Actualizacion',
        ];
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
