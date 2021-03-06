<?php

use macgyer\yii2materializecss\lib\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\Movietheater */

$this->title = Yii::t('yii', 'Update {modelClass}: ', [
    'modelClass' => 'Movietheater',
]) . ' ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('yii', 'Movietheaters'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('yii', 'Update');
?>
<div class="movietheater-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
