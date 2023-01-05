<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tag".
 *
 * @property string $nombre
 *
 * @property Tiene[] $tienes
 */
class Tag extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tag';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre'], 'required'],
            [['nombre'], 'string'],
            [['nombre'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'nombre' => 'Nombre',
        ];
    }

    /**
     * Gets query for [[Tienes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTienes()
    {
        return $this->hasMany(Tiene::class, ['nombre' => 'nombre']);
    }
}
