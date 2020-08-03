<?php

use macgyer\yii2materializecss\lib\Html;
use yii\widgets\DetailView;
use backend\models\Subscription;
use yii\web\View;

$user                 = User::findOne($model->uid);
$user_name            = $user->profile->name;
$created_at           = date ( 'd M, Y g:i A' , $model->created_at );
$updated_at           = date ( 'd M, Y g:i A' , $model->updated_at );

$script = <<< JS
jQuery('.tooltipped').tooltip({delay: 50, html: true, position: 'top'});
JS;
$this->registerJs($script, View::POS_READY, 'script-detalle');

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('yii', 'Notifications List'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
  <div col="col s12">
    <h3>Subscription ID # <?=$model->id?></h3>
    <h6>Created at: <small><?=$created_at;?></small> Updated at: <small><?=$updated_at;?></small> User: <small><?=$user_name?></small></h3>
  </div>
  <div class="col s12">
      <div class="img-thumbnail img-rounded text-center">
          <?= Html::img('@logo', ['title' => Yii::t('yii', 'Movie Show Time Finder'), 'width'=>'100%', 'height'=>'200', 'style' => 'padding:2px;']); ?>
          <div class="small text-muted" style="text-decoration: underline;"><b>Created at: <?=$created_at; ?></b></div>
      </div>
  </div>
  <div class="col s12">
      <table class="kv-grid-table table table-hover table-bordered table-striped table-condensed kv-table-wrap">
        <tbody>
          <tr class="danger">
              <th colspan="3" class="text-center text-danger">Subscription</th>
          </tr>
          <tr class="active">
              <th class="text-center">Item</th>
              <th>Detail</th>
          </tr>
          <tr>
              <td class="text-center">Movie Subscription</td>
              <td class="kv-nowrap" style="text-align: justify; white-space: normal !important;">
                <?=$model->subscription->movie->name;?>
              </td>
          </tr>
          <tr>
              <td class="text-center">Notification</td>
              <td class="kv-nowrap" style="text-align: justify; white-space: normal !important;">
                <?=$model->notification;?>
              </td>
          </tr>
          <tr>
              <td class="text-center">Status</td>
              <td class="kv-nowrap" style="text-align: justify; white-space: normal !important;">
                <?=$model->getStatus();?>
              </td>
          </tr>
      </tbody>
    </table>
  </div>
</div>
