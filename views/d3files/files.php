<?php
use yii\helpers\Html;
use yii\web\View;

$uploadUrl = Yii::$app->urlManager->createUrl(
    ['d3files/d3files/upload', 'id' => $model_id]
);

$t_aria_label = Yii::t('d3files', 'Close');
$t_confirm    = Yii::t('d3files', 'Are you sure you want to delete this item?');
$t_no_results = Yii::t('d3files', 'No results found.');

$script = <<< JS

$(function(){
    
    function showError(data, el)
    {
        $('.d3files-alert').remove();
        
        var html = '<div class="d3files-alert alert alert-danger alert-dismissible" role="alert" style="margin: 0; margin-bottom: 1px;">';
        html += '<button type="button" class="close" data-dismiss="alert" aria-label="$t_aria_label"><span aria-hidden="true">&times;</span></button>';
        html += '<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> ';
        html += '<strong>' + data.status + '</strong> ';
        html += '<span class="sr-only">' + data.name + '</span> ';
        html += data.message;
        html += '</div>';
        
        el.prepend(html);
    }
    
    $(document).on('click', '.d3files-delete', function(e) {
        
        $('.d3files-alert').remove();
        
        if (!confirm('$t_confirm')) {
            return false;
        }
        
        var url = $(this).attr('href');
        var tbl = $(this).closest('table');
        var row = $(this).closest('tr');
        var el  = $(this).closest('.d3files-widget');
        
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
                showError(xhr.responseJSON, el);
            }
        });
        
        function addEmptyRow() {
            var html = '<tr><td colspan="2"><div class="empty">$t_no_results</div></td></tr>';
            tbl.append(html);
        }
        
        return false;
    });
        
    $('.d3file-input').on('change', function() {
        uploadFile(this.files[0], $(this).closest('.d3files-widget'));
        return false;
    });
    
    function uploadFile(file, el) {
        $('.d3files-alert').remove();
        
        var tbl = el.find('table.d3files-table');
        
        var url = el.find('.d3file-input').attr('data-url');
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
                    showError(response, el);
                }
            }
        };
        fd.append('model_name', el.find('.d3file-input').attr('name'));
        fd.append('_csrf', yii.getCsrfToken());
        fd.append('upload_file', file);
        xhr.send(fd);
    }
    
    // Check for the File API support.
    if (!window.File) {
        $('.d3files-drop-zone').hide();
    } else {
        function handleFileSelect(e) {
            e.stopPropagation();
            e.preventDefault();
            handleDragLeave(e);
            var file = e.dataTransfer.files[0];
            uploadFile(file, $(e.target).closest('.d3files-widget'));
        }

        function handleDragOver(e) {
            e.stopPropagation();
            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy'; // Explicitly show this is a copy.
            $(e.target).css('border-color', '#555');
            $(e.target).css('color', '#555');
        }
        
        function handleDragLeave(e) {
            $(e.target).css('border-color', '#bbb');
            $(e.target).css('color', '#bbb');
        }

        // Setup the dnd listeners.
        $('.d3files-drop-zone').each(function() {
            var dropZone = $(this)[0];
        
            dropZone.addEventListener('dragover', handleDragOver, false);
            dropZone.addEventListener('dragleave', handleDragLeave, false);
            dropZone.addEventListener('drop', handleFileSelect, false);
        });
        
    }
});
JS;

$this->registerJs($script, View::POS_END, 'd3files');
?>
<div class="d3files-widget">
<table class="table table-striped table-bordered" style="margin-bottom: 0px; border-bottom: 0;">
    <?php
    if (!$hideTitle) {
        ?>
        <tr style="border-bottom: 0;">
            <th style="border-bottom: 0;">
                <span class="<?php echo $icon; ?>"></span>
                <?php echo $title; ?>
                <label style="margin: 0; margin-left: 5px;" title="<?php echo Yii::t('d3files', 'Add'); ?>">
                    <input type="file" class="d3file-input" style="display: none;" data-url="<?php echo $uploadUrl; ?>" name="<?php echo $model_name; ?>" />
                    <span class="glyphicon glyphicon-plus text-primary" style="cursor: pointer;"></span>
                </label>
            </th>
        </tr>
    <?php
    }
    ?>
    <tr style="border-bottom: 0;">
        <td style="padding: 0; border-bottom: 0;">
            <div class="d3files-drop-zone" title="<?php echo Yii::t('d3files', 'Drag&Drop a file here, upload will start automatically'); ?>" style="border: 2px dashed #bbb; color: #bbb; text-align: center; padding: 8px;">
                <span class="glyphicon glyphicon-cloud-upload"></span>
                <?php echo Yii::t('d3files', 'Drag&Drop file here'); ?>
            </div>
        </td>
    </tr>
</table>
<table class="d3files-table table table-striped table-bordered">
<?php

foreach ($fileList as $row){
    ?>
    <tr>
        <td class="col-xs-11">
            <?=Html::a(
                    $row['file_name'],
                    ['/d3files/d3files/download', 'id' => $row['id']],
                    ['title' => Yii::t('d3files', 'Download')]
                )?>
        </td>
        <td class="text-center col-xs-1">
            <?=Html::a(
                    '<span class="glyphicon glyphicon-trash"></span>',
                    ['/d3files/d3files/delete', 'id' => $row['file_model_id']],
                    ['class' => 'd3files-delete', 'title' => Yii::t('d3files', 'Delete')]
                );?>
        </td>
    </tr>
    <?php
}
?>        
</table>    

</div>
