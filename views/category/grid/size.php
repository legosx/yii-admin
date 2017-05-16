<?php
use app\models\Movie;
use yii\web\View;
use app\components\Arr;
use yii\helpers\Html;
use yii\helpers\Url;
use rmrevin\yii\fontawesome\FA;

/** @var View $this */
/** @var Movie $data */

$count = $data->getMovieCount();
$movie_count = Arr::get($count, 0);
$category_count = Arr::get($count, 1);

$title = Arr::get($data->listParents(), 0);

?>

<?= Html::a($movie_count . ' movies', Url::to(['movie/index', 'Movie[category]' => $title]), ['data-pjax' => 0]) ?>
<br>
<?= Html::a($category_count . ' categories', Url::to(['category/index', 'Movie[category]' => $title]), ['data-pjax' => 0]) ?>

