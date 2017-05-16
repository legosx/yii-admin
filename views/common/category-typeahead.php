<?php
/**
 * @var string $name
 * @var string $fid
 * @var string $tid
 * @var string $value
 * @var string $fname
 * @var string $id
 */

$cid = 'movie_category_map'; // storage typeahead
$cids = 'movie_category_map_sub'; // storage typeahead subcategories

use kartik\widgets\Typeahead;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\helpers\Json;

$prepare = <<< JS
    function(query, settings) {
        query = query.replace(new RegExp(' / ', 'g'), ';');
        settings.url = settings.url.replace('%QUERY', query);
        return settings;
    }
JS;

$existCheck = '';

if (isset($fid)) {
    $existCheck = <<< JS
    $('$fid input[type=checkbox][name="Movie[0][category][]"]').each(function() {
        current.push($(this).val());          
    });
JS;

    if (isset($id)) {
        $existCheck .= <<< JS
        current.push('$id');
JS;
    }
}

$transform = <<< JS
    function(response) {
        if (!('$cid' in window)) {
            window.$cid = [];
        }
        
        if (!('$cids' in window)) {
            window.$cids = [];
        }
        
        var current = [];
        
        $existCheck
    
        return $.map(response, function(key, value) {
            var arr = window.$cid, sub = window.$cids;

            if (key.endsWith(";")) {
                key = key.slice(0, -1);
                sub[key] = value;
            }
            
            arr[key] = value;
            
            if (current.indexOf(value) !== -1) {
                return null;
            }
            
            key = key.replace(new RegExp(';', 'g'), ' / ');
            
            return key;
        });
    }
JS;

$dataset = [
    'remote' => [
        'url' => Url::to(['movie/categories']) . '?q=%QUERY',
        'wildcard' => '%QUERY',
        'prepare' => new JsExpression($prepare),
        'transform' => new JsExpression($transform)
    ],
    'limit' => 1000
];

$typeahead_select = <<< JS
    function(e,name) {
        var sub = window.$cids;
        name = name.replace(new RegExp(' / ', 'g'), ';');
        if (sub[name]) {
            $('#$tid').data('ttTypeahead').menu.update(name + ';');
            setTimeout("$('#$tid').data('ttTypeahead').open()", 300);
        }
    }
JS;


$options = [
    'name' => $name,
    'id' => $tid,
    'pluginOptions' => [
        'highlight' => true,
        'minLength' => 0
    ],
    'pluginEvents' => [
        "typeahead:select" => $typeahead_select,
    ],
    'dataset' => [
        $dataset
    ]
];

if (isset($value)) {
    $options['value'] = $value;
}

echo Typeahead::widget($options);

if (isset($fname)) {
    $data_1 = str_replace('-', '_', $tid) . '_data_1';
    ?>
    <script type="text/javascript">
        function <?= $fname ?>() {
            var <?= $data_1 ?> =
            new Bloodhound({
                "datumTokenizer": Bloodhound.tokenizers.whitespace,
                "queryTokenizer": Bloodhound.tokenizers.whitespace,
                "remote":<?= Json::encode($dataset['remote'])?>
            });
            kvInitTA('<?= $tid ?>', window[$('#<?= $tid ?>').attr('data-krajee-typeahead')], [{
                "limit":<?= $dataset['limit'] ?>,
                "name": "<?= $data_1 ?>",
                "source":<?= $data_1 ?>.ttAdapter()
            }]);

            jQuery("#<?= $tid ?>").on('typeahead:select', <?= $typeahead_select ?>);
        }
    </script>
    <?php
}
?>
