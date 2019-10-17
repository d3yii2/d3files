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
            previewButton: $('.d3files-preview-widget-load'),
            previewDropdown: $('.d3files-preview-dropdown'),
            prevButton: $('.d3files-preview-prev-button'),
            nextButton: $('.d3files-preview-next-button'),
            modalContent: $('#th-modal .th-modal-content'),
            modalMessages: $('#th-modal .th-modal-messages'),
            imageContent: $('.d3preview-image-content'),
            filesListContent: $('.d3preview-model-files')
        };
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

    //assigning an object literal to the prototype is a shorter syntax
    //than assigning one property at a time
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
            self.initPreviewButtonHandler();
            $(this.handlers.prevButton).on('click', function () {
                self.preview($(this));
            });
            $(this.handlers.nextButton).on('click', function () {
                self.preview($(this));
            });
        },
        initPreviewButtonHandler: function () {
            let self = this;
            $(this.handlers.previewButton).on('click', function () {
                self.preview($(this));
            });
        },
        preview: function (e) {
            this.handlers.modalMessages.empty();

            let m = this.getAttachmentData(e);
            
            //this.modelFiles = this.getModelFiles(); //JSON.parse(m.files);
            this.selectedRows = this.getSelectedRows();
            if (0 < this.selectedRows.length) {
                this.handlers.modalMessages.html('Selected: ' + this.selectedRows.length);
            }

            //let ma = this.getNextActiveFile(m);
            /*if ("undefined" === typeof m.active) {
                console.log(this.logErrorPrefix + ' No active file by key: ' + m.active);
                return false;
            }*/

            this.initPrevNextButtons(m);
            this.initFilesListDropdown(m);
            this.activeModel = m;

            let ma = null;
            if ("undefined" === typeof m.active) {
                ma = this.getNextActiveFile(m);
            } else {
                ma = this.getFileById(m.active, m.files);
            }
            if (!ma) {
                console.log(this.logErrorPrefix + 'Cannot get active file from model');
                console.log(m);
                return false;
            }

            this.loadFile(ma);
            this.renderModelFiles(m);
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
            if ("undefined" === typeof m) {
                return null;
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
        getFileByExtension: function (fl, e) {
            let fe = null,
                self = this;
            $.each(fl, function (f) {
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
                console.log(this.logErrorPrefix + 'missing data attribute: ' + this.previewButtonDataName + ' in element');
                console.log(e);
                return false;
            }

            //let pd = JSON.parse(decodeURIComponent(d));
            return d; //pd;
        },
        loadFile: function (f) {
            let ext = this.getFileExtension(f.file_name);
            if ("pdf" === ext) {
                this.handlers.imageContent.html('').hide();
                this.loadPDF(f);
                this.activeFile = f;
                return true;
            }
            if ("png" === ext || "jpg" === ext|| "jpeg" === ext) {
                this.loadImage(f, this.handlers.modalContent);
                this.activeFile = f;
                return true;
            }
            console.log(this.logErrorPrefix + 'Unsupported file type for load: ' + ext);
            return false;
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
        getPrevModel: function (m) {
            let f = this.getPrevModelBySelection();
            if (!f) {
                let mi = this.getModelIndex(m),
                    id = parseInt(this.getArrPrevIndex(this.filesList, mi));
                return this.filesList[id];
            }
            return null;
        },
        getNextModel: function (m) {
            let f = this.getNextModelBySelection();
            if (!f) {
                let mi = this.getModelIndex(m),
                    id = parseInt(this.getArrNextIndex(this.filesList, mi));
                return this.filesList[id];
            }
            return null;
        },
        getModelIndex: function (m) {
            let index = null;
            $.each(this.filesList, function (i, item) {
                if (parseInt(m.modelId) === parseInt(item.modelId)) {
                    index = i;
                    return false;
                }
            });
            return index;
        },
        getPrevModelBySelection: function () {
            let s = this.getSelectedRows();
            if (s.length === 0) {
                return null;
            }
            let fl = this.getFilesListFromSelected(s),
                i = this.getArrPrevIndex(fl, this.activeModel.modelId);
            return this.filesList[i];
        },
        getFilesListFromSelected: function(s) {
            let l = [];
            $(s).each(function () {
                l.push($(this).val());
            });
            return l;
        },
        getNextModelBySelection: function () {
            let s = this.getSelectedRows();
            if (s.length === 0) {
                return null;
            }
            let i = this.getArrNextIndex(s, this.activeModel.modelId);
            return this.filesList[i];
        },
        /*getArrPrevItemIndex: function (arr, index) {
            if (arr.length === 0) {
                return null;
            }
            let k = index-1;
            if (0 > k) {
                return null;
            }
            let i = -1 === arr[k] ? null : k;
            return i;
        },*/
        /*getArrNextItemIndex: function (arr, index) {
            if (arr.length === 0) {
                return null;
            }
            let lastI = null,
                nextI = null;
            $.each(arr, function (id, item) {
                if (parseInt(id) === index) {
                    lastI = id;
                } else if (lastI) {
                    nextI = id;
                    return false;
                }
            });
            return nextI;
        },*/
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
            let s = [];
            $('#ThGridViewTable tbody input[type="checkbox"]:checked').each(function () {
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

            this.initPreviewButtonHandler();
        },
        initPrevNextButtons: function(m) {
            let nm = this.getNextModel(m);
            if (!nm) {
                ///this.handlers.modalMessages.html('No more attachments');
                this.handlers.nextButton.hide();
            } else {
                this.setLoadButtonAttrs(this.handlers.nextButton, nm);
                this.handlers.nextButton.show();
            }
            let pm = this.getPrevModel(m);
            if (!pm) {
                //this.handlers.modalMessages.html('No more attachments');
                this.handlers.prevButton.hide();
            } else {
                this.setLoadButtonAttrs(this.handlers.prevButton, pm);
                this.handlers.prevButton.show();
            }
        },
        setLoadButtonAttrs: function(b, f) {
            b.attr('data-' + this.previewButtonDataName, JSON.stringify(f));
        }
    };
    let d3fp = new $.D3FilesPreview();
    d3fp.reflow();
}(jQuery));