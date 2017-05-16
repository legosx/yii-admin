<?php
use app\models\Movie;
use rmrevin\yii\fontawesome\FA;
use app\components\Arr;
use kartik\widgets\ActiveForm;
use yii\helpers\Html;
use yii\bootstrap\Modal;
use kartik\widgets\Typeahead;
use yii\helpers\Url;
use yii\web\JsExpression;
use app\components\Controller;

// to collection

$cid = 'movie_category_map'; // storage typeahead
$mid = 'bulk-category-modal';
$tid = 'bulk-category-typeahead';
$fid = '#' . ActiveForm::begin(['id' => 'bulk-category-form'])->getId();

echo Html::hiddenInput('hasEditable', 1);
echo Html::hiddenInput('editableIndex', 0);
echo Html::hiddenInput('editableAttribute', 'category');
echo Html::hiddenInput('editableAction', Controller::ACTION_TO_CATEGORY);

ActiveForm::end();

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
    
    selected = $(this).parents('.movie-index').find('.movie-grid').yiiGridView('getSelectedRows');

    $.ajax({
        context: this,
        method: 'POST',
        data: $('$fid').serialize() +'&'+ $.param({editableKey: selected}) + '&Movie[0][category]=' + cat_id,
        success: function(data) {
            $('#$mid').modal('toggle');
            $(this).parents('.movie-index').find('.movie-grid').yiiGridView('applyFilter');
        }
    });
    
    return false;
JS;

Modal::begin([
    'header' => 'Add category',
    'options' => [
        'id' => $mid,
        'class' => 'modal'
    ],
    'footer' => Html::button('Add', ['class' => 'btn btn-primary', 'onClick' => $onClick]) . Html::button('Cancel', ['class' => 'btn', 'data-dismiss' => 'modal'])
])->setId($mid);

echo $this->render('/common/category-typeahead', [
    'name' => '',
    'tid' => $tid,
    'fname' => 'bulk_category_typeahead_init'
]);

Modal::end();

$onClick = <<< JS
    if (!$('#$mid').hasClass('category-init')) {
        $('#$mid').on('show.bs.modal', function () {
            setTimeout('$(\'#$mid .tt-input\').focus()', 0);        
        }).on('hide.bs.modal', function() {
            $('#$mid .tt-input').parent().removeClass('has-error');
            $('#$mid .tt-input').typeahead('val', '');
        });
        $('#$mid').addClass('category-init');
        
        $('#$mid .tt-input').change(function() {
            $(this).parent().removeClass('has-error');          
        });
    }
    
    $('#$mid').modal('toggle');
    
    return false;
JS;

$buttons = Html::button(FA::icon(FA::_PLUS), ['class' => 'btn btn-primary btn-sm', 'title' => 'To category', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'onClick' => $onClick]);

// pub & del

$tooltip = [
    'data-toggle' => 'tooltip',
    'data-placement' => 'top'
];

$buttons_list = [
    [
        'status' => Movie::STATUS_PUB,
        'title' => 'Publish',
        'class' => 'success',
        'icon' => FA::_UPLOAD,
        'confirm' => 'Are you really want to publish #N movies?'
    ],
    [
        'status' => Movie::STATUS_DEL,
        'title' => 'Remove',
        'class' => 'danger',
        'icon' => FA::_TRASH,
        'confirm' => 'Are you really want to remove #N movies?'
    ]
];

foreach ($buttons_list as $params) {
    $status = Arr::get($params, 'status');
    $title = Arr::get($params, 'title');
    $class = Arr::get($params, 'class');
    $icon = Arr::get($params, 'icon');
    $confirm = Arr::get($params, 'confirm');

    $fid = '#' . ActiveForm::begin(['id' => 'bulk-category-status-' . $status . '-form'])->getId();

    echo Html::hiddenInput('hasEditable', 1);
    echo Html::hiddenInput('editableIndex', 0);
    echo Html::hiddenInput('editableAttribute', 'status');
    echo Html::hiddenInput('Movie[0][status]', $status);

    $save = <<< JS
            selected = $(this).parents('.grid-view').yiiGridView('getSelectedRows');

            if (confirm('$confirm'.replace('#N', selected.length))) {
                $.ajax({
                    context: this,
                    method: 'POST',
                    data: $('$fid').serialize() +'&'+ $.param({editableKey: selected}),
                    success: function(data) {
                        $(this).parents('.grid-view').yiiGridView('applyFilter');
                    }
                });
            }
            return false;
JS;


    $buttons .= Html::button(FA::icon($icon), array_merge($tooltip, ['class' => 'btn btn-' . $class . ' btn-sm', 'title' => $title, 'onClick' => $save]));
    ActiveForm::end();
}

?>

<div class="grid-view-header">
    <div class="row">
        <div class="col-md-6">
            <div class="bulk-buttons"><?= $buttons ?>
                <div class="count-selected-text"><span class="count-selected"></span> movies</div>
            </div>
        </div>
        <div class="col-md-6">{pager} {summary}</div>
    </div>
</div>
{items}
<div class="grid-view-footer">
    <div class="row">
        <div class="col-md-6">
            <div class="bulk-buttons"><?= $buttons ?>
                <div class="count-selected-text"><span class="count-selected"></span> movies</div>
            </div>
        </div>
        <div class="col-md-6">{pager} {summary}</div>
    </div>
</div>