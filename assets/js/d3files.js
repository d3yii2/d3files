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
