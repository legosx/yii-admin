<?php
use app\models\Movie;
use vova07\imperavi\Widget as Redactor;
use yii\widgets\ActiveForm;
use yii\helpers\Html;

/**
 * @var Movie $data
 * @var string $field
 */

$id = (string)$data->_id;

$fid = '#' . ActiveForm::begin([
        'id' => 'redactor-' . $field . '-form-' . $id
    ])->getId();

echo Html::hiddenInput('hasEditable', 1);
echo Html::hiddenInput('editableIndex', 0);
echo Html::hiddenInput('editableKey', $id);
echo Html::hiddenInput('editableAttribute', $field);
?>

    <div class="editable-redactor">
        <?php
        $js = <<< JS
            $(this).hide();
            $(this).next().show();
            $(this).next().find('.redactor-editor').focus();
JS;
        ?>
        <div class="editable-redactor-display kv-editable-link" onclick="<?= $js ?>">
            <?= trim($data->$field) ? $data->$field : '(n/a)' ?>
        </div>
        <div class="editable-redactor-edit">
            <?php
            echo Redactor::widget([
                'name' => 'Movie[0][' . $field . ']',
                'value' => $data->$field,
                'settings' => [
                    'lang' => 'ru',
                    'toolbar' => false,
                    'pastePlainText' => true,
                    'pasteImages' => false,
                    'minHeight' => 150,
                    'buttons' => []
                ]
            ]);
            ?>
        </div>
    </div>

<?php

$save = <<< JS
    $(this).parent().find('.editable-redactor-edit .redactor-editor').addClass('kv-editable-processing');
    $.ajax({
        context: this,
        method: 'POST',
        data: $('$fid').serialize(),
        success: function(data) {
            $(this).parent().find('.editable-redactor-edit .redactor-editor').removeClass('kv-editable-processing');
            $(this).parent().find('.editable-redactor-display').html($.trim(data.output) ? data.output : '(n/a)');
            $(this).parent().find('.editable-redactor-edit .redactor-editor').html(data.output);
            $(this).parent().find('.editable-redactor-edit textarea').val(data.output);
            $(this).parent().find('.editable-redactor-edit').hide();
            $(this).parent().find('.editable-redactor-display').show();
        }
    });
            
    return false;
JS;

echo Html::button('', ['class' => 'kv-editable-submit', 'onClick' => $save]);

ActiveForm::end();