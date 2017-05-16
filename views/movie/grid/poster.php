<?php
use app\models\Movie;
use yii\helpers\Url;
use yii\helpers\Html;
use rmrevin\yii\fontawesome\FA;
use yii\bootstrap\Modal;
use app\components\Arr;
use yii\web\View;
use app\components\Env;

/**
 * @var View $this
 * @var Movie $data
 * @var bool $reload
 */
if (!isset($reload)) {
    $reload = false;
}

$id = (string)$data->_id;
$url = null;
if ($ext = pathinfo($data->poster ? $data->poster : $data->{'imdb.Poster'}, PATHINFO_EXTENSION)) {
    $url = Url::to(['movie/poster/' . $id . '.' . $ext]);
}
?>

<div class="preview-block">
    <div class="preview-panel">
        <?php if ($url): ?>
            <a class="btn btn-info btn-xs" target="_blank" data-pjax="0" href="<?= $url ?>"><i class="fa fa-link"></i></a>
        <?php endif; ?>
        <input class="hidden" type="file" name="file">
        <?php
        $actionUrl = Url::to(['movie/update-poster', 'id' => $id]);
        echo Html::a(FA::icon('cog'), '#', [
            'class' => 'btn btn-primary btn-xs file-upload',
            'onClick' => <<< JS
                var file = $(this).prev();
                if (!$(file).hasClass('sendfile')) {
                    $(file).change(function() {
                        formData = new FormData;
                        formData.append("poster", this.files[0]);
                        $.ajax({
                            url: '$actionUrl',
                            type: 'POST',
                            data: formData,
                            cache: false,
                            context: this,
                            contentType: false,
                            processData: false,
                            success: function (data) {
                                if (data) {
                                    $(this).parent().parent().html(data);
                                }
                            }
                        });
                    });
                    $(file).addClass('sendfile');
                }
                $(file).click();
                return false;
JS
        ]);
        ?>
        <?php if ($url) {
            $confirm = "Are you really want to delete poster?";
            $actionUrl = Url::to(['movie/delete-poster', 'id' => $id]);
            echo Html::a(FA::icon('trash'), '#', [
                'class' => 'btn btn-danger btn-xs btn-delete-poster',
                'onClick' => <<< JS
                                if (confirm("$confirm")) {
                                    $.ajax({
                                        type: 'POST',
                                        cache: false,
                                        context: this,
                                        url: "$actionUrl",
                                        success: function(data) {
                                            if (data) {
                                                $(this).parent().parent().html(data);
                                            }
                                        }
                                    });
                                }
                                return false;
JS
            ]);
        } ?>
    </div>
    <a href="http://www.imdb.com/title/<?= $data->{'imdb.imdbID'} ?>/" target="_blank" data-pjax="0">
        <?php if (!$url) { ?>
            <div class="preview-text">n/a</div>
        <?php } ?>
        <?php
        if ($url) {
            $path = pathinfo($url);
            $url = Arr::get($path, 'dirname') . '/' . Arr::get($path, 'filename') . '_110x0.' . Arr::get($path, 'extension');
            if ($reload) {
                $url .= '?' . time();
            }
        }
        ?>
        <img src="<?= $url ?>">
    </a>
</div>
