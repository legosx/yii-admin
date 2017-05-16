<?php
use app\models\Movie;
use yii\helpers\Html;
use yii\web\View;
use rmrevin\yii\fontawesome\FA;
use app\components\Arr;
use yii\widgets\ActiveForm;
use app\components\Controller;

/** @var View $this */
/** @var Movie $data */
$id = (string)$data->_id;

$buttons = [[
    'status' => $data::STATUS_PUB,
    'title' => 'Publish',
    'class' => 'success',
    'icon' => 'upload'
]];

if ($data->status != $data::STATUS_DEL) {
    $buttons[] = [
        'status' => $data::STATUS_DEL,
        'title' => 'Remove',
        'class' => 'danger',
        'icon' => 'trash'
    ];
}

?>

<div class="actions-block">
    <div class="status-label">
        <?= Arr::get($data::$statusLabel, $data->status) ?>
    </div>
    <div class="actions-buttons">
        <?php
        foreach ($buttons as $params) {
            $status = Arr::get($params, 'status');
            $title = Arr::get($params, 'title');
            $class = Arr::get($params, 'class');
            $icon = Arr::get($params, 'icon');

            $fid = '#' . ActiveForm::begin([
                    'id' => 'actions-block-' . $status . '-form-' . $id
                ])->getId();

            echo Html::hiddenInput('hasEditable', 1);
            echo Html::hiddenInput('editableIndex', 0);
            echo Html::hiddenInput('editableKey', $id);
            echo Html::hiddenInput('editableAttribute', 'status');
            echo Html::hiddenInput('Movie[0][status]', $status);
            echo Html::hiddenInput('editableOutput', 'grid/actions');

            $save = '';

            if (Arr::get($params, 'status') == $data::STATUS_PUB) {
                $save .= <<< JS
                $(this).parents('tr').find('button.kv-editable-submit').each(function() {
                    $(this).click();
                });
JS;
            }

            $save .= <<< JS
            $.ajax({
                context: this,
                method: 'POST',
                async: false,
                data: $('$fid').serialize(),
                success: function(data) {
                    if (data) {
                        $(this).parent().parent().parent().html(data);
                        $('[data-toggle="tooltip"]').tooltip({trigger: 'hover'});
                    }
                }
            });
            
            return false;
JS;

            echo Html::button(FA::icon($icon), [
                'data-toggle' => 'tooltip',
                'data-placement' => 'top',
                'class' => 'btn btn-' . $class . ' btn-sm',
                'title' => $title,
                'onClick' => $save
            ]);
            ActiveForm::end();
        }
        ?>
    </div>
</div>