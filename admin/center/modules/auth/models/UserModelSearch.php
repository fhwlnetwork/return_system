<?php

namespace center\modules\auth\models;

use common\models\User;
use common\models\UserModel;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * UserModelSearch represents the model behind the search form about `center\modules\auth\models\UserModel`.
 */
class UserModelSearch extends UserModel
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username'], 'safe'],
            [['created_at'], 'integer'],
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
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = UserModel::find();
        if (!User::isSuper()) {
            $In = (new User())->getChildIdAll();

            if (!empty($In)) {
                $query->where('id in(' . implode(',', array_keys($In)) . ')');
            } else {
                $query->where('id in(' .Yii::$app->user->identity->getId() . ')');
            }
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'username', $this->username]);
        $query->andFilterWhere(['like', 'username', $this->username]);

        return $dataProvider;
    }
}
