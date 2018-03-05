<?php

namespace app\models\search;

use yii;
use app\components\Arr;
use yii\data\ActiveDataProvider;
use app\models\Movie as MovieModel;

/**
 * Movie represents the model behind the search form about `app\models\Movie`.
 */
class Movie extends MovieModel
{
    public function rules()
    {
        return [
            [['title', 'description', 'year', 'imdb', 'poster', 'created', 'updated', 'movies', 'status', 'api_name'], 'safe'],
        ];
    }

    public function search($params)
    {
        $query = MovieModel::find()->where([
            'movies' => [
                '$exists' => $this->scenario == self::SCENARIO_CATEGORY
            ]
        ]);

        if (!isset($params['sort'])) {
            $query->orderBy($this->scenario == self::SCENARIO_CATEGORY ? ['_id' => 1] : ['_id' => -1]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        if ($category = Arr::get($params, 'Movie.category')) {
            $parts = array_filter(explode(' / ', $category));

            $movies = [];
            foreach ($parts as $i => $part) {
                $movies = MovieModel::find()->where(array_filter([
                    '_id' => $movies ? ['$in' => $movies] : null,
                    'title' => $part,
                    'api_name' => [
                        '$exists' => $i == 0
                    ]
                ]))->select(['movies'])->asArray()->column();
                if (!$movies) {
                    break;
                }

                $movies = Arr::get($movies, 0);
            }

            $query->andWhere([
                '_id' => [
                    '$in' => $movies
                ]
            ]);
        }

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        if ($this->status != self::STATUS_DEL) {
            $query->andWhere([
                'status' => [
                    '$ne' => self::STATUS_DEL
                ]
            ]);
        }

        // grid filtering conditions
        $query->andFilterWhere(['like', '_id', $this->_id])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'year', $this->year])
            ->andFilterWhere(['like', 'imdb', $this->imdb])
            ->andFilterWhere(['like', 'poster', $this->poster])
            ->andFilterWhere(['like', 'created', $this->created])
            ->andFilterWhere(['like', 'updated', $this->updated])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'api_name', $this->api_name]);

        return $dataProvider;
    }
}
