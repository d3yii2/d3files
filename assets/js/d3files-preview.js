(function ($) {
    "use strict";

    $.D3FilesPreview = function () {
        this.files = {};
        this.handlers = {
            previewButton: $('.d3files-preview-button'),
            //previewDropdown: $('.d3files-preview-dropdown'),
            prevButton: $('.d3files-preview-prev-button'),
            nextButton: $('.d3files-preview-next-button')
        };
        this.modalContent = $('#th-modal .th-modal-content');
        this.modalMessages = $('#th-modal .th-modal-messages');
        this.rows = [];
        this.checkedRowIds = [];
        this.rowId = null;
        this.modelFiles = null;
        this.prevFile = null;
        this.nextFile = null;
        this.prevNextBySelection = true;
        this.pdfOptions = {};
        //console.log(this);
    };

    //assigning an object literal to the prototype is a shorter syntax
    //than assigning one property at a time
    $.D3FilesPreview.prototype = {
        setOption: function(prop, val) {
            this.prop = val;
        },
        setPdfObject: function(o) {
            if ('undefined' === typeof D3PDF) {
                console.log('D3PDF is not defined. Missing pdfobject-custom.js ?');
                return false;
            }
            D3PDF.construct(o);
        },
        reflow: function () {
            this.updateRows();

            // Make class accesible into event
            var self = this;

            $(this.handlers.previewButton).on('click', function () {
                self.modalMessages.empty();
                self.handleView($(this));
            });
            $(this.handlers.prevButton).on('click', function () {
                self.handlePrev();
            });
            $(this.handlers.nextButton).on('click', function () {
                self.handleNext();
            });

        },
        handleView: function (e) {
            //console.log(e);
            var f = JSON.parse(e.attr('data-files-list'));
            this.rowId = this.getActiveRowId(e.data('row-id'));
            this.modelFiles = f.files;
            this.checkedRowIds = this.getCheckedRowIds();
            this.modalMessages.html('Selected: ' + this.checkedRowIds.length);

            //this.prevFile = this.getFilesListByRowId(files[0])
            return true;
        },
        getActiveRowId: function (modelId) {
            var id = null;
            $.each(this.rows, function (i, val) {
                if (parseInt(modelId) === parseInt(val)) {
                    id = val;
                }
            });
            return id;
        },
        handlePrev: function () {
            var id = this.getPrevRowIdBySelection();
            if (!id) {
                id = this.getPrevInArrayByKey(this.rows, this.rowId);
            }
            if (!id) {
                this.modalMessages.html('No more attachments');
                return;
            }
            this.loadPreview(id);
        },
        handleNext: function () {
            var id = this.getNextRowIdBySelection();

            if (!id) {
                id = this.getNextInArrayByKey(this.rows, this.rowId);
            }
            if (!id) {
                this.modalMessages.html('No more attachments');
                return;
            }
            this.loadPreview(id);
        },
        loadPreview: function (rowId) {
            var link = $('#d3files-preview-button-' + rowId),
                url = link.data('src'),
                modelFiles = link.data('files-list');
            D3PDF.embed(url);
            this.rowId = rowId;
        },
        getPrevRowIdBySelection: function () {
            var rows = this.getCheckedRowIds();

            if (rows.length === 0) {
                return null;
            }

            var i = this.getPrevInArrayByKey(rows, this.rowId);
            return i;
        },
        getNextRowIdBySelection: function () {
            var rows = this.getCheckedRowIds();

            if (rows.length === 0) {
                return null;
            }

            var i = this.getNextInArrayByKey(rows, this.rowId);
            return i;
        },
        getPrevInArrayByKey: function (arr, number) {
            if (arr.length === 0) {
                return null;
            }
            var i = arr.indexOf(number);
            i--;
            return arr[i];
        },
        getNextInArrayByKey: function (arr, number) {
            var i = arr.indexOf(number);
            i++;
            if (i >= arr.length)
                i = 0;

            return arr[i];
        },
        updateRows: function () {
            var ids = this.getAllRowIds();
            this.setRows(ids);
        },
        setRows: function (r) {
            this.rows = r;
        },
        getRows: function () {
            return this.rows;
        },
        getFilesListByRowId: function (id) {
            var files = $('#d3files-preview-button-' + id).data('files-list');
            return files;
        },
        /* Get the array of row ids by selected checkboxes */
        getCheckedRowIds: function () {
            var selected = [];
            $('#ThGridViewTable tbody input[type="checkbox"]:checked').each(function () {
                selected.push($(this).val());
            });
            return selected;
        },
        /* Get the array of row ids */
        getAllRowIds: function () {
            var r = [];
            $('#ThGridViewTable tbody input[type="checkbox"]').each(function () {
                r.push($(this).val());
            });
            return r;
        }
    };
    var d3fp = new $.D3FilesPreview();
    d3fp.reflow();
}(jQuery));