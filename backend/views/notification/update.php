<?php

use macgyer\yii2materializecss\lib\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\Notification */

$this->title = Yii::t('yii', 'Update {modelClass}: ', [
    'modelClass' => 'Notification',
]) . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('yii', 'Notifications'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('yii', 'Update');
?>
<div class="notification-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
