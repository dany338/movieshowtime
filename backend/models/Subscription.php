<?php

namespace backend\models;

use Yii;
use yii\helpers\ArrayHelper;
use dektrium\user\models\User;
/**
 * This is the model class for table "subscription".
 *
 * @property int $id
 * @property int $movie_id
 * @property int $uid
 * @property int $notification Notification
 * @property int $status Status
 * @property string $created_at Created at
 * @property string $updated_at Updated at
 *
 * @property Movie $movie
 */
class Subscription extends \yii\db\ActiveRecord
{
  const INACTIVE                = 0;
  const ACTIVE                  = 1;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'subscription';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['movie_id', 'uid', 'notification', 'status'], 'required'],
            [['movie_id', 'uid', 'notification', 'status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['movie_id'], 'exist', 'skipOnError' => true, 'targetClass' => Movie::className(), 'targetAttribute' => ['movie_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'           => Yii::t('yii', 'ID'),
            'movie_id'     => Yii::t('yii', 'Movie ID'),
            'uid'          => Yii::t('yii', 'Uid'),
            'notification' => Yii::t('yii', 'Notification'),
            'status'       => Yii::t('yii', 'Status'),
            'created_at'   => Yii::t('yii', 'Created at'),
            'updated_at'   => Yii::t('yii', 'Updated at'),
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
     * {@inheritdoc}
     * @return SubscriptionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new SubscriptionQuery(get_called_class());
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

    public static function getStatusSubscriptions()
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
        case Subscription::Inactive:
          $class = 'red lighten-4';
        break;
        default:
          $class = '';
        break;
      }
      return $class;
    }

    public static function getSubscriptions()
    {
      $subscriptions = Subscription::find()->where('status = 1')->all();
      return ArrayHelper::map($subscriptions, 'id', function($model, $defaultValue) {
        $user = User::findOne($model->uid);
        return '-> Movie: '.$model->movie->name.' User: '.$user->profile->name . ' Email: (' . $user->profile->public_email . ') Location: (' . $user->profile->location . ')';
      });
    }

    public static function getSql($movie = '')
    {
      $condition  = (!empty($movie)) ? ' WHERE a.status = 1 and a.movie_id = b.id ' : '';
      $sql = ' SELECT a.id,
                      a.notification,
                      DATE_FORMAT(FROM_UNIXTIME(a.created_at), "%Y-%m-%d %H:%i %p) as created_at,
                      CASE
                        WHEN a.status = 0 THEN "INACTIVE"
                        WHEN a.status = 1 THEN "ACTIVE"
                      END AS statusLabel,
                      b.name
                 FROM subscription as a, movie as b,
                '.$condition.'
                ORDER BY a.id ASC';

      return $sql;
    }
}
