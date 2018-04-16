<?php

namespace center\modules\auth\models;

use common\models\UserModel;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use center\modules\auth\models\AuthItem;
use yii\db\Query;
use yii\web\User;

/**
 * AuthItemSearch represents the model behind the search form about `center\modules\auth\models\AuthItem`.
 */
class AuthItemSearch extends AuthItem
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'description', 'updated_at'], 'safe'],
            [['type', 'created_at', 'updated_at'], 'integer'],
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
    public function search($params, $type)
    {
        if (\common\models\User::isSuper()) {
            $query = AuthItem::find()->where(['type' => $type]);
        } else {
            $uid = Yii::$app->user->id;
            $array = AuthItem::findAll(['by_id' => $uid]);
            if (!empty($array)) {
                $query = AuthItem::find()->where(['type' => $type]);
                foreach ($array as $key => $val) {
                    $newPath = $val['attributes']['path'] . '-' . $val['attributes']['id'];
                    if ($key == 0) {
                        $query->andWhere('path like "' . $newPath . '%"');
                    } else {
                        $query->orWhere('path like "' . $newPath . '%"');
                    }
                }
                $query->orWhere(['by_id' => $uid]);
            } else {
                $query = AuthItem::find()->where(['type' => null]);
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

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'updated_at', $this->updated_at]);

        return $dataProvider;
    }
}
