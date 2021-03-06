<?php

use macgyer\yii2materializecss\lib\Html;
use yii\widgets\DetailView;
use backend\models\Movietheater;
use yii\web\View;

$user                 = User::findOne($model->user_id);
$user_name            = $user->profile->name;
$created_at           = date ( 'd M, Y g:i A' , $model->created_at );
$updated_at           = date ( 'd M, Y g:i A' , $model->updated_at );
$countMoviebillboards = count($model->moviebillboards);

$script = <<< JS
jQuery('.tooltipped').tooltip({delay: 50, html: true, position: 'top'});
JS;
$this->registerJs($script, View::POS_READY, 'script-detalle');

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('yii', 'Movie Theaters List'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
  <div col="col s12">
    <h3>Movie Theater ID # <?=$model->id?></h3>
    <h6>Created at: <small><?=$created_at;?></small> Updated at: <small><?=$updated_at;?></small> Funcionario Ultima Módificación: <small><?=$user_name?></small></h3>
  </div>
  <?php if($countMoviebillboards > 0) { ?>
    <div col="col s12">
      <a class="btn waves-effect waves-light yellow darken-4 white-text tooltipped modal-trigger moviebillboards"
        style="margin-left: 15px;"
        data-theater="<?=$model->id?>"
        href="#modal-moviebillboards"
        data-position="top"
        data-delay="50"
        data-tooltip="<p style='text-align:justify;''>Views moviebillboards <span class='red-text text-lighten-2'><b>Movie Show Time Finder</b></p>"
      >
        Views moviebillboards
      </a>
    </div>
  <?php } ?>
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
              <th colspan="3" class="text-center text-danger">Movie</th>
          </tr>
          <tr class="active">
              <th class="text-center">Item</th>
              <th>Detail</th>
          </tr>
          <tr>
              <td class="text-center">Name</td>
              <td class="kv-nowrap" style="text-align: justify; white-space: normal !important;">
                <?=$model->name;?>
              </td>
          </tr>
          <tr>
              <td class="text-center">Location</td>
              <td class="kv-nowrap" style="text-align: justify; white-space: normal !important;">
                <?=$model->location;?>
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
