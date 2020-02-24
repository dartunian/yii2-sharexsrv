<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\FileEntry;

class FileEntrySearch extends FileEntry
{
   // add the public attributes that will be used to store the data to be search
    public $combo;
    public $public_combo;
    public $name;
	public $time;

    // now set the rules to make those attributes safe
    public function rules()
    {
        return [
            // ... more stuff here
            [['combo', 'public_combo', 'name', 'time'], 'safe'],
            // ... more stuff here
        ];
    }
	
	public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }
	
    public function search($params)
    {
        $query = FileEntry::find()
            ->where(['user' => Yii::$app->user->identity->ms_id])
            ->orWhere(['in', 'auth_id_list', Yii::$app->user->identity->id]);
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        // load the search form data and validate
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        // adjust the query by adding the filters
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['like', 'combo', $this->combo])
            ->andFilterWhere(['like', 'public_combo', $this->public_combo])        
			->andFilterWhere(['like', 'name', $this->name])
			->andFilterWhere(['like', 'time', $this->time]);

        return $dataProvider;
    }
}