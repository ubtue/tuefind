var CMS_STATIC = {
    FileManager: {
        Init: function() {
            
            $(document).on('hide.bs.modal', '.modal', function () {
                if (this.contains(document.activeElement) || document.activeElement === this) {
                    document.activeElement.blur();
                }
            });

            $('.modalCreateFolderBtn').off('click').on('click', function() {
                let THIS = $(this);
                let parentModal = THIS.closest('.modal-content');
                let folderNameInput = parentModal.find('.folderNameInput').val();
                let currentBreadcrumbs = parentModal.find('.createFolderPATH').text().trim();
                let serverPATH = $('#createFolderBtn').data('server-path').trim();
                let cleanPath = serverPATH.replace(/\/$/, "");
                let parentPath = cleanPath + currentBreadcrumbs;
                let validationElement = parentModal.find('.invalid-feedback');

                $.ajax({
                    url: VuFind.path + '/AJAX/JSON',
                    method: 'GET',
                    data: {
                        method: 'CmsDocs',
                        action: 'createFolder',
                        'parentPath': parentPath,
                        'folderName': folderNameInput
                    },
                    dataType: 'json',
                    success: function(response) {
                        let respone = response.data;
                        let status = respone.status;
                        let message = respone.message;

                        if(status === 'ERROR'){
                            validationElement.show().html(message);
                            setTimeout(() => {
                                validationElement.hide().html('');
                            }, 2000);
                            return;
                        }else{
                            validationElement.hide().html('');
                            parentModal.find('.container').html(`<div class="alert alert-success" role="alert">${message}</div>`);
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        if (window.console && window.console.log) {
                            console.log("Status: " + xhr.status + ", Error: " + thrownError);
                        }
                    }
                }); //end AJAX
            });

            $('#createFolderModal').on('show.bs.modal', function (event) {
                let currentBreadcrumbs = $('.cms-breadcrumbs .cms-actions-panel-right');
                let oneBread = [];
                currentBreadcrumbs.find('.btn').each(function() {
                    let btnText = $(this).text().trim();
                    if (btnText.length > 0 && btnText != "..") {
                        oneBread.push(btnText);
                    }
                });

                let fullPath = (oneBread.length > 0) ? "/" + oneBread.join('/') + "/" : "/";

                $('.createFolderPATH').text(fullPath);
            });
            
            $(document).on('click', '.cms_preview, .card-img-top', function(thisEvent) {
                thisEvent.preventDefault();
                thisEvent.stopPropagation();

                let card = $(thisEvent.currentTarget).closest('.card');
                let image = card.find('.card-img-top');

                let cardHeaderTitle = card.find('.card-header').attr('title') || card.find('.card-header').text().trim();

                $('#previewModal .preview_title').html(cardHeaderTitle);

                if (image.length) {
                    let clonedImg = image.clone().removeClass('card-img-top img-fluid');
                    $('#previewModal .preview_body').html(clonedImg);
                } else {
                    let iconClone = card.find('.card-body').html();
                    $('#previewModal .preview_body').html(iconClone);
                }
            });
            
            $(document).on('click', '.delete-btn', function(thisEvent) {
                thisEvent.preventDefault();

                let btn = $(this);
                let cardParent = btn.closest('.smc-card');

                $('.smc-card').removeClass('pre-delete');
                cardParent.addClass('pre-delete');

                let fileName = cardParent.find('.card-header').text().trim();
                let fullPath = btn.attr('data-relative-path');
                let isImage = '';
                if (cardParent.find('.card-img-top').length > 0) {
                    isImage = 'image';
                }
                $('#confirmDeleteModal .sureDeleteName').text(fileName);
                $('#confirmDeleteModal .file-path').text(fullPath);
                $('#confirmDeleteModal .file_or_image').text(isImage);
            });

            $('#confirmDeleteBtn').off('click').on('click', function() {
                let THIS = $(this);
                let parentModal = THIS.closest('.modal-content');
                let filePATH = parentModal.find('.file-path').text().trim();
                let fileORImage = parentModal.find('.file_or_image').text().trim();

                $.ajax({
                    url: VuFind.path + '/AJAX/JSON',
                    method: 'GET',
                    data: {
                        method: 'CmsDocs',
                        action: (fileORImage.length > 0) ? 'deleteImage' : 'deleteFile',
                        'full-path': filePATH
                    },
                    dataType: 'json',
                    success: function(response) {
                        
                        let message = response.data.data;
                        let status = response.data.status;

                        if(status === 'success') {
                           
                            THIS.blur();

                            $('#confirmDeleteModal').one('hidden.bs.modal', function () {
                                location.reload();
                            });

                            $('.ajax-info').removeClass('d-none').find('.alert').text(message);
                            $('.ajax-response').removeClass('d-none').addClass('text-success').text(message);

                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                            
                        }else{
                            $('.ajax-response').removeClass('d-none').addClass('text-danger').text(message);
                            console.log('Delete failed:', message);
                            setTimeout(() => {
                                $('.ajax-info').addClass('d-none');
                                $('.ajax-response').addClass('d-none').text('');
                            }, 2000);
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        if (window.console && window.console.log) {
                            console.log("Status: " + xhr.status + ", Error: " + thrownError);
                        }
                    }
                });
            });

            $(document).off('click', '.uploadBtn').on('click', '.uploadBtn', function (e) {
                e.preventDefault();

                let THIS = $(this);

                let validationElement = $(e.currentTarget).siblings('.uploadBtn-validation');

                let fileInput = $('.fileUploadInput')[0];
                if (!fileInput || !fileInput.files.length) {
                    validationElement.text('Select file');
                    validationElement.removeClass('text-success').addClass('text-danger').show();
                    setTimeout(() => {
                        validationElement.text('');
                    }, 1000);
                    return;
                }
                let formData = new FormData();
                formData.append('file', fileInput.files[0]);

                let theme = $(e.currentTarget).data('theme');
                

                $.ajax({
                    url: VuFind.path + '/AJAX/JSON?method=CmsDocs&action=uploadFiles&theme='+theme,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function (response) {
                        let message = response.data.message;
                        let status = response.data.status;

                        THIS.blur();
                        
                        validationElement.text(message);
                        
                        if(status === 'success') {
                            
                            validationElement.removeClass('text-danger').addClass('text-success').show();
                            setTimeout(() => {
                                location.reload();
                            }, 1000);

                        } else {
                            validationElement.removeClass('text-success').addClass('text-danger').show();
                            setTimeout(() => {
                                validationElement.hide().text('');
                            }, 3000);
                        }

                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        if (window.console && window.console.log) {
                            console.log("Status: " + xhr.status + ", Error: " + thrownError);
                        }
                    }
                });
            });
        }
    }
};
