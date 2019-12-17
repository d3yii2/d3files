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
        this.handlers = {
            previewDropdown: $('.d3files-preview-dropdown'),
            prevButton: $('.d3files-preview-prev-button'),
            nextButton: $('.d3files-preview-next-button'),
            modalContent: $('#th-modal .th-modal-content'),
            modalMessages: $('#th-modal .th-modal-messages'),
            imageContent: $('.d3preview-image-content'),
            filesListContent: $('.d3preview-model-files')
        };
        this.selectedElementsQuery = '#ThGridViewTable tbody input[type="checkbox"]:checked';
        this.filesList = [];
        this.selectedRows = [];
        this.modelFiles = null;
        this.prevNextBySelection = true;
        this.pdfOptions = {};
        this.previewButtonDataName = 'd3files-preview';
        this.viewByExtension = 'pdf';

        this.logErrorPrefix = 'D3FilesPreview error: ';
        this.logWarningPrefix = 'D3FilesPreview warning: ';

        this.activeModel = null;
        this.activeModelIndex = null;
        this.activeFile = null;
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
            $(this.handlers.prevButton).on('click', function () {
                self.preview($(this));
            });
            $(this.handlers.nextButton).on('click', function () {
                self.preview($(this));
            });
            $('table .d3files-preview-widget-load').on('click', function () {
                self.preview($(this));
            });
        },
        preview: function (e) {
            this.handlers.modalMessages.empty();
            this.handlers.modalContent.empty();

            try {
                let m = this.getAttachmentData(e);
                this.selectedRows = this.getSelectedRows();
                if (0 < this.selectedRows.length) {
                    this.handlers.modalMessages.html('Selected: ' + this.selectedRows.length);
                }
                this.activeModel = m;
                let ma = null;
                    ma = "undefined" === typeof m.active ? this.getNextActiveFile(m) : this.getFileById(m.active, m.files);

                if (!ma) {
                    throw new Error('Cannot get active file from model');
                }

                this.loadFile(ma);
                this.renderModelFiles(m);

                let fl = 0 < this.selectedRows.length ? this.selectedRows : this.filesList;
                this.initPrevNextButtons(m, fl);
            } catch (err) {
                console.log(this.logErrorPrefix + "preview() Catch got: " + err);
            }
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
                if (f.id === id) {
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
            $.each(ml, function (f) {
                let ext = self.getFileExtension(f.file_name);
                if (e === ext) {
                    fe = f;
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
                    return true;
                } catch (err) {
                    throw new Error('loadFile got catch: ' + err);
                }
            }
            if ("png" === ext || "jpg" === ext|| "jpeg" === ext) {
                this.loadImage(f, this.handlers.modalContent);
                this.activeFile = f;
                return true;
            }
            throw new Error('Unsupported file type for load: ' + ext);
        },
        loadPDF: function (f) {
            D3PDF.trigger (f.src);
        },
        loadImage: function (f) {
            this.handlers.imageContent.html('').show();
            let img = $('<img>');
            img.attr('src', f.src);
            img.attr('alt', f.file_name);
            img.attr('title', f.file_name);
            img.css('max-width', '400px');
            img.css('max-height', '400px');
            this.handlers.imageContent.append(img);
        },
        getPrevModel: function (m, ml) {
            if ("object" !== typeof m) {
                throw new TypeError('getPrevModel m argument is not an object');
            }
            if ("object" !== typeof ml) {
                throw new TypeError('getPrevModel ml argument is not an object');
            }
            let mi = this.getModelIndex(m.modelId),
                id = parseInt(this.getArrPrevIndex(ml, mi));
            return ml[id];
        },
        getNextModel: function (m, ml) {
            if ("object" !== typeof m) {
                throw new TypeError('getNextModel m argument is not an object');
            }
            if ("object" !== typeof ml) {
                throw new TypeError('getNextModel ml argument is not an object');
            }
            let mi = this.getModelIndex(m.modelId),
                id = parseInt(this.getArrNextIndex(ml, mi));
            return ml[id];
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
        getPrevModelBySelection: function (currId) {
            let s = this.getSelectedRows();
            if (s.length === 0) {
                return null;
            }
            let ml = this.getFilesListFromSelected(s),
                i = this.getArrPrevIndex(ml, currId);
            return this.filesList[i];
        },
        getFilesListFromSelected: function(s) {
            let l = [];
            $(s).each(function () {
                l.push($(this).val());
            });
            return l;
        },
        getNextModelBySelection: function (currId) {
            let s = this.getSelectedRows();
            if (s.length === 0) {
                return null;
            }
            let i = this.getArrNextIndex(s, currId);
            // Just take first selected if current row is not
            if (!i) {
                let mId = s[0],
                    mi = this.getModelIndex(mId);
                return this.filesList[mi];
            }
            return this.filesList[i];
        },
        getArrPrevIndex: function (arr, index) {
            if (arr.length === 0) {
                return null;
            }
            let k = index-1;
            if (0 > k) {
                return null;
            }
            if (-1 === arr[k]) {
                return null;
            }
            return k;
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
                rows = $(this.selectedElementsQuery);
            // For testing
            /*if (!rows.length) {
                throw new Error('Selected elements not found by: ' + this.selectedElementsQuery);
            }*/
            rows.each(function () {
                s.push($(this).val());
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
            return name.split(/\#|\?/)[0].split('.').pop().trim();
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
        initPrevNextButtons: function(m, ml) {
            try {
                this.handlers.nextButton.hide();
                this.handlers.prevButton.hide();

                let nms = this.getNextModelBySelection(m.modelId),
                    nm = nms || this.getNextModel(m, ml);
                if (nm) {
                    this.setLoadButtonAttrs(this.handlers.nextButton, nm);
                    this.handlers.nextButton.show();
                }
                let pm = this.getPrevModel(m, ml);
                if (pm) {
                    this.setLoadButtonAttrs(this.handlers.prevButton, pm);
                    this.handlers.prevButton.show();
                }
            } catch (e) {
                throw new Error( "initPrevNextButtons Catch got: " + e);
            }
        },
        setLoadButtonAttrs: function(b, f) {
            b.data(this.previewButtonDataName, f);
        }
    };
    let d3fp = new $.D3FilesPreview();
    d3fp.reflow();
    $(document).on('pjax:success', function() {
        d3fp.reflow();
    });

}(jQuery));