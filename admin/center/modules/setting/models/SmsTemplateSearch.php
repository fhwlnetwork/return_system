<?php

namespace center\modules\setting\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use center\modules\setting\models\SmsTemplate;

/**
 * SmsTemplateSearch represents the model behind the search form about `center\modules\setting\models\SmsTemplate`.
 */
class SmsTemplateSearch extends SmsTemplate
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'is_delete'], 'integer'],
            [['name', 'join_ali', 'content', 'instructions'], 'safe'],
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
        $query = SmsTemplate::find();

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
            //'id' => $this->id,
            'status' => $this->status,
            'is_delete' => $this->is_delete
            //'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'join_ali', $this->join_ali])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'content', $this->content])
            ->andFilterWhere(['like', 'instructions', $this->instructions])
            ->andFilterWhere(['like', 'created_at', strtotime($this->created_at)]);

        return $dataProvider;
    }
}
