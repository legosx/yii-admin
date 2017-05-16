<?php

namespace app\controllers;

use app\components\Poster;
use app\components\Curl;
use MongoDB\BSON\ObjectID;
use MongoDB\BSON\Regex;
use yii;
use app\models\Movie;
use app\models\search\Movie as MovieSearch;
use app\components\Arr;
use app\components\Controller;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\UploadedFile;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use finfo;
use Imagick;
use ImagickException;

class MovieController extends Controller
{

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete-poster' => ['POST'],
                    'update-poster' => ['POST']
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new MovieSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if (Yii::$app->request->post('hasEditable')) {
            return $this->_movieEditableHandle();
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }

    public function actionPoster($image)
    {
        if ($raw = Poster::get($image)) {
            Yii::$app->response->format = Response::FORMAT_RAW;
            Yii::$app->response->headers->add('Content-Type', (new finfo(FILEINFO_MIME_TYPE))->buffer($raw));

            return $raw;
        }

        preg_match('/^([^_]+)((_(\d+)x(\d+))?)?\.(\w+)$/', $image, $matches);

        $id = Arr::get($matches, 1);
        $width = Arr::get($matches, 4);
        $height = Arr::get($matches, 5);
        $extension = Arr::get($matches, 6);

        $movie = Movie::findOne([
            '_id' => new ObjectID($id),
            '$or' => [
                [
                    '$and' => [
                        ['poster' => ['$exists' => true]],
                        ['poster' => ['$ne' => null]]
                    ]
                ],
                [
                    '$and' => [
                        ['imdb.Poster' => ['$exists' => true]],
                        ['imdb.Poster' => ['$ne' => 'N/A']],
                        ['imdb.Poster' => ['$ne' => null]],
                        ['imdb.Poster' => ['$ne' => '']]
                    ]
                ]
            ]
        ]);

        if (!$movie) {
            throw new NotFoundHttpException('Image not found');
        }

        $poster = $movie->poster;
        $imdb_poster = $movie->{'imdb.Poster'};
        $original = $id . '.' . $extension;

        // Try to download poster
        $posterData = null;
        if (!$poster) {
            if (!($posterData = Curl::get($imdb_poster))) {
                throw new NotFoundHttpException('Failed to download poster');
            }

            Poster::save($original, $posterData);
            $movie->poster = $original;
            $movie->save(false);

            if (!$width && !$height) {
                Yii::$app->response->format = Response::FORMAT_RAW;
                Yii::$app->response->headers->add('Content-Type', (new finfo(FILEINFO_MIME_TYPE))->buffer($raw));

                return $posterData;
            }
        }
        if (!$posterData) {
            $posterData = Poster::get($original);
        }

        // Generate poster with specified size
        if ($width || $height) {
            if (!class_exists('Imagick')) {
                throw new NotFoundHttpException('Imagick extension is needed');
            }

            try {
                $image = new Imagick();
                $image->readImageBlob($posterData);
                $image->thumbnailImage($width, $height);
                $raw = $image->getImageBlob();
                Poster::save($id . '_' . $width . 'x' . $height . '.' . $extension, $raw);
            } catch (ImagickException $e) {
                throw new NotFoundHttpException($e->getMessage());
            }

            Yii::$app->response->format = Response::FORMAT_RAW;
            Yii::$app->response->headers->add('Content-Type', (new finfo(FILEINFO_MIME_TYPE))->buffer($raw));

            return $raw;
        }

        throw new NotFoundHttpException('Image not found');
    }

    public function actionUpdatePoster($id)
    {
        if (!Yii::$app->request->isAjax || !($poster = UploadedFile::getInstanceByName('poster'))) {
            throw new BadRequestHttpException;
        }

        $allowed = [
            'jpeg' => 'jpg',
            'png' => 'png'
        ];
        $type = str_replace('image/', '', Arr::get(getimagesize($poster->tempName), 'mime'));
        if (!($ext = Arr::get($allowed, $type))) {
            throw new BadRequestHttpException;
        }
        $filename = $id . '.' . $ext;

        Poster::delete($id);
        Poster::save($filename, file_get_contents($poster->tempName));

        $movie = Movie::findOne([
            '_id' => new ObjectID($id)
        ]);

        $movie->poster = $filename;
        $movie->save(false);

        return $this->renderPartial('grid/poster', [
            'data' => $movie,
            'reload' => true
        ]);
    }

    public function actionDeletePoster($id)
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException;
        }

        Poster::delete($id);

        $movie = Movie::findOne([
            '_id' => new ObjectID($id)
        ]);
        $movie->poster = null;
        $movie->save(false);

        return $this->renderPartial('grid/poster', [
            'data' => $movie,
            'reload' => true
        ]);
    }

    public function actionCategories($q)
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException;
        }

        Yii::$app->response->format = Response::FORMAT_JSON;


        $limit = 10;
        if (!$q) {
            $q = 'Genres;';
            $limit = 0;
        }

        if (strpos($q, ';') === false) {
            /** @var Movie[] $categories */
            $categories = Movie::find()->where([
                'title' => new Regex('^' . $q, 'i'),
                'movies' => [
                    '$exists' => true
                ]
            ])->orderBy([
                'updated' => -1
            ])->limit($limit)->all();
        } else {
            $parts = explode(';', $q);

            $categories = [];
            $movies = [];
            foreach ($parts as $i => $part) {
                if ($i == count($parts) - 1) {
                    $categories = Movie::find()->where(array_filter([
                        '_id' => ['$in' => $movies],
                        'title' => $part ? new Regex('^' . $part, 'i') : null,
                        'movies' => [
                            '$exists' => true,
                        ]
                    ]))->orderBy([
                        'updated' => -1
                    ])->limit($limit)->all();
                } else {
                    $movies = Movie::find()->where(array_filter([
                        '_id' => $movies ? ['$in' => $movies] : null,
                        'title' => $part,
                        'api_name' => [
                            '$exists' => $i == 0
                        ]
                    ]))->orderBy([
                        'updated' => -1
                    ])->select(['movies'])->asArray()->column();
                    if (!$movies) {
                        break;
                    }

                    $movies = Arr::get($movies, 0);
                }
            }
        }

        if (!$categories) {
            return [];
        }

        $main_query = preg_replace('/((.*?);)[^;]+$/', '$1', $q);

        $result = [];
        foreach ($categories as $category) {
            $parents = array_values($category->listParents(';'));
            $cat = Arr::get(array_values(preg_grep('/^' . $main_query . '/', $parents)), 0);
            if (!$cat) {
                $cat = Arr::get(array_values(preg_grep('/^Genres/', $parents)), 0);
            }
            if (!$cat) {
                $cat = Arr::get($parents, 0);
            }

            $postfix = Movie::find()->where([
                '_id' => [
                    '$in' => $category->movies,
                ],
                'movies' => [
                    '$exists' => true
                ]
            ])->exists() ? ';' : '';

            $result[(string)$category->_id] = $cat . $postfix;
        }

        return $result;
    }
}
