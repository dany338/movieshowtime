<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Subscription;

/**
 * SubscriptionSearch represents the model behind the search form about `backend\models\Subscription`.
 */
class SubscriptionSearch extends Subscription
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'movie_id', 'uid', 'notification', 'status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Subscription::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
              'pageSize' => 8,
            ],
            'sort'=> ['defaultOrder' => ['id' => SORT_DESC]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'movie_id' => $this->movie_id,
            'uid' => $this->uid,
            'notification' => $this->notification,
            'status' => $this->status,
            'updated_at' => $this->updated_at,
        ]);

        if(!empty($this->created_at)) {
          $this->created_at = strtotime($this->created_at);
          $this->created_at = date('Y-m-d', $this->created_at);
        }

        $query->andFilterWhere([
          'like',
          'DATE_FORMAT(created_at,"%Y-%m-%d")',
          $this->created_at
        ]);

        return $dataProvider;
    }
}
