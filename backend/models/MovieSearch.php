<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Movie;

/**
 * MovieSearch represents the model behind the search form about `backend\models\Movie`.
 */
class MovieSearch extends Movie
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'moviedb_id', 'status', 'user_first_id'], 'integer'],
            [['name', 'created_at', 'updated_at'], 'safe'],
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
        $query = Movie::find();

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
            'moviedb_id' => $this->moviedb_id,
            'user_first_id' => $this->user_first_id,
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

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}
