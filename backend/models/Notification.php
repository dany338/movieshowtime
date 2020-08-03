<?php

namespace backend\models;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "notification".
 *
 * @property int $id
 * @property int $subscription_id Subscription
 * @property int $moviebillboard_id Movie billboard
 * @property int $uid User
 * @property string $description Description
 * @property int $status Status
 * @property string $created_at Created at
 * @property string $updated_at Updated at
 *
 * @property Subscription $subscription
 */
class Notification extends \yii\db\ActiveRecord
{
  const INACTIVE                = 0;
  const ACTIVE                  = 1;
  const DISPLAYED               = 2;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'notification';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['subscription_id', 'uid', 'description', 'status'], 'required'],
            [['subscription_id', 'moviebillboard_id', 'uid', 'status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['description'], 'string', 'max' => 255],
            [['subscription_id'], 'exist', 'skipOnError' => true, 'targetClass' => Subscription::className(), 'targetAttribute' => ['subscription_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'              => Yii::t('yii', 'ID'),
            'subscription_id' => Yii::t('yii', 'Subscription'),
            'moviebillboard_id' => Yii::t('yii', 'Movie billboard'),
            'uid'             => Yii::t('yii', 'User'),
            'description'     => Yii::t('yii', 'Description'),
            'status'          => Yii::t('yii', 'Status'),
            'created_at'      => Yii::t('yii', 'Created at'),
            'updated_at'      => Yii::t('yii', 'Updated at'),
        ];
    }

    /**
     * Gets query for [[Subscription]].
     *
     * @return \yii\db\ActiveQuery|SubscriptionQuery
     */
    public function getSubscription()
    {
        return $this->hasOne(Subscription::className(), ['id' => 'subscription_id']);
    }

    /**
     * {@inheritdoc}
     * @return NotificationQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new NotificationQuery(get_called_class());
    }

    public function getStatus()
    {
      switch ($this->status) {
        case Notification::INACTIVE:
          $estado = '<div class="chip red darken-1 white-text" style="font-size: smaller;">Inactive</div>';
        break;
        case Notification::ACTIVE:
          $estado = '<div class="chip green darken-1 white-text" style="font-size: smaller;">Active</div>';
        break;
        case Notification::DISPLAYED:
          $estado = '<div class="chip yellow darken-4 white-text" style="font-size: smaller;">Displayed</div>';
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
        case Notification::INACTIVE:
          $estado = 'Inactive';
        break;
        case Notification::ACTIVE:
          $estado = 'Active';
        break;
        case Notification::DISPLAYED:
          $estado = 'Displayed';
        break;
        default:
          $estado = 'Active';
        break;
      }
      return $estado;
    }

    public static function getStatusNotifications()
    {
      return [
        0 => 'Inactive',
        1 => 'Active',
        2 => 'Displayed',
      ];
    }

    public function getColorRow()
    {
      $class = '';
      switch ($this->estado) {
        case Notification::INACTIVE:
          $class = 'red lighten-4';
        break;
        case Notification::DISPLAYED:
          $class = 'yellow lighten-4';
        break;
        default:
          $class = '';
        break;
      }
      return $class;
    }

    public static function getNotifications()
    {
      $notifications = Notification::find()->where('status = 1')->all();
      return ArrayHelper::map($notifications, 'id', function($model, $defaultValue) {
        $user = User::findOne($model->uid);
        return '-> Movie: '.$model->subscription->movie->name.' User: '.$user->profile->name . ' Email: (' . $user->profile->public_email . ') Location: (' . $user->profile->location . ')';
      });
    }
}
