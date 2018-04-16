<?php

namespace center\modules\product\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use center\modules\product\models\Major;

/**
 * MajorSearch represents the model behind the search form about `center\modules\product\models\Major`.
 */
class MajorSearch extends Major
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'ctime'], 'integer'],
            [['major_name'], 'safe'],
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
        $query = Major::find();

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
            'ctime' => $this->ctime,
        ]);

        $query->andFilterWhere(['like', 'major_name', $this->major_name]);

        return $dataProvider;
    }
}
