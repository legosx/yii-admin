<?php

use yii\bootstrap\NavBar;

/* @var $this \yii\web\View */
/* @var $content string */

$this->beginContent('@app/views/layouts/main.php');
$this->beginBlock('wrap');
?>

<?= NavBar::widget([
    'brandLabel' => false,
    'options' => [
        'class' => 'navbar-inverse navbar-fixed-top',
    ],
]);
?>
    <div class="container">
        <?= $content ?>
    </div>
<?php
$this->endBlock();
$this->endContent();
?>