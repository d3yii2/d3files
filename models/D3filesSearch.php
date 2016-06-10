<?php

namespace d3yii2\d3files\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * D3filesSearch represents the model behind the search form about `d3yii2\d3files\models\D3files`.
 */
class D3filesSearch extends D3files
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'type_id', 'user_id', 'deleted', 'model_id'], 'integer'],
            [['file_name', 'add_datetime', 'notes', 'model_name'], 'safe'],
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
        $query = D3files::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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
            'type_id' => $this->type_id,
            'add_datetime' => $this->add_datetime,
            'user_id' => $this->user_id,
            'deleted' => $this->deleted,
            'model_id' => $this->model_id,
        ]);

        $query->andFilterWhere(['like', 'file_name', $this->file_name])
            ->andFilterWhere(['like', 'notes', $this->notes])
            ->andFilterWhere(['like', 'model_name', $this->model_name]);

        return $dataProvider;
    }
}
