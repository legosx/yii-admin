<?php
use app\models\Movie;
use yii\helpers\Html;
use yii\web\View;
use rmrevin\yii\fontawesome\FA;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use app\components\Controller;

/** @var View $this */
/** @var Movie $data */
$id = (string)$data->_id;
$cid = 'movie_category_map'; // storage typeahead
$tid = 'category-select-' . $id; // typeahead
$mid = 'new-category-modal-' . $id; // modal
?>

<div class="category-block">
    <?php
    $form = ActiveForm::begin([
        'id' => 'category-block-form-' . $id
    ]);
    $fid = '#' . $form->getId();

    echo Html::hiddenInput('hasEditable', 1);
    echo Html::hiddenInput('editableIndex', 0);
    echo Html::hiddenInput('editableKey', $id);
    echo Html::hiddenInput('editableAttribute', 'category');
    echo Html::hiddenInput('editableOutput', '/common/category');
    ?>

    <div class="category-check">
        <?php
        $list = $data->getCategoryList();
        if ($list) {
            foreach ($list as $check) {
                $check_id = 'movie-category-check-' . $check['_id'] . '-' . $id;
                foreach ($check['path'] as $i => $path) {
                    if (!$i) {
                        echo Html::checkbox('Movie[0][category][]', true, ['label' => $path, 'onChange' => "$(this).parents('tr').find('.save-buttons').addClass('visible');", 'id' => $check_id, 'value' => $check['_id']]);
                    } else {
                        echo Html::label($path, $check_id, ['class' => 'category-just-label']);
                    }
                }
            }
        } ?>
    </div>
    <?php
    $onClick = <<< JS
    if (!('$cid' in window)) {
        return false;        
    }
    var cid = window.$cid, name = $('#$tid').val().replace(new RegExp(' / ', 'g'), ';'), current = [], cat_id = '';
    
    if (!(name in cid)) {
        $('#$tid').parent().addClass('has-error');
        return false;
    }
    cat_id = cid[name];            
    
    $('$fid input[type=checkbox][name="Movie[0][category][]"]').each(function() {
        current.push($(this).val());          
    });
    
    current.push('$id');
    
    if (current.indexOf(cat_id) !== -1) {
        $('#$tid').parent().addClass('has-error');
        return false;
    }
        
    $('#$mid').modal('toggle');
    
    $('$fid .category-check').append(
    '<label>' +
     '<input type="checkbox" id="movie-category-check-'+cat_id+'-$id" name="Movie[0][category][]" value="'+cat_id+'" checked=""> ' +
      name.replace(new RegExp(';', 'g'), ' / ') +
    '</label>');
    
    $('$fid').parents('tr').find('.save-buttons').addClass('visible');
    
    return false;
JS;

    Modal::begin([
        'header' => 'Category add',
        'options' => [
            'class' => 'category-modal',
            'id' => $mid
        ],
        'footer' => Html::button('Add', ['class' => 'btn btn-primary', 'onClick' => $onClick]) . Html::button('Cancel', ['class' => 'btn', 'data-dismiss' => 'modal'])
    ])->setId($mid);

    $typeahead_init = str_replace('-', '_', $tid) . '_init';

    echo $this->render('category-typeahead', [
        'name' => 'Movie[category][]',
        'tid' => $tid,
        'fid' => $fid,
        'fname' => $typeahead_init,
        'id' => $id
    ]);

    Modal::end();

    $onClick = <<< JS
    if (!$('#{$mid}').hasClass('category-init')) {
        $('#{$mid}').on('show.bs.modal', function () {
            setTimeout('$(\'#{$mid} .tt-input\').focus()', 0);        
        }).on('hide.bs.modal', function() {
            $('#{$mid} .tt-input').parent().removeClass('has-error');
            $('#{$mid} .tt-input').typeahead('val', '');
        });
        $('#{$mid}').addClass('category-init');
        
        $('#{$mid} .tt-input').change(function() {
            $(this).parent().removeClass('has-error');          
        });
    }
    
    $('#$mid').modal('toggle');
    
    return false;
JS;
    ?>

    <a href="#" data-toggle="tooltip" data-placement="top" title="Add" onClick="<?= $onClick ?>">
        <?= FA::icon(FA::_PLUS) ?>
    </a>

    <?php

    $list = [
        'save' => Controller::ACTION_UPDATE_CATEGORY,
        'reset' => Controller::ACTION_OUTPUT
    ];

    $events = [];

    foreach ($list as $event => $action) {
        $js = <<< JS
        $.ajax({
            async: false,
            context: this,
            method: 'POST',
            data:$('$fid').serialize() + "&editableAction=$action",
            success: function(data) {
                $(this).parent().parent().parent().html(data);
                $('[data-toggle="tooltip"]').tooltip({trigger: 'hover'});
                $typeahead_init();
            }
        });
        return false;
JS;

        $events[$event] = $js;
    }

    ?>

    <div class="action">
        <?= Html::button(FA::icon('floppy-o') . ' Save', ['class' => 'btn btn-xs btn-primary kv-editable-submit', 'onClick' => $events['save']]) ?>
        <?= Html::button(FA::icon('times') . ' Cancel', ['class' => 'btn btn-xs kv-editable-close kv-editable-reset', 'onClick' => $events['reset']]) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
