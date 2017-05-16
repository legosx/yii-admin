<?php
/** @var \yii\web\View $this */

use app\models\Movie;
use rmrevin\yii\fontawesome\FA;
use app\components\Arr;
use kartik\widgets\ActiveForm;
use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use app\components\Controller;
use app\models\forms\CreateCategoryForm;
use kartik\form\ActiveFormAsset;

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
    
    selected = $(this).parents('.category-index').find('.category-grid').yiiGridView('getSelectedRows');

    $.ajax({
        context: this,
        method: 'POST',
        data: $('$fid').serialize() +'&'+ $.param({editableKey: selected}) + '&Movie[0][category]=' + cat_id,
        success: function(data) {
            $('#$mid').modal('toggle');
            $(this).parents('.category-index').find('.category-grid').yiiGridView('applyFilter');
        }
    });
    
    return false;
JS;

Modal::begin([
    'header' => 'Add Category',
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
        'status' => Movie::STATUS_DEL,
        'title' => 'Remove',
        'class' => 'danger',
        'icon' => FA::_TRASH,
        'confirm' => 'Are you really want to delete #N categories?'
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
    echo Html::hiddenInput('editableAction', Controller::ACTION_REMOVE_CATEGORY);

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

$nmid = 'create-category-modal';
$nfid = 'create-category-form';
$nfid_name = 'createcategoryform-name';

Modal::begin([
    'header' => 'New category',
    'options' => [
        'id' => $nmid,
        'class' => 'modal'
    ],
    'footer' => Html::button('Save', ['class' => 'btn btn-primary', 'onClick' => "$('#$nfid').submit(); return false;"]) . Html::button('Cancel', ['class' => 'btn', 'data-dismiss' => 'modal'])
])->setId($nmid);

$model = new CreateCategoryForm;
?>

<?php
ActiveFormAsset::register($this);
$form = ActiveForm::begin([
    'id' => $nfid,
    'action' => Url::to(['category/create']),
    'options' => ['class' => $nfid],
    'type' => ActiveForm::TYPE_INLINE,
    'validateOnType' => false,
    'validateOnChange' => false,
    'validateOnBlur' => false,
    'enableAjaxValidation' => false,
    'formConfig' => [
        'showErrors' => true
    ]
]); ?>
<div class="row">
    <?= $form->field($model, 'name', ['options' => ['class' => 'col-xs-12']])->textInput(); ?>
</div>
<?php
ActiveForm::end();

Modal::end();

$onClick = <<< JS
    $('#$nmid').modal('toggle');
    
    if (!$('#$nmid').hasClass('category-init')) {
        $('#$nmid').on('hide.bs.modal', function() {
            $('#$nfid')[0].reset();
        });
        $('#$nmid').addClass('category-init');
    }
    
    return false;
JS;

$addButton = Html::button(FA::icon(FA::_FILE), array_merge($tooltip, ['class' => 'btn btn-success btn-sm', 'title' => 'New category', 'onClick' => $onClick]));

?>

<script type="text/javascript">
    function create_category_form_init() {
        if (!$('#<?= $nfid?>').data('yiiActiveForm')) {
            $('#<?= $nfid?>').yiiActiveForm([{
                "id": "<?= $nfid_name ?>",
                "name": "name",
                "container": ".field-<?= $nfid_name ?>",
                "input": "#<?= $nfid_name ?>",
                "validateOnChange": false,
                "validateOnBlur": false,
                "validate": function (attribute, value, messages, deferred, $form) {
                    value = yii.validation.trim($form, attribute, []);
                    yii.validation.required(value, messages, {"message": "Category name required"});
                }
            }], []);
        }

        $('#<?= $nfid ?>').on('submit', function (e) {
            if ($('#<?= $nfid ?>').data('yiiActiveForm').validated) {
                $.ajax({
                    url: $('#<?= $nfid ?>').attr('action'),
                    data: $('#<?= $nfid ?>').serialize(),
                    type: $('#<?= $nfid ?>').attr('method'),
                    context: this,
                    success: function () {
                        $('#<?= $nmid ?>').modal('toggle');
                        $(this).parents('.category-index').find('.category-grid').yiiGridView('applyFilter');
                    }
                });
            }
            e.preventDefault();
            return false;
        });
    }
</script>

<div class="grid-view-header">
    <div class="row">
        <div class="col-md-6">
            <div class="add-button pull-left">
                <?= $addButton ?>
            </div>
            <div class="bulk-buttons pull-left"><?= $buttons ?>
                <div class="count-selected-text"><span class="count-selected"></span> categories</div>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="col-md-6">{pager} {summary}</div>
    </div>
</div>
{items}
<div class="grid-view-footer">
    <div class="row">
        <div class="col-md-6">
            <div class="add-button pull-left">
                <?= $addButton ?>
            </div>
            <div class="bulk-buttons pull-left"><?= $buttons ?>
                <div class="count-selected-text"><span class="count-selected"></span> categories</div>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="col-md-6">{pager} {summary}</div>
    </div>
</div>