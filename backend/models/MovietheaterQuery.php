<?php

namespace backend\models;

/**
 * This is the ActiveQuery class for [[Movietheater]].
 *
 * @see Movietheater
 */
class MovietheaterQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Movietheater[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Movietheater|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
