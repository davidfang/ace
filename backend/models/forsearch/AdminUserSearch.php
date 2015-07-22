<?php

namespace backend\models\forsearch;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\AdminUser;

/**
 * AdminUserSearch represents the model behind the search form about `backend\models\AdminUser`.
 */
class AdminUserSearch extends AdminUser
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['username',  'userphoto'], 'safe'],
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
        $query = AdminUser::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
        ]);

        $query->andFilterWhere(['like', 'username', $this->username])
           // ->andFilterWhere(['like', 'password', $this->password])
            ->andFilterWhere(['like', 'userphoto', $this->userphoto]);

        return $dataProvider;
    }
}
