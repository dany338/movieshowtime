<?php

namespace backend\models;

/**
 * This is the ActiveQuery class for [[Moviebillboard]].
 *
 * @see Moviebillboard
 */
class MoviebillboardQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Moviebillboard[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Moviebillboard|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
