<?php

use macgyer\yii2materializecss\lib\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\Movie */

$this->title = Yii::t('yii', 'Create Movie');
$this->params['breadcrumbs'][] = ['label' => Yii::t('yii', 'Movies'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="movie-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
