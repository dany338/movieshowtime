<?php
use macgyer\yii2materializecss\lib\Html;
use macgyer\yii2materializecss\widgets\form\ActiveForm;
use macgyer\yii2materializecss\widgets\form\Select;
//use macgyer\yii2materializecss\widgets\form\DatePicker;
use macgyer\yii2materializecss\widgets\form\TimePicker;
//use yii\bootstrap\ActiveForm;
//use yii\widgets\ActiveForm;
use backend\models\Movie;
use backend\models\Movietheater;
use yii\helpers\Url;
use yii\web\View;
use yii\web\JsExpression;
use kartik\select2\Select2;
use kartik\widgets\DatePicker;
use kartik\widgets\DateTimePicker;
use yii2mod\alert\Alert;
$script = <<< JS
jQuery('.tooltipped').tooltip();
JS;
$this->registerJs($script, View::POS_READY, 'script-register');
echo Alert::widget([]);
?>
<div class="row">
  <div class="col s12 m6 offset-m3 l6 offset-l3 xl6 offset-xl3">
    <h4 class="header">Register Movie Theater</h4>
    <?php $form = ActiveForm::begin([
        'id' => 'inscripcion-form',
        'errorCssClass'   => 'has-error',
        'successCssClass' => 'has-success',
    ]); ?>
    <div class="card">
      <div class="card-content">
        <span class="card-title">Theater</span>

        <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'autofocus' => true, 'tab-index' => 1]) ?>

        <?= $form->field($model, 'location')->textInput(['maxlength' => true, 'autofocus' => true, 'tab-index' => 1]) ?>

      </div>
      <div class="card-action">

        <?= Html::submitButton('SAVE <i class="material-icons right">send</i>',
          [
            'class'         => 'btn waves-effect waves-light red lighten-2 tooltipped',
            'data-position' => 'right',
            'data-tooltip'  => 'Save <span class="red-text text-lighten-2"><b>Movie Show Time Finder</b></span>'
          ]) ?>

      </div>
    </div>
    <?php ActiveForm::end(); ?>
  </div>
</div>
