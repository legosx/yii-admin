<?php

use kartik\grid\GridView;
use rmrevin\yii\fontawesome\FA;
use kartik\editable\Editable;
use app\components\Arr;
use app\models\Movie;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\Movie */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Category list';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="category-index big-grid container">
    <?php

    $editable = [
        'submitOnEnter' => false,
        'asPopover' => false,
        'inputType' => Editable::INPUT_TEXTAREA,
        'inlineSettings' => [
            'templateBefore' => "",
            'templateAfter' => <<< HTML
    <button class="btn btn-xs btn-primary kv-editable-submit"><i class="fa fa-floppy-o"></i> Save</button>
    <button class="btn btn-xs kv-editable-close kv-editable-reset"><i class="fa fa-times"></i> Cancel</button>
HTML
        ],
        'valueIfNull' => '(n/a)',
        'editableValueOptions' => [
            'class' => 'text-left'
        ]
    ];

    $editableLine = Arr::merge($editable, [
        'inputType' => Editable::INPUT_TEXT
    ]);

    $change_checkbox_js = <<< JS
    if (selected = $(this).parents('.grid-view').yiiGridView('getSelectedRows').length) {
        $(this).parents('.grid-view').find('.bulk-buttons .count-selected').html(selected);
        $(this).parents('.grid-view').find('.bulk-buttons').show();
    } else {
        $(this).parents('.grid-view').find('.bulk-buttons').hide();    
    }
JS;

    GridView::begin([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'condensed' => true,
        'pjax' => true,
        'options' => [
            'class' => 'grid-view category-grid'
        ],
        'layout' => $this->render('grid/layout'),
        'summary' => '<div class="summary"><b>{begin, number}-{end, number}</b> of <b>{totalCount, number}</b></div>',
        'pager' => [
            'maxButtonCount' => 5,
            'nextPageLabel' => FA::icon(FA::_CHEVRON_RIGHT),
            'prevPageLabel' => FA::icon(FA::_CHEVRON_LEFT)
        ],
        'columns' => [
            [
                'class' => '\kartik\grid\CheckboxColumn',
                'rowSelectedClass' => GridView::TYPE_INFO,
                'contentOptions' => [
                    'onChange' => $change_checkbox_js
                ],
                'checkboxOptions' => [
                    'onClick' => "if (!$(this).hasClass('good-click')) { return false; }",
                    'onMouseOver' => "$(this).addClass('good-click')",
                    'onMouseOut' => "$(this).removeClass('good-click')"
                ],
                'headerOptions' => [
                    'onChange' => $change_checkbox_js
                ]
            ],
            [
                'class' => 'kartik\grid\EditableColumn',
                'attribute' => 'title',
                'editableOptions' => $editableLine,
                'headerOptions' => [
                    'class' => 'title-col'
                ],
            ],
            [
                'format' => 'raw',
                'filter' => $this->render('/common/category-typeahead', [
                    'name' => 'Movie[category]',
                    'tid' => 'movie-category-filter',
                    'fname' => 'movie_category_filter_init',
                    'value' => Arr::get(Yii::$app->request->get('Movie'), 'category')
                ]),
                'label' => 'Category',
                'value' => function ($data) {
                    /** @var Movie $data */
                    return $data->api_name ? '' : $this->render('/common/category', ['data' => $data]);
                },
                'headerOptions' => [
                    'class' => 'category-col'
                ],
                'contentOptions' => [
                    'class' => 'category-cell'
                ],
                'filterOptions' => [
                    'class' => 'category-filter'
                ]
            ],
            [
                'format' => 'raw',
                'label' => 'Size',
                'value' => function ($data) {
                    return $this->render('grid/size', ['data' => $data]);
                }
            ],
            [
                'format' => 'raw',
                'attribute' => 'api_name',
                'value' => function ($data) {
                    return $data->api_name ? $data->api_name : '';
                }
            ],
            [
                'label' => 'Actions',
                'format' => 'raw',
                'value' => function ($data) {
                    return $this->render('grid/actions', ['data' => $data]);
                },
                'headerOptions' => [
                    'class' => 'actions-col'
                ],
                'contentOptions' => [
                    'class' => 'actions-cell'
                ]
            ],
        ]
    ]);
    $grid = GridView::end();

    $pjax_id = Arr::get($grid->pjaxSettings, 'options.id');
    $grid_id = $grid->getId();

    $js = <<< JS
        $(function () {
            $('[data-toggle="tooltip"]').tooltip({trigger: 'hover'});
        })
        create_category_form_init();
    
        $('#{$pjax_id}').on('pjax:end', function() {
            $(function () {
                $('[data-toggle="tooltip"]').tooltip({trigger: 'hover'});
            });
            
            movie_category_filter_init();
            bulk_category_typeahead_init();
            create_category_form_init();
        });
JS;

    $this->registerJs($js);
    ?>
</div>
