<?php

namespace center\modules\setting\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use center\modules\setting\models\ExtendsField;

/**
 * ExtendsFieldSearch represents the model behind the search form about `center\modules\setting\models\ExtendsField`.
 */
class ExtendsFieldSearch extends ExtendsField
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'is_must', 'can_search', 'type', 'show_type', 'sort'], 'integer'],
            [['table_name', 'field_name', 'field_desc', 'value', 'default_value', 'rule'], 'safe'],
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
        $query = ExtendsField::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'is_must' => $this->is_must,
            'can_search' => $this->can_search,
            'type' => $this->type,
            'show_type' => $this->show_type,
            'sort' => $this->sort,
        ]);

        $query->andFilterWhere(['like', 'table_name', $this->table_name])
            ->andFilterWhere(['like', 'field_name', $this->field_name])
            ->andFilterWhere(['like', 'field_desc', $this->field_desc])
            ->andFilterWhere(['like', 'value', $this->value])
            ->andFilterWhere(['like', 'default_value', $this->default_value])
            ->andFilterWhere(['like', 'rule', $this->rule])
            ->andFilterWhere(['!=', 'field_type', 2]);

        return $dataProvider;
    }
}
