var CMS = {

    Git: {
        Init: function() {
            $('#git-pull-btn').on('click', (e) => CMS.Git.HandlePull(e.currentTarget));
            $('#git-push-btn').on('click', (e) => CMS.Git.HandlePush(e.currentTarget));
        },

        HandlePull: async function() {
            await this._executeSyncProcess(this, async (log) => {
                // Step 1: Pull
                await this._runGitStage('gitPull', 'Step 1/3: Requesting git pull from remote repository...', 'PULL STAGE FAILED', log);

                // Step 2: Import
                await this._runGitStage('gitImport', 'Step 2/3: Scanning JSON files and updating database...', 'IMPORT STAGE FAILED', log);

                // Step 3: Push
                await this._runGitStage('gitPush', 'Step 3/3: Committing local changes and pushing to Git...', 'PUSH STAGE FAILED', log);

                log('=== ALL SYNC STAGES COMPLETED SUCCESSFULLY ===', 'success');
            });
        },

        HandlePush: async function() {
            await this._executeSyncProcess(this, async (log) => {
                await this._runGitStage('gitPush', 'Step 1/1: Committing local changes and pushing to Git...', 'PUSH STAGE FAILED', log);
                log('=== PUSH COMPLETED SUCCESSFULLY ===', 'success');
            });
        }
    },

    Editor: {
        Init: function() {
            $('.cms-form-update').on('submit', function () {
                var editor = $('.editor');
                // Disable codeview before saving page to execute transformations
                if (editor.summernote('codeview.isActivated')){
                    editor.summernote('codeview.deactivate');
                }
            });

            // Summernote API Documentation:
            // https://summernote.org/deep-dive/
            $('.editor').summernote({
                placeholder: '',
                tabsize: 2,
                height: 600,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture']],
                    ['view', ['codeview', 'help']]

                    // Note: 'fullscreen' removed due to severe display problems
                ],
                callbacks: {
                  onBlur: function() {
                    lastRange = $('.editor').summernote('createRange');
                  }
                }
            });

            $('.btn-codeview').click(function() {
                if (this.classList.contains('active')) {
                    // Codeview is being deactivated, clean up the content
                    let editor = $('.editor');
                    let content = editor.summernote('code');

                    // Note: This must be in sync with the code in Controller\Feature\CmsTrait which is executed when saving the page,
                    // as well as the command parser in the TueFind View Helper.
                    const cleaned = content.replace(/\{\{[\s\S]*?\}\}/g, (match) => {
                        return match.replace(/&gt;/g, '>');
                    });

                    // Write the cleaned content back to the editor
                    editor.summernote('code', cleaned);
                }
            });

            $('.cms_preview').click(function(thisEvent){
                let activeTab =  $(thisEvent.currentTarget).parent().prev().find('.tab-content .tab-pane.active');
                let pagetitle = activeTab.find('.page_title').val();
                let pageContent = activeTab.find('.editor').summernote('code');
                $('#exampleModal .preview_title').html(pagetitle);
                CMS.Editor.TransformPageContent(pageContent).then(transformedContent => {
                    $('#exampleModal .preview_body').html(transformedContent);
                });
            });

             $(document).on('click', '.copyImageURL', function(thisEvent) {
                let fullPATH = $(this).data('full-path');
                let ajaxImagePreURL = VuFind.path + '/AJAX/JSON?method=CmsDocs&action=getImageContent&full-path='+fullPATH;
                $('.note-image-url').val(ajaxImagePreURL);
                $('.note-image-btn').click();
            });

            $(document).on('click', '.copyDocumentURL', function(thisEvent) {
                thisEvent.preventDefault();

                let fullPATH = $(this).data('full-path');
                let ajaxFilePreURL = VuFind.path + '/AJAX/JSON?method=CmsDocs&action=getFileContent&full-path='+fullPATH;
                let fileName = $(this).data('file-name');
                let linkHTML = '<a target="_blank" href="'+ajaxFilePreURL+'">'+fileName+'</a>';

                if (lastRange) {
                    lastRange.select();
                }

                $('.editor').summernote('pasteHTML', linkHTML);

                var modal = $('.note-modal.open');
                modal.removeClass('open')
                    .attr('aria-hidden', 'true')
                    .hide();

                $('.note-modal-backdrop').remove();
                $('body').removeClass('modal-open').css('overflow', '');
            });

            $('.note-btn-group.note-insert').on('click', function() {
                let noteType = $(this).data('note-type');
                let noteContent = $(this).data('note-content');
                let noteTarget = $(this).data('note-target');
                let noteTargetElement = $('#' + noteTarget);

                console.log('clicked note button: type=' + noteType + ', content=' + noteContent + ', target=' + noteTarget);
                let noteForm = $('.note-modal-body .form-group.note-form-group.note-group-select-from-files');
                $('.AJAXCMSDocsBlock').remove();
                $('<div class="AJAXCMSDocsBlock">Loading...</div>').insertAfter(noteForm);
                CMS.GetAJAXDocs('AJAXCMSDocsBlock','plugin');

            });

        },

        // Function for calling AjaxHandler to replace palceholders (display texts, images, ...)
        TransformPageContent: function(pageContent) {
            // Use POST instead of GET due URL size limitation
            const postData = { content: pageContent };
            const url = VuFind.path + '/AJAX/JSON?method=CmsPageContentTransformer';

            return fetch(url, {
                method: 'POST',
                headers: {'Accept': 'application/json'},
                body: new URLSearchParams(postData)
            })
            .then(response => response.text())
            .then((data) => {
                const jsonObject = JSON.parse(data);
                return jsonObject.data.content;
            });
        }
    },

    FileManager: {
        Init: function() {
            $('.modalCreateFolderBtn').off('click').on('click', function() {
                let THIS = $(this);
                let parentModal = THIS.closest('.modal-content');
                let folderNameInput = parentModal.find('.folderNameInput').val();
                let currentBreadcrumbs = parentModal.find('.createFolderPATH').text().trim();
                let serverPATH = $('#createFolderBtn').data('server-path').trim();
                let cleanPath = serverPATH.replace(/\/$/, "");
                let parentPath = cleanPath + currentBreadcrumbs;
                console.log([folderNameInput,currentBreadcrumbs,serverPATH]);

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
                        $('#createFolderModal').modal('hide');

                        let message = response.data && response.data.data ? response.data.data : 'Folder not created';
                        $('.ajax-info').removeClass('d-none').find('.alert').text(message);

                        setTimeout(() => {
                            $('.ajax-info').addClass('d-none');
                        }, 2000);

                       $('.cms-breadcrumbs .btn-secondary.tf-theme-btn').last().click(); //reload

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

                $('#exampleModal .preview_title').html(cardHeaderTitle);

                if (image.length) {
                    let clonedImg = image.clone().removeClass('card-img-top img-fluid');
                    $('#exampleModal .preview_body').html(clonedImg);
                } else {
                    let iconClone = card.find('.card-body').html();
                    $('#exampleModal .preview_body').html(iconClone);
                }
            });

            $(document).on('click', '.delete-btn', function(thisEvent) {
                thisEvent.preventDefault();

                let btn = $(this);
                let cardParent = btn.closest('.smc-card');

                $('.smc-card').removeClass('pre-delete');
                cardParent.addClass('pre-delete');

                let fileName = cardParent.find('.card-header').text().trim();
                let fullPath = btn.attr('data-full-path');
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
                        $('#confirmDeleteModal').modal('hide');

                        let message = response.data && response.data.data ? response.data.data : 'File removed success';
                        $('.ajax-info').removeClass('d-none').find('.alert').text(message);

                        setTimeout(() => {
                            $('.ajax-info').addClass('d-none');
                        }, 2000);

                       $('.cms-breadcrumbs .btn-secondary.tf-theme-btn').last().click(); //reload

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

                let fileInput = $('.fileUploadInput')[0];
                if (!fileInput || !fileInput.files.length) {
                    alert('Select file');
                    return;
                }
                let formData = new FormData();
                formData.append('file', fileInput.files[0]);

                let theme = $('.cms-breadcrumbs .btn-secondary.tf-theme-btn').last().data('theme');

                $.ajax({
                    url: VuFind.path + '/AJAX/JSON?method=CmsDocs&action=uploadFiles&theme='+theme,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.data && response.data.status === 'success') {
                            $('.ajax-info').removeClass('d-none').find('.alert').text(response.data.message);
                            setTimeout(() => {
                                $('.ajax-info').addClass('d-none');
                            }, 2000);
                            $('.fileUploadInput').val('');

                            $('.cms-breadcrumbs .btn-secondary.tf-theme-btn').last().trigger('click');
                        } else {
                            let errMsg = response.data && response.data.message ? response.data.message : 'error upload';
                            console.log(errMsg);
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        if (window.console && window.console.log) {
                            console.log("Status: " + xhr.status + ", Error: " + thrownError);
                        }
                    }
                });
            });

            $(document).on('click', '.tf-theme-btn', function() {
                // Get attr data-theme
                let serverPath = $(this).data('server-path');
                let themeName = $(this).data('theme');
                let fullPath = $(this).data('full-path');
                let block = $(this).data('block');
                let modetype = $(this).data('modetype');
                //console.log('Theme selected:', themeName);

                let uploadBlock = $("." + block);

                $.ajax({
                    url: VuFind.path + '/AJAX/JSON?method=CmsDocs&action=getThemeContent&server-path=' + serverPath+ '&path=' + themeName+ '&full-path=' + fullPath+ '&block=' + block+ '&modetype=' + modetype,
                    type: 'GET',
                    beforeSend: function() {
                        // show a loading spinner or message here if needed
                        uploadBlock.html('Loading...');
                    },
                    success: function(response) {

                        //console.log('Server response:', response);

                        if (response && response.status === 'OK' && response.data) {

                            uploadBlock.html(response.data);
                        } else if (response && response.data) {

                            uploadBlock.html(response.data);
                        } else {
                            uploadBlock.html("<span class='text-danger'>Theme not found or empty</span>");
                        }
                    },
                    error: function(xhr, status, error) {
                        uploadBlock.removeClass('disabled').text(themeName);
                        console.error('Error:', error);
                    }
                });
            });
        },
    },

    GetAJAXDocs: function(ajaxCmsDocsBlockClass='',modeType='') {
        const className = ajaxCmsDocsBlockClass.trim() || 'AJAXCMSDocsBlock';
        const $block = $(`.${className}`);
        $block.html('<div class="tf_themes_block">Loading...</div>');
        $.ajax({
            url: VuFind.path + '/AJAX/JSON',
            type: 'GET',
            data: {
                method: 'CmsDocs',
                action: 'getThemeURLs',
                block: className,
                modetype: modeType
            },
            dataType: 'json'
        })
        .done(response => {
            if (response && response.data && response.data.length > 0) {
                $block.html(response.data);
            } else {
                $block.html('Themes not found.');
            }
        })
        .fail((xhr, status, error) => {
            console.error('AJAX Error:', error);
            $block.html('html not loaded.');
        });
    },

    // deprecated? former tuefind.js, see GetAJAXDocs
    getDocs: function() {
        let table = $('.dataTable').DataTable({
            destroy: true, // if the table already exists, destroy it before reinitializing
            processing: true,
            serverSide: false, // later change to true if needed
            ajax: {
                url: VuFind.path + '/AJAX/JSON?method=CmsDocs&action=listFiles',
                method: 'GET',
                dataSrc: 'data'
            },
            columns: [
                {
                    data: 'name',
                    render: function (data, type, row) {
                        return `<a href="${row.url}" target="_blank">${data}</a>`;
                    }
                },
                {
                    data: null,
                    orderable: false,
                    render: function (data, type, row) {
                        return `
                            <a href="${row.url}" class="me-3" target="_blank">👁</a>
                            <a href="${row.url}" class="text-center text-danger col-6 delete-btn" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
                                <i class="fas fa-trash"></i>
                            </a>
                        `;
                    }
                }
            ]
        });
    },

    // deprecated? former tuefind.js
    getImages: function() {
        $.ajax({
            type: "GET",
            url: VuFind.path + '/AJAX/JSON?method=CmsDocs&action=listImages',
            dataType: "json",
            success: function (data) {
                let file = data.data;
                let HTMlData = '';
                for (let i = 0; i < file.length; i++) {
                    let oneBlock = `
                        <div class="col-3">
                            <div class="card h-100 smc-card">
                                <div class="card-header">
                                    ${file[i]['name']}
                                </div>
                                <div class="card-body">
                                    <img src="${file[i]['url']}" class="card-img-top" alt="${file[i]['name']}">
                                </div>
                                <div class="card-footer text-muted row gx-0">
                                    <a href="#" class="text-center d-block text-default cms_preview col-6" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="${file[i]['url']}" class="text-center d-block text-danger col-6 delete-btn" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>`;
                    HTMlData += oneBlock;
                }
                $('.ajax-content-images-container').html(HTMlData);
            }
        }); // end ajax
    }
};
