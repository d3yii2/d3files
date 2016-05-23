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
        
    function showError(data)
    {
        $('#d3files-alert').remove();
        
        var html = '<div id="d3files-alert" class="alert alert-danger alert-dismissible" role="alert" style="margin: 0; margin-bottom: 1px;">';
        html += '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
        html += '<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> ';
        html += '<strong>' + data.status + '</strong> ';
        html += '<span class="sr-only">' + data.name + '</span> ';
        html += data.message;
        html += '</div>';
        
        $('#d3files-widget').prepend(html);
    }
    
    $(document).on('click', '.d3files-delete', function(e) {
        
        $('#d3files-alert').remove();
        
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
                if (!tbl.find('tr').length) {
                    addEmptyRow();
                }
            },
            error: function(xhr) {
                showError(xhr.responseJSON);
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
        $('#d3files-alert').remove();
        
        var url = '$uploadUrl';
        var xhr = new XMLHttpRequest();
        var fd  = new FormData();
        xhr.open('POST', url, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                var response = $.parseJSON(xhr.responseText);
        
                if (xhr.status == 200) {
                    tbl.find('div.empty').closest('tr').remove();
                    tbl.append(response);
                } else {
                    showError(response);
                }
            }
        };
        fd.append('model_name', '$model_name');
        fd.append('_csrf', yii.getCsrfToken());
        fd.append('upload_file', file);
        xhr.send(fd);
    }
    
    // Check for the File API support.
    if (!window.File) {
        $('#d3files-drop-zone').hide();
    } else {
        function handleFileSelect(e) {
            e.stopPropagation();
            e.preventDefault();
            handleDragLeave();
            var file = e.dataTransfer.files[0];
            uploadFile(file);
        }

        function handleDragOver(e) {
            e.stopPropagation();
            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy'; // Explicitly show this is a copy.
            $('#d3files-drop-zone').css('border-color', '#555');
            $('#d3files-drop-zone').css('color', '#555');
        }
        
        function handleDragLeave() {
            $('#d3files-drop-zone').css('border-color', '#bbb');
            $('#d3files-drop-zone').css('color', '#bbb');
        }

        // Setup the dnd listeners.
        var dropZone = document.getElementById('d3files-drop-zone');
        dropZone.addEventListener('dragover', handleDragOver, false);
        dropZone.addEventListener('dragleave', handleDragLeave, false);
        dropZone.addEventListener('drop', handleFileSelect, false);
    }
});
JS;

$this->registerJs($script, View::POS_END);
?>
<div id="d3files-widget">
<table class="table table-striped table-bordered" style="margin-bottom: 0px; border-bottom: 0;">
    <?php
    if (!$hideTitle) {
        ?>
        <tr style="border-bottom: 0;">
            <th style="border-bottom: 0;">
                <span class="<?php echo $icon; ?>"></span>
                <?php echo $title; ?>
                <label style="margin: 0; margin-left: 5px;" title="Add">
                    <input type="file" id="d3file-input" style="display: none;" />
                    <span class="glyphicon glyphicon-plus text-primary" style="cursor: pointer;"></span>
                </label>
            </th>
        </tr>
    <?php
    }
    ?>
    <tr style="border-bottom: 0;">
        <td style="padding: 0; border-bottom: 0;">
            <div id="d3files-drop-zone" title="Drag&Drop a file here, upload will start automatically" style="border: 2px dashed #bbb; color: #bbb; text-align: center; padding: 8px;">
                <span class="glyphicon glyphicon-cloud-upload"></span>
                Drag&Drop file here
            </div>
        </td>
    </tr>
</table>
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
            'contentOptions' => ['class' => 'col-xs-11'],
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
            'contentOptions' => ['class' => 'text-center col-xs-1'],
        ],

    ],
    'rowOptions' => [
        'class' => 'd3files-row',
    ],
]); ?>
</div>