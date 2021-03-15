/*jslint browser */
/*globals document, window, $, FormData, D3FilesVars, confirm, console*/
/*global document, window, $, FormData, D3FilesVars, confirm, console*/

/*jshint esversion: 6 */

function clearAlert(widget) {
    "use strict";
    let alert = widget.find(".d3files-alert");
    alert.remove();
}

function showError(response, widget) {
    "use strict";
    let html;
    let ariaLabel = D3FilesVars.i18n.aria_label;
    clearAlert(widget);
    html = "<div class=\"d3files-alert alert alert-danger alert-dismissible\" role=\"alert\">";
    html += "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"" + ariaLabel + "\">" +
            "<span aria-hidden=\"true\">&times;</span>" +
            "</button>";
    html += "<span class=\"glyphicon glyphicon-exclamation-sign\" aria-hidden=\"true\"></span> ";
    //html += "<strong>" + response.status + "</strong> ";
    //html += "<span class=\"sr-only\">" + response.name + "</span> ";
    html += response.msg;
    html += "</div>";

    widget.prepend(html);
}

function showSuccess(response, widget) {
    "use strict";
    let html;
    let ariaLabel = D3FilesVars.i18n.aria_label;
    clearAlert(widget);

    html = "<div class=\"d3files-alert alert alert-success alert-dismissible\" role=\"alert\">";
    html += "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"" + ariaLabel + "\">" +
            "<span aria-hidden=\"true\">&times;</span>" +
            "</button>";
    html += "<span class=\"glyphicon glyphicon-exclamation-sign\" aria-hidden=\"true\"></span> ";
    html += response.msg;
    html += "</div>";

    widget.prepend(html);
}

function uploadFile(file, widget) {
    "use strict";
    let form;
    let fileInputName;
    let tbl;
    let hasPreview;
    let url;
    let fd;
    let fileInput;

    tbl = widget.find("table.d3files-table");

    if (0 === tbl.length) {
        console.log("D3Files error: cannot find the table");
        return false;
    }
    form = widget.find("form");

    if (0 === form.length) {
        console.log("D3Files error: cannot find the form");
        return false;
    }

    clearAlert(widget);

    hasPreview = widget.data("type");
    fileInput = form.find(".d3file-input");
    url = fileInput.attr("data-url");
    if (hasPreview) {
        url += "&preview=1";
    }
    fileInputName = fileInput.data("model_name");
    fd = new FormData(form[0]);
    fd.append("model_name", fileInputName);
    if ("undefined" !== typeof file) {
        fd.append("upload_file", file);
    }

    $.ajax({
        url: url,
        type: "POST",
        data: fd,
        cache: false,
        processData: false,  // tell jQuery not to process the data
        contentType: false,  // tell jQuery not to set contentType
        success: function (data) {
            //let closestRow = emptyDiv.closest("tr");
            //closestRow.remove();
            tbl.append(data.content);
            showSuccess(data, widget);
            if ("undefined" !== typeof document.D3FP) {
                document.D3FP.reflow();
            }
        },
        failed: function (data) {
            showError(data, widget);
            console.log(data);
        },
        error: function (xhr) {
            let response = $.parseJSON(xhr.responseText);
            console.log(response);
            if (response) {
                showError(response, widget);
                console.log(response.error);
            } else {
                // This would mean an invalid response from the server - maybe the site went down or whatever...
                showError({msg: "Unexpected server error"}, widget);
            }
        }
    });
}

function handleDragLeave(e) {
    "use strict";
    let targetElement = $(e.target);
    targetElement.css("border-color", "#bbb");
    targetElement.css("color", "#bbbbbb");
}

function handleFileSelect(e) {
    "use strict";
    let file;
    let targetEl;
    let closestWidget;
    e.stopPropagation();
    e.preventDefault();
    handleDragLeave(e);
    targetEl = $(e.target);
    closestWidget = targetEl.closest(".d3files-widget");
        $.each(e.dataTransfer.files, function (index, file) {
        uploadFile(file, closestWidget);
    });
}

function handleDragOver(e) {
    "use strict";
    let targetElement = $(e.target);
    e.stopPropagation();
    e.preventDefault();
    e.dataTransfer.dropEffect = "copy"; // Explicitly show this is a copy.
    targetElement.css("border-color", "#555");
    targetElement.css("color", "#555555");
}

$(function () {
    "use strict";

    let input;
    let dropZone;
    let d = $(document);
    d.on("click", ".d3files-delete", function () {
        let url;
        let tbl;
        let row;
        let widget = $(this).closest(".d3files-widget");
        let alert = widget.find(".d3files-alert");

        alert.remove();

        if (!confirm(D3FilesVars.i18n.confirm)) {
            return false;
        }

        url = $(this).attr("href");
        tbl = $(this).closest("table");
        row = $(this).closest("tr");

        $.ajax({
            url: url,
            type: "POST",
            data: {},
            success: function (data) {
                let html;
                row.remove();
                if (!tbl.find("tr").length) {
                    html = "" +
                            "<tr>" +
                            "<td colspan=\"2\"><div class=\"empty\">" + D3FilesVars.i18n.no_results + "</div></td>" +
                            "</tr>";
                    tbl.append(html);
                }
                showSuccess(data, widget);
            },
            error: function (xhr) {
                showError(xhr.responseJSON, widget);
            }
        });
        return false;
    });
    
    
    d.on("click", ".d3files-edit-notes", function () {
        let textareaRow = $(this).closest("tr").next("tr.d3files-row-notes");
        textareaRow.toggle();
    });
    
    d.on("click", ".d3files-save-notes", function () {
        let widget = $(this).closest(".d3files-widget");
        let alert = widget.find(".d3files-alert");
        let textarea = $(this).prev(".d3files-notes-field");
        let notes = textarea ? textarea.val() : false;
        let url;

        if (notes) {
            alert.remove();
            url = $(this).data("url");
            
            $.ajax({
                url: url,
                type: "POST",
                data: {notes: notes},
                success: function (data) {
                    showSuccess(data, widget);
                    textarea.closest("tr.d3files-row-notes").hide();
                },
                error: function (xhr) {
                    showError(xhr.responseJSON, widget);
                }
            });
        }
        return false;
    });
    
    input = $(".d3files-widget input[type=\"file\"]");

    input.on("change", function () {
        let widget = $(this).closest(".d3files-widget");
        uploadFile($(this), widget);
    });

    dropZone = $(".d3files-drop-zone");

    // Check for the File API support.
    if (window.File) {
        // Setup the dnd listeners.
        dropZone.each(function () {
            let dropZoneItem = $(this)[0];
            dropZoneItem.addEventListener("dragover", handleDragOver, false);
            dropZoneItem.addEventListener("dragleave", handleDragLeave, false);
            dropZoneItem.addEventListener("drop", handleFileSelect, false);
        });
    } else {
        dropZone.hide();
    }
});
