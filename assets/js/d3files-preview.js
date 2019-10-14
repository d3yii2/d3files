(function ($) {
    "use strict";

    $.D3FilesPreview = function () {
        this.handlers = {
            previewButton: $('.d3files-preview-widget-load'),
            //previewDropdown: $('.d3files-preview-dropdown'),
            prevButton: $('.d3files-preview-prev-button'),
            nextButton: $('.d3files-preview-next-button')
        };
        this.modalContent = $('#th-modal .th-modal-content');
        this.modalMessages = $('#th-modal .th-modal-messages');
        this.filesList = [];
        this.selectedRows = [];
        this.modelFiles = null;
        this.prevFile = null;
        this.nextFile = null;
        this.prevNextBySelection = true;
        this.pdfOptions = {};
        this.logErrorPrefix = 'D3FilesPreview error: ';

        this.logWarningPrefix = 'D3FilesPreview warning: ';
        this.previewButtonDataName = 'd3files-preview';
        this.viewByExtension = 'pdf';
        this.activeModel = null;
        this.activeModelIndex = null;
        this.activeFile = null;
    };

    //assigning an object literal to the prototype is a shorter syntax
    //than assigning one property at a time
    $.D3FilesPreview.prototype = {
        setOption: function(prop, val) {
            this.prop = val;
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

            // Make class accesible into event
            var self = this;

            $(this.handlers.previewButton).on('click', function () {
                self.preview($(this));
            });
            $(this.handlers.prevButton).on('click', function () {
                self.preview($(this));
            });
            $(this.handlers.nextButton).on('click', function () {
                self.preview($(this));
            });

        },
        preview: function (e) {
            this.modalMessages.empty();

            var d = this.getAttachmentData(e);

            if ("undefined" === typeof d) {
                console.log(this.logErrorPrefix + ' Cannot read attachment data. Element has no attribute: data-' + this.previewButtonDataName);
                return false;
            }
            this.activeModel = d;

            if ("undefined" === typeof this.activeModel.active) {
                this.activeFile = this.getActiveFile();
            } else {
                this.activeFile = this.activeModel.files[this.activeModel.active];
            }

            if ("undefined" === typeof this.activeFile) {
                console.log(this.logErrorPrefix + ' No active file by key: ' + this.activeModel.active);
                return false;
            }

            //this.modelFiles = this.getModelFiles(); //JSON.parse(this.activeModel.files);
            this.selectedRows = this.getSelectedRows();
            this.modalMessages.html('Selected: ' + this.selectedRows.length);
            this.loadPreview(this.activeFile.src);
            this.initPrevNextButtons();
        },
        getActiveFile: function (e) {
            if ("undefined" === typeof this.activeModel) {
                return null;
            }
            if (0 === this.activeModel.length) {
                return null;
            }
            var af = null,
                fbe = this.getFileByExtension(this.activeModel, this.viewByExtension);
            if (fbe) {
                af = fbe;
            } else {
                var nf = this.getArrNextIndex(this.activeModel, 0);
                af = this.activeModel[nf];
            }
            return af;
        },
        getFileByExtension: function (fl, e) {
            var f = null,
                self = this;
            $.each(fl, function () {
                var fe = self.getFileExtension(this.file_name);
                if (e === fe) {
                    f = this;
                    return false;
                }
            });
            return f;
        },
        getAttachmentData: function (e) {
            return e.data(this.previewButtonDataName);
        },
        loadFile: function (f) {
        },
        loadPreview: function (url) {
            D3PDF.trigger (url);
        },
        getPrevModel: function () {
            var f = this.getPrevModelBySelection();
            if (!f) {
                var i = this.getArrPrevIndex(this.filesList, this.activeModel.modelId);
                return this.filesList[i];
            }
            return null;
        },
        getNextModel: function () {
            var f = this.getNextModelBySelection();
            if (!f) {
                var mi = this.getModelIndex(this.activeModel),
                    id = parseInt(this.getArrNextItemIndex(this.filesList, mi));
                return this.filesList[id];
            }
            return null;
        },
        getModelIndex: function (m) {
            var index = null;
            $.each(this.filesList, function (i, item) {
                if (parseInt(m.modelId) === parseInt(item.modelId)) {
                    index = i;
                    return false;
                }
            });
            return index;
        },
        getPrevModelBySelection: function () {
            var s = this.getSelectedRows();
            if (s.length === 0) {
                return null;
            }
            var fl = this.getFilesListFromSelected(s),
                i = this.getArrPrevIndex(fl, this.activeModel.modelId);
            return this.filesList[i];
        },
        getFilesListFromSelected: function(s) {
            var l = [];
            $(s).each(function () {
                l.push($(this).val());
            });
        },
        getNextModelBySelection: function () {
            var s = this.getSelectedRows();
            if (s.length === 0) {
                return null;
            }
            i = this.getArrNextIndex(fl, this.activeModel.modelId);
            return this.filesList[i];
        },
        getArrPrevItemIndex: function (arr, index) {
            if (arr.length === 0) {
                return null;
            }
            var k = index-1;
            if (0 > k) {
                return null;
            }
            var f = -1 === arr[k] ? null : arr[k];
            return f;
        },
        getArrNextItemIndex: function (arr, index) {
            if (arr.length === 0) {
                return null;
            }
            var lastI = null,
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
        },
        getArrPrevIndex: function (arr, index) {
            if (arr.length === 0) {
                return null;
            }
            var k = index-1;
            if (0 > k) {
                return null;
            }
            var f = -1 === arr[k] ? null : arr[k];
            return f;
        },
        getArrNextIndex: function (arr, index) {
            if (arr.length === 0) {
                return null;
            }
            var k = index + 1;
            if (k > arr.length) {
                return null;
            }
            var f = -1 === arr[k] ? null : arr[k];
            return f;
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
            var f = $('#d3files-preview-button-' + id).data('d3files-files');
            return f;
        },
        /* Get the array of the files from selected rows */
        getSelectedRows: function () {
            var s = [];
            $('#ThGridViewTable tbody input[type="checkbox"]:checked').each(function () {
                s.push($(this).val());
            });
            return s;
        },
        /* Build the filesList */
        buildFilesList: function () {
            var r = [],
                self = this;
            $('*[data-' + this.previewButtonDataName + ']').each(function () {
                var d = self.getAttachmentData($(this));
                if (d) {
                    r.push(d);
                }
            });
            return r;
        },
        getModelViewFilesByExt: function(f) {
            var fe = [];
            f.each(function () {
                var fe = this.getFileExtension($(this).file_name);
                if (this.viewByExtension === fe) {
                    fe.push();
                }
            });
            return fe;
        },
        getFileExtension: function(f) {
            var e = (f.lastIndexOf('.') < 1) ?   null : f.split('.').slice(-1);
            return "undefined" === typeof e[0] ? null :e[0];
        },
        initPrevNextButtons: function() {
            var nf = this.getNextModel();
            if (!nf) {
                ///this.modalMessages.html('No more attachments');
                this.handlers.nextButton.hide();
            } else {
                this.setLoadButtonAttrs(this.handlers.nextButton, nf);
                this.handlers.nextButton.show();
            }
            var pf = this.getPrevModel();
            if (!pf) {
                //this.modalMessages.html('No more attachments');
                this.handlers.prevButton.hide();
            } else {
                this.setLoadButtonAttrs(this.handlers.prevButton, pf);
                this.handlers.prevButton.show();
            }
        },
        setLoadButtonAttrs: function(b, f) {
            //f.active =
            b.attr('data-d3files-preview', JSON.stringify(f));
            /*if ("undefined" !== f.fileTitle) {
                b.attr('title', f.fileTitle);
            }*/
        }
    };
    var d3fp = new $.D3FilesPreview();
    d3fp.reflow();
}(jQuery));