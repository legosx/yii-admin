<?php

namespace app\models;

use yii;
use yii\mongodb\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\BaseActiveRecord;
use app\components\Arr;
use app\components\Env;
use MongoDB\BSON\UTCDatetime;
use MongoDB\BSON\ObjectID;
use Exception;

/**
 * This is the model class for collection "movies".
 *
 * @property ObjectID $_id
 * @property mixed $title
 * @property mixed $description
 * @property mixed $year
 * @property mixed $imdb
 * @property mixed $poster
 * @property mixed $created
 * @property mixed $updated
 * @property mixed $status
 * @property mixed $movies
 * @property mixed $api_name
 */
class Movie extends ActiveRecord
{

    const STATUS_NEW = 'new';
    const STATUS_PUB = 'pub';
    const STATUS_DEL = 'del';

    public static $statusLabel = [
        self::STATUS_NEW => 'New',
        self::STATUS_PUB => 'Published',
        self::STATUS_DEL => 'Removed'
    ];

    /**
     * @param array $row
     * @return static
     */
    public static function instantiate($row)
    {
        $model = parent::instantiate($row);
        $model->setAttributes($row);
        return $model;
    }

    const SCENARIO_CATEGORY = 'category';

    public function scenarios()
    {
        $parent = parent::scenarios();
        $parent[self::SCENARIO_CATEGORY] = Arr::get($parent, self::SCENARIO_DEFAULT);
        return $parent;
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created',
                'updatedAtAttribute' => 'updated',
                'value' => function ($event) {
                    return new UTCDatetime(time() * 1000);
                },
                'attributes' => [
                    BaseActiveRecord::EVENT_BEFORE_INSERT => 'created',
                    BaseActiveRecord::EVENT_BEFORE_UPDATE => 'updated',
                ]
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return 'movies';
    }

    /**
     * @return \yii\mongodb\Connection the MongoDB connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    public function __get($name)
    {
        if (preg_match('/^([^.]+)\.(.*?)$/', $name, $matches)) {
            if (is_array($this->{$matches[1]})) {
                return Arr::get($this->{$matches[1]}, $matches[2]);
            }
        }

        return parent::__get($name);
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return [
            '_id',
            'title',
            'description',
            'year',
            'imdb',
            'poster',
            'created',
            'updated',
            'movies',
            'status',
            'api_name'
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'description', 'year', 'imdb', 'poster', 'created', 'updated', 'movies', 'status', 'api_name'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            '_id' => 'ID',
            'title' => 'Title',
            'description' => 'Description',
            'year' => 'Year',
            'imdb' => 'Imdb',
            'poster' => 'Poster',
            'created' => 'Created',
            'updated' => 'Updated',
            'movies' => 'Movies',
            'status' => 'Status',
            'api_name' => 'Api Name'
        ];
    }

    public function getCategoryList()
    {
        /** @var Movie[] $categories */
        $categories = $this->find()->where([
            'movies' => $this->_id
        ])->all();

        if (!$categories) {
            return [];
        }

        $list = [];
        foreach ($categories as $category) {
            $list[] = [
                '_id' => (string)$category->_id,
                'path' => $category->listParents()
            ];
        }

        return $list;
    }

    public static function addToCategory($category, $movies)
    {
        $parent = self::listCategoryPath($category, ['parents']);

        foreach ($movies as $movie_key => $movie_value) {
            $child = self::listCategoryPath($movie_value, ['childs']);
            if (array_intersect($parent, $child)) {
                unset($movies[$movie_key]);
            }
        }

        if (!$movies) {
            return;
        }

        self::updateAll([
            '$addToSet' => [
                'movies' => [
                    '$each' => $movies
                ]
            ],
            '$set' => [
                'updated' => new UTCDatetime(time() * 1000)
            ]
        ], [
            '_id' => $category
        ]);
    }

    public static function updateCategory($movie, $categories)
    {
        $old = self::find()
            ->where(['movies' => $movie])
            ->select(['_id'])
            ->column();

        $list = array_filter([
            '$pull' => array_values(array_diff($old, $categories)),
            '$addToSet' => array_values(array_diff($categories, $old))
        ]);

        if (!$list) {
            return;
        }

        foreach ($list as $op => $category_ids) {
            if ($op == '$addToSet') {
                $child = self::listCategoryPath($movie, ['childs']);
                if ($child) {
                    foreach ($category_ids as $category_key => $category_id) {
                        $parent = self::listCategoryPath($category_id, ['parents']);
                        if (array_intersect($parent, $child)) {
                            unset($category_ids[$category_key]);
                        }
                    }
                }

                if (!$category_ids) {
                    continue;
                }
            }

            self::updateAll([
                $op => [
                    'movies' => $movie,
                ],
                '$set' => [
                    'updated' => new UTCDatetime(time() * 1000)
                ]
            ], [
                '_id' => $category_ids
            ]);
        }
    }

    public static function deleteCategory($_id)
    {
        self::updateAll([
            '$pull' => [
                'movies' => [
                    '$in' => $_id
                ]
            ],
            '$set' => [
                'updated' => new UTCDatetime(time() * 1000)
            ]
        ]);

        self::deleteAll([
            '_id' => $_id
        ]);
    }

    /**
     * Возвращает всех родителей и детей, включая саму категорию
     * @param ObjectID $_id
     * @param array $return
     * @return array
     */
    public static function listCategoryPath($_id, $return = ['childs', 'parents'])
    {
        $model = self::findOne([
            '_id' => $_id,
            'movies' => [
                '$exists' => true
            ]
        ]);
        if (!$model) {
            return [];
        }

        $result = [];
        foreach ($return as $ret) {
            switch ($ret) {
                case 'childs':
                    $result = array_merge($result, $model->listChildsIds());
                    break;
                case 'parents':
                    $result = array_merge($result, $model->listParentsIds());
                    break;
            }
        }

        return array_values(array_unique($result));
    }

    public function listChildsIds()
    {
        $data = [(string)$this->_id];

        /** @var Movie[] $childs */
        $childs = $this->find()->where([
            '_id' => $this->movies,
            'movies' => [
                '$exists' => true
            ]
        ])->all();

        if (!$childs) {
            return $data;
        }

        foreach ($childs as $child) {
            $data = array_merge($data, $child->listChildsIds());
        }

        return $data;
    }

    public function listParentsIds()
    {
        /** @var Movie[] $parents */
        $parents = self::find()->where([
            'movies' => $this->_id,
        ])->all();

        $data = [(string)$this->_id];
        if (!$parents) {
            return $data;
        }

        foreach ($parents as $parent) {
            $data = array_merge($data, $parent->listParentsIds());
        }

        return $data;
    }

    public function listParents($delimiter = ' / ', $path = '')
    {
        /** @var Movie[] $roots */
        $roots = self::find()->where([
            'movies' => $this->_id,
        ])->all();

        $path = $this->title . ($path ? $delimiter : '') . $path;

        if (!$roots) {
            return [$path];
        }

        $data = [];
        foreach ($roots as $root) {
            $data = array_merge($data, $root->listParents($delimiter, $path));
        }

        return $data;
    }

    public function getMovieCount()
    {
        $result = [];
        foreach ([0, 1] as $exist) {
            $result[] = self::find()->where([
                '_id' => $this->movies,
                'movies' => [
                    '$exists' => $exist
                ],
                'status' => [
                    '$ne' => self::STATUS_DEL
                ]
            ])->count();
        }

        return $result;
    }
}
