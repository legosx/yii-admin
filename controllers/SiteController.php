<?php

namespace app\controllers;

use app\models\Movie;
use yii;
use app\components\Controller;
use yii\filters\VerbFilter;

class SiteController extends Controller
{

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'index' => ['get'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ]
        ];
    }

    public function actionIndex()
    {
        $c = Movie::getCollection();

        $data = [
            'new' => $c->count(['status' => 'new']),
            'pub' => $c->count(['status' => 'pub']),
            'del' => $c->count(['status' => 'del']),
            'all' => $c->count(['movies' => ['$exists' => false]])
        ];

        return $this->render('index', ['data' => $data]);
    }
}
