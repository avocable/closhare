(function (factory) {
    'use strict';
    if (typeof define === 'function' && define.amd) {
        define([
            'jquery',
            './cl.fuploader.process'
        ], factory);
    } else {
        factory(
            window.jQuery
        );
    }
}(function ($) {
    'use strict';
    $.closhare.fileupload.prototype.options.processQueue.push(
        {
            action: 'validate',
            always: true,
            acceptFileTypes: '@',
            maxFileSize: '@',
            minFileSize: '@',
            maxNumberOfFiles: '@',
            fileNameMaxChar : '@',
            disabled: '@disableValidation'
        }
    );
    $.widget('closhare.fileupload', $.closhare.fileupload, {

        options: {
            
            acceptFileTypes: uOptions.allowedTypes ? new RegExp('(\.|\/)('+uOptions.allowedTypes+')$', "g") : undefined,
            maxFileSize: uOptions.maxFileSize,
            //minFileSize: undefined, // No minimal file size
            maxNumberOfFiles: uOptions.maxNumberOfFiles,
            
            fileNameMaxChar : 155, 
            getNumberOfFiles: $.noop,
            messages: {
                allowedSize: 'Max allowed file size:<br>',
                allowedSizeMin: 'Min allowed file size:<br>',
                allowedNumb: 'Max allowed items in a queue:<br>',
                allowedFTypes: 'Check help docs for allowed file types.',
                allowedFChars: 'Max allowed file name length is:<br>',
                maxNumberOfFiles: 'Maximum number of files exceeded',
                acceptFileTypes: 'File type not allowed',
                maxFileSize: 'File is too large!',
                minFileSize: 'File is too small!',
                fileNameMaxChar : 'File name is too long!'
            }
        },

        processActions: {

            validate: function (data, options) {
                if (options.disabled) {
                    return data;
                }
                var dfd = $.Deferred(),
                    settings = this.options,
                    file = data.files[data.index],
                    fileCount = $filesList.find("li").not('.processing').length;
            
                if (options.maxFileSize && file.size > options.maxFileSize) {
                    file.error = settings.i18n('maxFileSize');
                    file.errorE = settings.i18n('allowedSize')+formatFileSize(options.maxFileSize);
                    file.errcode = 1;
                }              
                else if ($.type(options.maxNumberOfFiles) === 'number' &&
                        (fileCount || 0) + data.files.length >
                            options.maxNumberOfFiles) {
                    file.error = settings.i18n('maxNumberOfFiles');
                    file.errorE = settings.i18n('allowedNumb')+options.maxNumberOfFiles;
                    file.errcode = 2;
                } 
                else if (options.acceptFileTypes &&
                        !(options.acceptFileTypes.test(file.type) ||
                        options.acceptFileTypes.test(file.name))) {
                    file.error = settings.i18n('acceptFileTypes');
                    file.errorE = settings.i18n('allowedFTypes');
                    file.errcode = 3;
                } 
                else if ($.type(file.size) === 'number' &&
                        file.size < options.minFileSize) {
                    file.error = settings.i18n('minFileSize');
                    file.errorE = settings.i18n('allowedSizeMin')+formatFileSize(options.minFileSize);
                    file.errcode = 4;
                } 
                else if (file.name.length > options.fileNameMaxChar) {
                    file.error = settings.i18n('fileNameMaxChar');
                    file.errorE = settings.i18n('allowedFChars')+options.fileNameMaxChar;
                    file.errcode = 5;
                } 
                else {
                    delete file.error;
                }
                if (file.error || data.files.error) {
                    data.files.error = true;
                    dfd.rejectWith(this, [data]);
                } else {
                    dfd.resolveWith(this, [data]);
                }
                return dfd.promise();
            }

        }

    });

}));