<?php

use macgyer\yii2materializecss\lib\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\Moviebillboard */

$this->title = Yii::t('yii', 'Create Moviebillboard');
$this->params['breadcrumbs'][] = ['label' => Yii::t('yii', 'Moviebillboards'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="moviebillboard-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
