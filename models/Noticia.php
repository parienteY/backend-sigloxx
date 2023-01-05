<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "noticia".
 *
 * @property int $id
 * @property string $titulo
 * @property string|null $subtitulo
 * @property string $foto
 * @property string|null $archivos_adjuntos
 * @property int $id_unidad
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
            [['id', 'titulo', 'foto', 'id_unidad'], 'required'],
            [['id', 'id_unidad'], 'default', 'value' => null],
            [['id', 'id_unidad'], 'integer'],
            [['titulo', 'subtitulo', 'foto'], 'string'],
            [['archivos_adjuntos'], 'safe'],
            [['id'], 'unique'],
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
            'titulo' => 'Titulo',
            'subtitulo' => 'Subtitulo',
            'foto' => 'Foto',
            'archivos_adjuntos' => 'Archivos Adjuntos',
            'id_unidad' => 'Id Unidad',
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
