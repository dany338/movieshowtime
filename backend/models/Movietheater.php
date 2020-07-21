<?php

namespace backend\models;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "movietheater".
 *
 * @property int $id
 * @property string $name Name
 * @property string $location Location
 * @property int $status Status
 * @property int $user_id User Created at/Updated at
 * @property string $created_at Created at
 * @property string $updated_at Updated at
 *
 * @property Moviebillboard[] $moviebillboards
 */
class Movietheater extends \yii\db\ActiveRecord
{
  const INACTIVE                = 0;
  const ACTIVE                  = 1;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'movietheater';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'location', 'status', 'user_id'], 'required'],
            [['status', 'user_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'location'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'         => Yii::t('yii', 'ID'),
            'name'       => Yii::t('yii', 'Name'),
            'location'   => Yii::t('yii', 'Location'),
            'status'     => Yii::t('yii', 'Status'),
            'user_id'    => Yii::t('yii', 'User Created at/Updated at'),
            'created_at' => Yii::t('yii', 'Created at'),
            'updated_at' => Yii::t('yii', 'Updated at'),
        ];
    }

    /**
     * Gets query for [[Moviebillboards]].
     *
     * @return \yii\db\ActiveQuery|MoviebillboardQuery
     */
    public function getMoviebillboards()
    {
        return $this->hasMany(Moviebillboard::className(), ['movietheater_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return MovietheaterQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new MovietheaterQuery(get_called_class());
    }

    public function getStatus()
    {
      switch ($this->status) {
        case Movietheater::INACTIVE:
          $estado = '<div class="chip red darken-1 white-text" style="font-size: smaller;">Inactive</div>';
        break;
        case Movietheater::ACTIVE:
          $estado = '<div class="chip green darken-1 white-text" style="font-size: smaller;">Active</div>';
        break;
        default:
          $estado = '<div class="chip green darken-1 white-text" style="font-size: smaller;">Active</div>';
        break;
      }
      return $estado;
    }

    public function getLabelStatus()
    {
      switch ($this->status) {
        case Movietheater::INACTIVE:
          $estado = 'Inactive';
        break;
        case Movietheater::ACTIVE:
          $estado = 'Active';
        break;
        default:
          $estado = 'Active';
        break;
      }
      return $estado;
    }

    public static function getStatusMovies()
    {
      return [
        0 => 'Inactive',
        1 => 'Active',
      ];
    }

    public function getColorRow()
    {
      $class = '';
      switch ($this->status) {
        case Movietheater::Inactive:
          $class = 'red lighten-4';
        break;
        default:
          $class = '';
        break;
      }
      return $class;
    }

    public static function getMovietheaters()
    {
      $movietheaters = Moviebillboard::find()->where('status = 1')->all();
      return ArrayHelper::map($movietheaters, 'id', function($model, $defaultValue) {
        return '->. '. $model->name . ' location: (' . $model->location . ')';
      });
    }
}
