/*jslint browser */
/*globals D3PDF, console, jQuery, D3FilesPreviewJsVars*/
/*global D3PDF, console, jQuery, D3FilesPreviewJsVars*/
/*jshint esversion: 6 */
/**
 * @param {{file_name:string}} f
 */
(function ($) {
    "use strict";

    $.D3FilesPreview = function () {
        this.gridSelector = '#ThGridViewTable tbody';
        this.handlers = {
            previewButton: $('.d3files-preview-widget-load'),
            previewDropdown: $('.d3files-preview-dropdown'),
            prevButton: $('.d3files-preview-prev-button'),
            nextButton: $('.d3files-preview-next-button'),
            modalContent: $('#th-modal .th-modal-content'),
            modalMessages: $('#th-modal .th-modal-messages'),
            imageContent: $('.d3preview-image-content'),
            filesListContent: $('.d3preview-model-files'),
            counterI: $(".d3preview-counter-i"),
            counterFrom: $(".d3preview-counter-from"),
            counterTotal: $(".d3preview-counter-total"),
            selectRowCheckbox: $(this.gridSelector)
        };
        this.filesList = [];
        this.selectedRows = [];
        this.modelFiles = null;
        this.prevNextBySelection = true;
        this.pdfOptions = {};
        this.previewButtonDataName = 'd3files-preview';
        this.viewByExtension = 'pdf';
        this.prevNextButtons = false;

        this.logErrorPrefix = 'D3FilesPreview error: ';
        this.logWarningPrefix = 'D3FilesPreview warning: ';

        this.activeModel = null;
        this.activeModelIndex = null;
        this.activeFile = null;
        this.i18n = {Selected: "Selected", from: "from"};
    };

    $.D3FilesPreview.prototype = {
        setOption: function(prop, val) {
            this[prop] = val;
        },
        setPdfObject: function(o) {
            if ('undefined' === typeof D3PDF) {
                console.log(this.logErrorPrefix + 'D3PDF is not defined. Missing pdfobject-custom.js ?');
                return false;
            }
            D3PDF.construct(o);
        },
        reflow: function () {
            this.updateFilesList();
            this.initHandlers();
        },
        initHandlers: function () {
            // Make class accesible into event
            let self = this;

            if (this.prevNextButtons) {
                $(this.handlers.prevButton).on('click', function () {
                    self.preview($(this));
                });
                $(this.handlers.nextButton).on('click', function () {
                    self.preview($(this));
                });
            }

            self.handlers.previewButton.on('click', function () {
                self.preview($(this));
            });

            this.handlers.selectRowCheckbox.on('change', function (e) {
                self.togglePreviewIcons(e);
            });
        },
        togglePreviewIcons: function (e) {
            let selectedRows = this.getSelectedRows();
            if (0 < selectedRows.length) {
                this.handlers.previewButton.hide();
                $(selectedRows).each(function () {
                    let btn = $('a[data-model-id=' + this + ']');
                    btn.show();
                });
            } else {
                this.handlers.previewButton.show();
            }
        },
        preview: function (e) {
            this.handlers.modalMessages.empty();
            this.handlers.modalContent.empty();
            try {
                let m = this.getAttachmentData(e);
                let modelI = 0;
                this.selectedRows = this.getSelectedRows();
                if (0 < this.selectedRows.length) {
                    this.handlers.modalMessages.html(this.i18n.Selected + ': ' + this.selectedRows.length);
                    this.setCounterTotal(this.selectedRows.length);
                    modelI = this.getArrIndexByVal(this.selectedRows, m.modelId);
                } else {
                    this.setCounterTotal(this.filesList.length);
                    modelI = this.getModelIndex(m.modelId);
                }
                this.setCounterI(modelI + 1);
                this.activeModel = m;
                let fileId = e.data('file-id'),
                    ma = "undefined" === typeof fileId ? this.getNextActiveFile(m) : this.getFileById(fileId, m.files);

                if (!ma) {
                    throw new Error('Cannot get active file from model');
                }

                this.loadFile(ma);
                this.renderModelFiles(m);
                if (this.prevNextButtons) {
                    if (this.selectedRows.length) {
                        this.initPrevNextButtons(m, this.selectedRows);
                    } else {
                        let allRows = this.getAllRows();
                        this.initPrevNextButtons(m, allRows);
                    }
                }
            } catch (err) {
                console.log(this.logErrorPrefix + "preview() Catch got: " + err);
            }
        },
        setCounterI: function (count) {
            this.handlers.counterI.text(count);
        },
        setCounterFrom: function (text) {
            this.handlers.counterFrom.text(text);
        },
        setCounterTotal: function (count) {
            this.handlers.counterTotal.text(count);
        },
        getFileById: function (id, files) {
            if ("undefined" === typeof id) {
                return null;
            }
            if (0 === files.length) {
                return null;
            }
            let af = null;
            $.each(files, function (i, f) {
                if (parseInt(f.id) === parseInt(id)) {
                    af = f;
                    return false;
                }
            });
            return af;
        },
        getNextActiveFile: function (m) {
            if ("undefined" === typeof m.files) {
                console.log(m);
                throw new Error('getNextActiveFile: missing files property in model:');
            }
            if (0 === m.files.length) {
                return null;
            }
            let af = null,
                fbe = this.getFileByExtension(m.files, this.viewByExtension);
            if (fbe) {
                af = fbe;
            } else {
                let nf = this.getArrNextIndex(m.files, 0);
                af = m.files[nf];
            }
            return af;
        },
        getFileByExtension: function (ml, e) {
            let fe = null,
                self = this;
            $.each(ml, function () {
                let ext = self.getFileExtension(this.file_name);
                if (e === ext) {
                    fe = this;
                    return false;
                }
            });
            return fe;
        },
        getAttachmentData: function (e) {
            let d = e.data(this.previewButtonDataName);
            if ("undefined" === typeof d) {
                throw new TypeError(this.logErrorPrefix + 'missing data attribute: ' + this.previewButtonDataName + ' in element');
            }
            return d;
        },
        loadFile: function (f) {
            let ext = this.getFileExtension(f.file_name);
            if ("pdf" === ext || "PDF" === ext) {
                try {
                    this.handlers.imageContent.html('').hide();
                    this.setPdfObject(this.pdfOptions);
                    this.loadPDF(f);
                    this.activeFile = f;
                    $('.modal-dialog .modal-content').css('height', '80%');
                    return true;
                } catch (err) {
                    throw new Error('loadFile got catch: ' + err);
                }
            }
            if ("png" === ext || "jpg" === ext || "jpeg" === ext || "gif" === ext) {
                this.loadImage(f);
                this.activeFile = f;
                return true;
            }
            if ("txt" === ext) {
                this.loadText(f);
                this.activeFile = f;
                return true;
            }
            throw new Error('Unsupported file type for load: ' + ext);
        },
        loadPDF: function (f) {
            D3PDF.trigger (f.src);
        },
        loadImage: function (f) {
            $('.th-modal-content').empty();
            $('.modal-dialog .modal-content').css('height', 'unset');
            new PhotoViewer([{
                src: f.src
            }]);
            this.handlers.imageContent.show();

        },
        loadText: function (f) {
            $('.th-modal-content').empty();
            $('.modal-dialog .modal-content').css('height', 'unset');
            $('.modal-dialog .th-modal-content').load(f.src);
        },
        getModelIndex: function (modelId) {
            let index = null;
            $.each(this.filesList, function (i, item) {
                if ("undefined" === typeof item.modelId) {
                    throw new Error('getModelIndex Error: undefined m.modelId');
                }
                if (parseInt(modelId) === parseInt(item.modelId)) {
                    index = i;
                    return false;
                }
            });
            return index;
        },
        getFilesListFromSelected: function(s) {
            let l = [];
            $(s).each(function () {
                l.push($(this).val());
            });
            return l;
        },
        getArrIndexByVal: function (arr, val) {
            let index = null;
            $.each(arr, function (i, iv) {
                if (parseInt(val) === parseInt(iv)) {
                    index = i;
                    return false;
                }
            });
            return index;
        },
        getArrNextIndex: function (arr, index) {
            if (arr.length === 0) {
                return null;
            }
            let k = index + 1;
            if (k > arr.length) {
                return null;
            }
            if(-1 === arr[k]) {
                return null;
            }
            return k;
        },
        getArrNextValue: function(arr, val) {
            var next = $.inArray(val, arr) + 1;
            if (next < arr.length) {
                let nextVal = arr[next];
                return nextVal;
            } else {
                return null;
            }
        },
        getArrPrevValue: function(arr, val) {
            var prv = $.inArray(val, arr) - 1;
            if (prv >= 0) {
                let prevVal = arr[prv];
                return prevVal;
            }
            else {
                return null;
            }
        },
        updateFilesList: function () {
            this.filesList = this.buildFilesList();
        },
        setFilesList: function (l) {
            this.filesList = l;
        },
        getFilesList: function () {
            return this.filesList;
        },
        getModelFilesList: function (id) {
            return  $('#d3files-preview-button-' + id).data('d3files-files');
        },
        /* Get the array of the files from selected rows */
        getSelectedRows: function () {
            let s = [],
                rows = $(this.gridSelector + ' input[type="checkbox"]:checked');
            // For testing
            /*if (!rows.length) {
                throw new Error('Selected elements not found by: ' + this.gridSelector);
            }*/
            rows.each(function () {
                let mid = parseInt($(this).val());
                if ($("a[data-model-id=" + mid + "]").length > 0) {
                    s.push(mid);
                }
            });
            return s;
        },
        getAllRows: function () {
            let s = [],
              rows = $(this.gridSelector + ' a.d3files-preview-widget-load');
            rows.each(function () {
                let mid = parseInt($(this).data("model-id"));
                s.push(mid);
            });
            return s;
        },
        /* Build the filesList */
        buildFilesList: function () {
            let r = [],
                self = this;
            $('*[data-' + this.previewButtonDataName + ']').each(function () {
                let d = self.getAttachmentData($(this));
                if (d) {
                    r.push(d);
                }
            });
            return r;
        },
        getModelViewFilesByExt: function(f) {
            let wf = [];
            f.each(function (f) {
                if ("undefined" === typeof f.file_name) {
                    console.log(this.logErrorPrefix + 'Cannot get the file name from:' + f);
                }
                let fe = this.getFileExtension(f.file_name);
                if (this.viewByExtension === fe) {
                    wf.push();
                }
            });
            return wf;
        },
        getFileExtension: function(name) {
           // return name.split(/\#|\?/)[0].split('.').pop().trim().toLowerCase();
            return name.substr( (name.lastIndexOf('.') +1) ).toLowerCase();
        },
        initFilesListDropdown: function(m) {
            if ("undefined" === m.files) {
                return false;
            }
            this.handlers.previewDropdown.empty();
            let self = this;

            $.each(m.files, function(i, f) {
                self.handlers.previewDropdown.append(
                    $('<option></option>').val(i).html(f.file_name)
                );
            });
        },
        objectHasOneItem: function(o) {
            return 1 === $.map(o, function(n, i) { return i; }).length;
        },
        renderModelFiles: function(m) {
            this.handlers.filesListContent.html('');

            //Just ignore if only one file there
            if (this.objectHasOneItem(m.files)) {
                return true;
            }

            let ul = $("<ul style='list-style-type: none'></ul>"),
                self = this;
            $.each(m.files, function (i, f) {
                let li = $('<li style="display: inline-block"></li>'),
                    icon = 'pdf' === self.getFileExtension(f.file_name) ? 'pdf' : 'img',
                    a = $(
                        '<a href="javascript:void(0)" class="d3files-preview-widget-load" title="' + f.file_name + '">' +
                        '<img src="' + D3FilesPreviewJsVars.assetUrl + '/img/' + icon + '-icon.png"  style="width:40px;height:40px"><br>' +
                '</a>'
                    );
                self.setLoadButtonAttrs(a, m);
                a.on('click', function () {
                    self.loadFile(f);
                });
                li.append(a);
                ul.append(li);
            });
            this.handlers.filesListContent.html(ul);
        },
        getNextData: function(modelId, arr) {
            let nextModelId = this.getArrNextValue(arr, modelId);
            if (! nextModelId) {
                return null;
            }
            let data = $("a[data-model-id=" + nextModelId + "]").data(this.previewButtonDataName);
            return data;
        },
        getPrevData: function(modelId, arr) {
            let prevModelId = this.getArrPrevValue(arr, modelId);
            if (! prevModelId) {
                return null;
            }
            let data = $("a[data-model-id=" + prevModelId + "]").data(this.previewButtonDataName);
            return data;
        },
        initPrevNextButtons: function(m, rows) {
            try {
                this.handlers.nextButton.hide();
                this.handlers.prevButton.hide();
                let modelId = parseInt(m.modelId);
                let nms = this.getNextData(modelId, rows);
                if (nms) {
                    this.setLoadButtonAttrs(this.handlers.nextButton, nms);
                    this.handlers.nextButton.show();
                }
                let pms = this.getPrevData(modelId, rows);
                if (pms) {
                    this.setLoadButtonAttrs(this.handlers.prevButton, pms);
                    this.handlers.prevButton.show();
                }
            } catch (e) {
                throw new Error( "initPrevNextButtons Catch got: " + e);
            }
        },
        setLoadButtonAttrs: function(b, f) {
            b.data(this.previewButtonDataName, f);
            //b.attr('data-' + this.previewButtonDataName, f);
        },
        getFirstPreviewItem: function(ext) {
            let firstItem;
            let self = this;
            
            if (!ext) {
                ext = 'pdf';
            }
            
            $.each(this.getFilesList(), function (i, item) {
                var file = self.getFileByExtension(item.files, ext);
                if (file) {
                    firstItem = $("a[data-file-id='" + file.id + "']");
                    return false;
                }
            });
            return firstItem;
        },
        clickFirstPreviewItem: function(ext) {
            let fpi = this.getFirstPreviewItem(ext);
            if (!fpi) {
                return false;
            }
            fpi.click();
        }
    };
    $(document).on('pjax:success', function() {
        if ("undefined" !== typeof document.D3FP) {
            document.D3FP.reflow();
        }
    });
}(jQuery));
