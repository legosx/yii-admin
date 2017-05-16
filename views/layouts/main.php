<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use yii\widgets\ActiveForm;
use app\assets\AppAsset;
use app\models\Movie;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title>Admin</title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    if (isset($this->blocks['wrap'])) {
        echo $this->blocks['wrap'];
    } else {
        NavBar::begin([
            'brandLabel' => false,
            'options' => [
                'class' => 'navbar-inverse navbar-fixed-top',
            ],
        ]);
        echo Nav::widget([
            'options' => ['class' => 'navbar-nav navbar-left'],
            'items' => [
                ['label' => 'Main', 'url' => ['/site/index']],
                ['label' => 'Movies', 'url' => ['/movie/index']],
                ['label' => 'Categories', 'url' => ['/category/index']],
            ],
        ]); ?>

        <?php
        $model = new Movie();
        $form = ActiveForm::begin([
            'id' => 'navbar-search-form',
            'action' => ['/movie/index'],
            'method' => 'get',
            'validateOnType' => false,
            'enableAjaxValidation' => false,
            'enableClientScript' => false,
            'options' => [
                'class' => 'form-inline pull-right navbar-search-form'
            ]
        ]);
        ?>

        <?php
        $field = $form->field($model, 'title');
        $field->textInput(['placeholder' => 'Movie search...']);
        $field->template = "{input}";
        echo $field;
        ?>
        <?php ActiveForm::end(); ?>

        <?php

        NavBar::end();
        ?>

        <div class="container-fluid main-container">
            <div class="container">
                <?= Breadcrumbs::widget([
                    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : []
                ]);
                ?>
            </div>
            <?= $content ?>
        </div>
        <?php
    }
    ?>
</div>


<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; Movies <?= date('Y') ?></p>

        <p class="pull-right"></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
