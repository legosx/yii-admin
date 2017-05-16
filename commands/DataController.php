<?php
namespace app\commands;

use app\components\Arr;
use app\components\Curl;
use app\components\Env;
use app\models\Movie;
use yii\console\Controller;
use yii\helpers\Console;
use RuntimeException;

/**
 * Update MongoDb database from API
 *
 * ```
 * #Update command
 * yii data/update
 * ```
 *
 * The `unload` sub-command can be used similarly to unload fixtures.
 *
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since 2.0
 */

class DataController extends Controller
{
    public $defaultAction = 'update';

    /**
     * Add new 1000 random movies from API to MongoDb
     */
    public function actionUpdate()
    {
        if (!($api_url = Env::get('API_URL'))) {
            throw new \RuntimeException('API_URL is required in .env');
        }

        if (!Movie::find()->where(['movies' => ['$exists' => true]])->exists()) {
            Console::stdout('Create categories... ');

            $root = new Movie;
            $root->title = 'Genres';
            $root->api_name = 'root';
            $root->movies = [];
            $root->save(false);

            $sub4 = new Movie;
            $sub4->title = 'SuperDuperMegaGood';
            $sub4->movies = [];
            $sub4->save(false);

            $sub3 = new Movie;
            $sub3->title = 'SuperDuperGood';
            $sub3->movies = [$sub4->_id];
            $sub3->save(false);

            $sub2 = new Movie;
            $sub2->title = 'SuperGood';
            $sub2->movies = [$sub3->_id];
            $sub2->save(false);

            $sub1 = new Movie;
            $sub1->title = 'Good';
            $sub1->movies = [$sub2->_id];
            $sub1->save(false);

            $root->movies = array_merge($root->movies, [$sub1->_id]);

            $sub2 = new Movie;
            $sub2->title = 'SuperCrazy';
            $sub2->movies = [];
            $sub2->save(false);

            $sub1 = new Movie;
            $sub1->title = 'Crazy';
            $sub1->movies = [$sub2->_id];
            $sub1->save(false);

            $root->movies = array_merge($root->movies, [$sub1->_id]);

            foreach (['Funny', 'Best', 'Cool'] as $cat) {
                $sub1 = new Movie;
                $sub1->title = $cat;
                $sub1->movies = [];
                $sub1->save(false);

                $root->movies = array_merge($root->movies, [$sub1->_id]);
            }

            $root->save(false);

            Console::output('done');
        }

        Console::stdout('Update movies... ');

        $categories = Movie::findAll([
            'title' => [
                '$in' => ['SuperDuperMegaGood', 'SuperCrazy', 'Funny', 'Best', 'Cool']
            ],
            'movies' => [
                '$exists' => true
            ],
            'api_name' => [
                '$exists' => false
            ]
        ]);
        $categories_count = count($categories);

        $col_exists = Movie::find()
            ->where(['movies' => ['$exists' => false]])
            ->select(['imdb.imdbID'])
            ->column();

        $imdb_ids_exists = array_map(function ($arr) {
            return Arr::get($arr, 'imdbID');
        }, $col_exists);

        // Add 1000 random movies
        $total_count = 1000;
        $imdb_ids = [];
        srand();
        for ($i = 0; $i < $total_count; $i++) {
            do {
                $imdb_id = 'tt' . sprintf('%07d', rand(1, 2000000));
            } while (in_array($imdb_id, $imdb_ids) || in_array($imdb_id, $imdb_ids_exists));
            $imdb_ids[] = $imdb_id;
        }

        $last_procent = 0;
        foreach ($imdb_ids as $key => $imdb_id) {
            $procent = floor(($key / $total_count) * 100 / 5) * 5;
            if ($procent > $last_procent) {
                $last_procent = $procent;
                Console::stdout($procent . '%... ');
            }

            $imdb = @json_decode(Curl::get($api_url . '?i=' . $imdb_id), JSON_OBJECT_AS_ARRAY);

            if (!$imdb || Arr::get($imdb, 'Error')) {
                continue;
            }

            $movie = new Movie;
            $map = [
                'title' => 'Title',
                'description' => 'Plot',
                'year' => 'Year'
            ];
            foreach ($map as $field => $key) {
                $movie->setAttribute($field, Arr::get(['N/A' => null, 'True' => true, 'False' => false], $val = Arr::get($imdb, $key), $val));
            }
            $movie->status = Movie::STATUS_NEW;
            $movie->imdb = $imdb;
            $movie->save(false);

            /** @var Movie $category */
            $category = $categories[rand(0, $categories_count - 1)];
            $category->movies = array_merge($category->movies, [$movie->_id]);
            $category->save(false);
        }

        Console::output('done');
    }
}
