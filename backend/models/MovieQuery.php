<?php

namespace backend\models;

/**
 * This is the ActiveQuery class for [[Movie]].
 *
 * @see Movie
 */
class MovieQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Movie[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Movie|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
