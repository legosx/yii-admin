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
?>

<div class="actions-block">
    <?php if (!$data->api_name) { ?>
        <div class="actions-buttons">
            <?php
            $status = 'save';
            $title = 'Publish';
            $class = 'success';
            $icon = 'upload';
            $editable = 'kv-editable-submit';

            $fid = '#' . ActiveForm::begin(['id' => 'actions-block-' . $status . '-form-' . $id])->getId();

            echo Html::hiddenInput('hasEditable', 1);
            echo Html::hiddenInput('editableIndex', 0);
            echo Html::hiddenInput('editableKey', $id);
            echo Html::hiddenInput('editableOutput', 'grid/actions');
            echo Html::hiddenInput('editableAction', Controller::ACTION_OUTPUT);

            $save = <<< JS
            $(this).parents('tr').find('button.$editable').each(function() {
                $(this).click();            
            });
            
            $.ajax({
                context: this,
                method: 'POST',
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

            $status = $data::STATUS_DEL;
            $title = 'Remove';
            $class = 'danger';
            $icon = 'trash';

            $fid = '#' . ActiveForm::begin([
                    'id' => 'actions-block-' . $status . '-form-' . $id
                ])->getId();

            echo Html::hiddenInput('hasEditable', 1);
            echo Html::hiddenInput('editableIndex', 0);
            echo Html::hiddenInput('editableKey', $id);
            echo Html::hiddenInput('editableAction', Controller::ACTION_REMOVE_CATEGORY);

            $confirm = 'Are you really want to delete category "' . $data->title . '" ?';

            $save = <<< JS
    if (confirm('$confirm')) {
        $.ajax({
            context: this,
            method: 'POST',
            data: $('$fid').serialize(),
            success: function(data) {
                $(this).parents('.grid-view.category-grid').yiiGridView('applyFilter');
            }
        });
    }
            
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
            ?>
        </div>
    <?php } ?>
</div>