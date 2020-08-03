<?php

namespace backend\models;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "movie".
 *
 * @property int $id
 * @property int $moviedb_id Movie ID
 * @property string $moviedb_image Movie Image
 * @property string $name Name
 * @property int $status Status
 * @property int $user_first_id User first subscription
 * @property string $created_at Created at
 * @property string $updated_at Updated at
 *
 * @property Moviebillboard[] $moviebillboards
 * @property Review[] $reviews
 * @property Subscription[] $subscriptions
 */
class Movie extends \yii\db\ActiveRecord
{
  const INACTIVE                = 0;
  const ACTIVE                  = 1;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'movie';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['moviedb_id', 'moviedb_image', 'name', 'status', 'user_first_id'], 'required'],
            [['moviedb_id', 'status', 'user_first_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['moviedb_image', 'name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'            => Yii::t('yii', 'ID'),
            'moviedb_id'    => Yii::t('yii', 'Movie ID'),
            'moviedb_image' => Yii::t('yii', 'Movie Image'),
            'name'          => Yii::t('yii', 'Name'),
            'status'        => Yii::t('yii', 'Status'),
            'user_first_id' => Yii::t('yii', 'User first subscription'),
            'created_at'    => Yii::t('yii', 'Created at'),
            'updated_at'    => Yii::t('yii', 'Updated at'),
        ];
    }

    /**
     * Gets query for [[Moviebillboards]].
     *
     * @return \yii\db\ActiveQuery|MoviebillboardQuery
     */
    public function getMoviebillboards()
    {
        return $this->hasMany(Moviebillboard::className(), ['movie_id' => 'id']);
    }

    /**
     * Gets query for [[Subscriptions]].
     *
     * @return \yii\db\ActiveQuery|SubscriptionQuery
     */
    public function getSubscriptions()
    {
        return $this->hasMany(Subscription::className(), ['movie_id' => 'id']);
    }

    /**
    * Gets query for [[Reviews]].
    *
    * @return \yii\db\ActiveQuery|ReviewQuery
    */
    public function getReviews()
    {
      return $this->hasMany(Review::className(), ['movie_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return MovieQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new MovieQuery(get_called_class());
    }

    public function getStatus()
    {
      switch ($this->status) {
        case Movie::INACTIVE:
          $estado = '<div class="chip red darken-1 white-text" style="font-size: smaller;">Inactive</div>';
        break;
        case Movie::ACTIVE:
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
        case Movie::INACTIVE:
          $estado = 'Inactive';
        break;
        case Movie::ACTIVE:
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
      switch ($this->estado) {
        case Movie::Inactive:
          $class = 'red lighten-4';
        break;
        default:
          $class = '';
        break;
      }
      return $class;
    }

    public static function getMovies()
    {
      $movies = Movie::find()->where('status = 1')->all();
      return ArrayHelper::map($movies, 'id', function($model, $defaultValue) {
        return '-> '.$model->name . ' # ' . $model->moviedb_id;
      });
    }

    public static function getSqlExport($year)
    {
      $condition = (!empty($anio)) ? ' WHERE YEAR(DATE_FORMAT(a.created_at, "%Y-%m-%d")) =:year ' : '';
      $sql = 'SELECT a.id AS "ID",
                    a.name AS "TITLE",
                    DATE_FORMAT(a.created_at, "%Y-%m-%d") AS "CREATE AT",
                    DATE_FORMAT(a.updated_at, "%Y-%m-%d") AS "UPDATE AT",
                    CASE
                      WHEN a.status = 0 THEN "INACTIVE"
                      WHEN a.status = 1 THEN "ACTIVE"
                    END AS STATUS
                FROM movie as a
          '.$condition.'
            ORDER BY a.id DESC';

      return $sql;
    }
}
