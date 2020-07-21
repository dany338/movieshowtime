<?php

namespace api\modules\v1\models;

use Yii;
use yii\base\Model;
use backend\models\Test;
/**
 * AccessTest is the model behind the contact form.
 */
class Reporte extends Model
{
  public $aciertos;
  public $formulacion;

  /**
    * @inheritdoc
    */
  public function rules()
  {
      return [
        [['aciertos'], 'integer'],
      ];
  }

  /**
    * @inheritdoc
    */
  public function attributeLabels()
  {
      return [
          'aciertos'    => 'Aciertos Población',
          'formulacion' => 'Valor de la Pregunta'
      ];
  }

}
