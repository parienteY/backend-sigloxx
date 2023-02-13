<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "unidad".
 *
 * @property string $nombre
 * @property int $id
 * @property string|null $descripcion
 * @property string|null $direccion
 * @property string|null $telefonos
 * @property string|null $coordenadas
 *
 * @property ArchivoPublico[] $archivoPublicos
 * @property Directorio[] $directorios
 * @property Noticia[] $noticias
 * @property User[] $users
 */
class Unidad extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'unidad';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre'], 'required'],
            [['nombre', 'descripcion', 'direccion'], 'string'],
            [['telefonos', 'coordenadas'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'nombre' => 'Nombre',
            'id' => 'ID',
            'descripcion' => 'Descripcion',
            'direccion' => 'Direccion',
            'telefonos' => 'Telefonos',
            'coordenadas' => 'Coordenadas',
        ];
    }

    /**
     * Gets query for [[ArchivoPublicos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getArchivoPublicos()
    {
        return $this->hasMany(ArchivoPublico::class, ['id_unidad' => 'id']);
    }

    /**
     * Gets query for [[Directorios]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDirectorios()
    {
        return $this->hasMany(Directorio::class, ['id_unidad' => 'id']);
    }

    /**
     * Gets query for [[Noticias]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNoticias()
    {
        return $this->hasMany(Noticia::class, ['id_unidad' => 'id']);
    }

    /**
     * Gets query for [[Users]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['id_unidad' => 'id']);
    }
}
