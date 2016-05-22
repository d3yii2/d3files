<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\View;

$uploadUrl = Yii::$app->urlManager->createUrl(
    ['d3files/d3files/upload', 'id' => $model_id]
);

$script = <<< JS
$(function(){
    var tbl = $('#d3files-table');
        
    $(document).on('click', '.d3files-delete', function(e) {
        
        if (!confirm('Are you sure you want to delete this item?')) {
            return false;
        }
        
        var url = $(this).attr('href');
        var row = $(this).closest('tr');
        
        $.ajax({
            url:     url,
            type:    'POST',
            data:    {},
            success: function(data) {
                row.remove();
                var count = tbl.find('tr').length;
                if (!count) {
                    addEmptyRow();
                }
            }
        });
        
        function addEmptyRow() {
            var html = '<tr><td colspan="2"><div class="empty">No results found.</div></td></tr>';
            tbl.append(html);
        }
        
        return false;
    });
        
    $('#d3file-input').on('change', function() {
        uploadFile(this.files[0]);
        return false;
    });
    
    function uploadFile(file) {
        var url = '$uploadUrl';
        var xhr = new XMLHttpRequest();
        var fd  = new FormData();
        xhr.open("POST", url, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                // Every thing ok, file uploaded
                tbl.find('div.empty').closest('tr').remove();
                tbl.append(xhr.responseText);
            }
        };
        fd.append("model_name", "$model_name");
        fd.append("_csrf", yii.getCsrfToken());
        fd.append("upload_file", file);
        xhr.send(fd);
    }
});
JS;

$this->registerJs($script, View::POS_END);
?>
<?php 
if (!$hideTitle) {
    ?>
    <table class="table table-striped table-bordered" style="margin-bottom: 0px; border-bottom: 0px;">
        <tr style="border-bottom: 0px;">
            <th colspan="2" style="border-bottom: 0px;">
                <span class="<?php echo $icon; ?>"></span>
                <?php echo $title; ?>
                <label style="margin: 0; margin-left: 5px;" title="Add">
                    <input type="file" id="d3file-input" style="display: none;" />
                    <span class="glyphicon glyphicon-plus text-primary" style="cursor: pointer;"></span>
                </label>
            </th>
        </tr>
    </table>
<?php
}
?>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'summary'      => '',
    'showHeader'   => false,
    'tableOptions' => [
        'id'    => 'd3files-table',
        'class' => 'table table-striped table-bordered'
    ],
    'columns'      => [
        [
            'format' => 'raw',
            'value'  => function ($data) {
                return Html::a(
                    $data->file_name,
                    ['d3files/d3files/download', 'id' => $data->id],
                    ['title' => 'Download']
                );
            },
        ],
        [
            'format' => 'raw',
            'value'  => function ($data) {
                return Html::a(
                    '<span class="glyphicon glyphicon-trash"></span>',
                    ['d3files/d3files/delete', 'id' => $data->id],
                    ['class' => 'd3files-delete', 'title' => 'Delete']
                );
            },
        ],

    ],
    'rowOptions' => [
        'class' => 'd3files-row',
    ],
]); ?>