<?php

namespace backend\models;

use Yii;
use yii\helpers\ArrayHelper;
use dektrium\user\models\User;
/**
 * This is the model class for table "moviebillboard".
 *
 * @property int $id
 * @property int $movietheater_id Movie Theater
 * @property int $movie_id
 * @property string $start_date Start Date
 * @property string $end_date End Date
 * @property int $status Status
 * @property int $user_id User Created at/Updated at
 * @property string $created_at Created at
 * @property string $updated_at Updated at
 *
 * @property Movie $movie
 * @property Movietheater $movietheater
 */
class Moviebillboard extends \yii\db\ActiveRecord
{
  const INACTIVE                = 0;
  const ACTIVE                  = 1;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'moviebillboard';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['movietheater_id', 'movie_id', 'start_date', 'end_date', 'status', 'user_id'], 'required'],
            [['movietheater_id', 'movie_id', 'status', 'user_id'], 'integer'],
            [['start_date', 'end_date', 'created_at', 'updated_at'], 'safe'],
            [['movie_id'], 'exist', 'skipOnError' => true, 'targetClass' => Movie::className(), 'targetAttribute' => ['movie_id' => 'id']],
            [['movietheater_id'], 'exist', 'skipOnError' => true, 'targetClass' => Movietheater::className(), 'targetAttribute' => ['movietheater_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'              => Yii::t('yii', 'ID'),
            'movietheater_id' => Yii::t('yii', 'Movie Theater'),
            'movie_id'        => Yii::t('yii', 'Movie ID'),
            'start_date'      => Yii::t('yii', 'Start Date'),
            'end_date'        => Yii::t('yii', 'End Date'),
            'status'          => Yii::t('yii', 'Status'),
            'user_id'         => Yii::t('yii', 'User Created at/Updated at'),
            'created_at'      => Yii::t('yii', 'Created at'),
            'updated_at'      => Yii::t('yii', 'Updated at'),
        ];
    }

    /**
     * Gets query for [[Movie]].
     *
     * @return \yii\db\ActiveQuery|MovieQuery
     */
    public function getMovie()
    {
        return $this->hasOne(Movie::className(), ['id' => 'movie_id']);
    }

    /**
     * Gets query for [[Movietheater]].
     *
     * @return \yii\db\ActiveQuery|MovietheaterQuery
     */
    public function getMovietheater()
    {
        return $this->hasOne(Movietheater::className(), ['id' => 'movietheater_id']);
    }

    /**
     * {@inheritdoc}
     * @return MoviebillboardQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new MoviebillboardQuery(get_called_class());
    }

    public function getStatus()
    {
      switch ($this->status) {
        case Moviebillboard::INACTIVE:
          $estado = '<div class="chip red darken-1 white-text" style="font-size: smaller;">Inactive</div>';
        break;
        case Moviebillboard::ACTIVE:
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
        case Moviebillboard::INACTIVE:
          $estado = 'Inactive';
        break;
        case Moviebillboard::ACTIVE:
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

    public function getColorFila()
    {
      $class = '';
      switch ($this->status) {
        case Moviebillboard::Inactive:
          $class = 'red lighten-4';
        break;
        default:
          $class = '';
        break;
      }
      return $class;
    }

    public static function getMoviebillboards()
    {
      $moviebillboards = Moviebillboard::find()->where('status = 1')->all();
      return ArrayHelper::map($moviebillboards, 'id', function($model, $defaultValue) {
        return '->. '.$model->movietheater->name . ' start: (' . date ( 'd M, Y g:i A' , strtotime($model->start_date)) . ') end: (' . date ( 'd M, Y g:i A' , strtotime($model->end_date)) . ')';
      });
    }

    public static function getSql($movie = '')
    {
      $condition  = (!empty($movie)) ? ' WHERE a.movie_id =:movie and a.movie_id = b.id and a.movietheater_id = c.id ' : '';
      $sql = ' SELECT a.id,
                      b.name as movie,
                      b.moviedb_image as image,
                      c.name as theater,
                      DATE_FORMAT(a.start_date, "%Y-%m-%d %H:%i %p") as start_date,
                      DATE_FORMAT(a.end_date, "%Y-%m-%d %H:%i %p") as end_date,
                      CASE
                        WHEN a.status = 0 THEN "INACTIVE"
                        WHEN a.status = 1 THEN "ACTIVE"
                      END AS statusLabel
                 FROM moviebillboard as a, movie as b, movietheater as c
                '.$condition.'
                ORDER BY a.id DESC';

      return $sql;
    }

    public static function getSqlMonths($month, $year)
    {
      $condition  = (!empty($month)) ? ' WHERE a.status = 1 and DATE_FORMAT(a.start_date, "%Y") =:year and DATE_FORMAT(a.start_date, "%m") =:month and a.movie_id = b.id and a.movietheater_id = c.id ' : '';
      $sql = ' SELECT a.id,
                      b.name as movie,
                      b.moviedb_image as image,
                      c.name as theater,
                      DATE_FORMAT(a.start_date, "%Y-%m-%d %H:%i %p") as start_date,
                      DATE_FORMAT(a.end_date, "%Y-%m-%d %H:%i %p") as end_date,
                      CASE
                        WHEN a.status = 0 THEN "INACTIVE"
                        WHEN a.status = 1 THEN "ACTIVE"
                      END AS statusLabel
                 FROM moviebillboard as a, movie as b, movietheater as c
                '.$condition.'
                ORDER BY a.id DESC';

      return $sql;
    }

    public function setSendNotificationsSubscribers()
    {
      foreach ($this->movie->subscriptions as $index => $subscription):
        if($subscription->status == 1) {
          $notification = Notification::find()->where(['moviebillboard_id'=> $this->id])->one();
          if($notification === null) {
            $user    = User::findOne($subscription->uid);
            $mensaje = $user->mailer->sendMoviebillboardMessage($user, $this->movie, $this, $subscription);
            if($mensaje) {
              $subscription->notification = (int)$subscription->notification + 1;
              $subscription->save(false);
              $notification = new Notification();
              $notification->subscription_id = $subscription->id;
              $notification->uid             = $user->id;
              $notification->description     = 'Send billboards'.' # '.$subscription->notification;
              $notification->status = 1;
              $notification->created_at      = date('Y-m-d H:i:s');
              $notification->updated_at      = date('Y-m-d H:i:s');
              $notification->moviebillboard_id = $this->id;
              $notification->save(false);
            }
          }
        }
      endforeach;

    }

    public static function getSqlExport($year)
    {
      $condition  = (!empty($anio)) ? ' WHERE YEAR(DATE_FORMAT(a.created_at, "%Y-%m-%d")) =:year ' : '';
      $condition .= (!empty($condition)) ? ' AND a.movie_id = b.id AND a.movietheater_id = c.id ' : ' WHERE a.movie_id = b.id AND a.movietheater_id = c.id ';
      $sql = 'SELECT a.id AS "ID",
                    b.name AS "TITLE",
                    c.name AS "THEATER",
                    DATE_FORMAT(a.created_at, "%Y-%m-%d") AS "CREATE AT",
                    DATE_FORMAT(a.updated_at, "%Y-%m-%d") AS "UPDATE AT",
                    CASE
                      WHEN a.status = 0 THEN "INACTIVE"
                      WHEN a.status = 1 THEN "ACTIVE"
                    END AS STATUS
                FROM moviebillboard as a, movie as b, movietheater as c
          '.$condition.'
            ORDER BY a.id DESC';
      return $sql;
    }
}
