<?php

use app\components\Arr;
use yii\helpers\Url;
use app\models\Movie;

/**
 * @var       $this yii\web\View
 * @var array $data
 */

?>
<div class="site-index">
    <div class="container">
        <h3>Main page</h3>

        <table class="table table-bordered table-stat">
            <caption>Movie stats</caption>
            <thead>
            <tr>
                <th>New</th>
                <th>Published</th>
                <th>Removed</th>
                <th>Total</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    <A href="<?= Url::to(['movie/index', 'Movie[status]' => Movie::STATUS_NEW]) ?>"><?= Arr::get($data, 'new') ?></A>
                </td>
                <td>
                    <A href="<?= Url::to(['movie/index', 'Movie[status]' => Movie::STATUS_PUB]) ?>"><?= Arr::get($data, 'pub') ?></A>
                </td>
                <td>
                    <A href="<?= Url::to(['movie/index', 'Movie[status]' => Movie::STATUS_DEL]) ?>"><?= Arr::get($data, 'del') ?></A>
                </td>
                <td>
                    <A href="<?= Url::to(['movie/index']) ?>"><?= Arr::get($data, 'all') ?></A>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>