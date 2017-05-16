<?php

namespace app\controllers;

use yii;
use app\models\Movie;
use app\models\search\Movie as MovieSearch;
use app\components\Controller;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use app\components\Arr;

class CategoryController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'create' => ['POST']
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new MovieSearch(['scenario' => Movie::SCENARIO_CATEGORY]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if (Yii::$app->request->post('hasEditable')) {
            return $this->_movieEditableHandle();
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate()
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException;
        }

        $post = Yii::$app->request->post();
        $movie = Movie::instantiate([]);
        $movie->title = Arr::get($post, 'CreateCategoryForm.name');
        $movie->movies = [];
        $movie->save(false);

        return true;
    }
}
