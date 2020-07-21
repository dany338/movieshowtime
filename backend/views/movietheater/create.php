<?php

use macgyer\yii2materializecss\lib\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\Movietheater */

$this->title = Yii::t('yii', 'Create Movietheater');
$this->params['breadcrumbs'][] = ['label' => Yii::t('yii', 'Movietheaters'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="movietheater-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
