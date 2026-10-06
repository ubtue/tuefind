var CMS = {
    Git: {
        Init: function() {
            $('#git-pull-btn').on('click', (e) => CMS_STATIC.Git.HandlePull(e.currentTarget));
            $('#git-push-btn').on('click', (e) => CMS_STATIC.Git.HandlePush(e.currentTarget));
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

            CMS.Editor.InitPluginForPlaceholders();

            var CustomPictureButton = function (context) {
            var ui = $.summernote.ui;

            // Create button
            var button = ui.button({
                contents: '<i class="fa-solid fa-paperclip"></i>', // icon for the button
                tooltip: 'Upload Files', // tooltip text
                click: function () {
                // Call the image dialog
                context.invoke('imageDialog.show');
                }
            });

                return button.render();
            };

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
                    ['insert', ['link', 'picture', 'myPicture']],

                    // Note: Placeholders plugin is still experimental, works but will lead to hanging window during preview
                    //['insert', ['link', 'picture', 'tuefindPlaceholders']],
                    ['view', ['codeview', 'help']]

                    // Note: 'fullscreen' removed due to severe display problems
                ],
                callbacks: {
                  onBlur: function() {
                    lastRange = $('.editor').summernote('createRange');
                  }
                },
                  buttons: {
                    myPicture: CustomPictureButton
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

            $('.cms_content_preview').off('click').on('click', function(thisEvent) {

                console.log('Preview button clicked, preparing to transform content...');

                thisEvent.preventDefault();
                
                var $btn = $(thisEvent.currentTarget);
                
                // protect against multiple clicks while the request is in progress
                if ($btn.data('loading')) {
                    return;
                }
                
                var $activeTab = $btn.parent().prev().find('.tab-content .tab-pane.active');
                var pageTitle = $activeTab.find('.page_title').val() || '';
                var pageContent = $activeTab.find('.editor').summernote('code');

                // 1. replace the modal content with a loading spinner and set the title
                $('#cmsUpdatePreviewModal .preview_title').text(pageTitle);
                $('#cmsUpdatePreviewModal .preview_body').html('<div class="text-center p-4"><i class="fa fa-spinner fa-spin fa-2x"></i> <p>Loading preview...</p></div>');
                
                // 2. open the modal immediately to show the loading state
                $('#cmsUpdatePreviewModal').modal('show');
                
                // block the button to prevent multiple clicks while the transformation is in progress
                $btn.data('loading', true).prop('disabled', true);

                // 3. execute the transformation
                CMS.Editor.TransformPageContent(pageContent)
                    .then(function(transformedContent) {
                        // insert the transformed content into the modal body
                        $('#cmsUpdatePreviewModal .preview_body').html(transformedContent);
                    })
                    .catch(function(err) {
                        console.error('Preview transformation error:', err);
                        $('#cmsUpdatePreviewModal .preview_body').html(pageContent);
                    })
                    .finally(function() {
                        // reblock the button after the transformation is complete
                        $btn.data('loading', false).prop('disabled', false);
                    });

                console.log('Preview button clicked: title=' + pageTitle);
            });

             $(document).on('click', '.copyImageURL', function(thisEvent) {
                let path = $(this).data('relative-path');
                let ajaxImagePreURL = VuFind.path + '/cms/assets' + path;
                $('.note-image-url').val(ajaxImagePreURL);
                $('.note-image-btn').click();
            });

            $(document).on('click', '.copyDocumentURL', function(thisEvent) {
                thisEvent.preventDefault();

                let path = $(this).data('relative-path');
                let ajaxFilePreURL = VuFind.path + '/cms/assets' + path;
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
                //console.log('Note button clicked: ' + noteType);

            });

        },

        InitPluginForPlaceholders: function() {
            $.extend($.summernote.plugins, {

                tuefindPlaceholders: function (context) {

                    var ui = $.summernote.ui;
                    var $editor = context.layoutInfo.editor;

                    var placeholders = [
                        {
                            value: 'transEsc wiki_link',
                            label: 'Translated Display Text'
                        },
                        {
                            value: 'icon send-email',
                            label: 'Icon'
                        },
                        {
                            value: 'url content-page [page=>A_Z]',
                            label: 'URL'
                        },
                        {
                            value: 'imageLink Logo_Universitaet_Tuebingen.svg',
                            label: 'Image (from theme hierarchy)'
                        }
                    ];

                    /*
                     * Toolbar-Button
                     */
                    context.memo('button.tuefindPlaceholders', function () {

                        return ui.button({
                            contents: '<span>{{ }}</span>',

                            click: function () {

                                /*
                                 * Cursorposition sichern.
                                 */
                                context.invoke('editor.saveRange');

                                /*
                                 * Vorhandenes Dropdown entfernen.
                                 */
                                $('.tuefind-placeholders-menu').remove();

                                /*
                                 * Dropdown erzeugen.
                                 */
                                var $menu = $('<div>', {
                                    'class': 'tuefind-placeholders-menu'
                                });

                                $.each(placeholders, function (index, placeholder) {

                                    var $item = $('<button>', {
                                        'type': 'button',
                                        'class': 'tuefind-placeholders-item',
                                        'text': placeholder.label
                                    });

                                    $item.on('click', function () {

                                        /*
                                         * Cursorposition wiederherstellen.
                                         */
                                        context.invoke('editor.restoreRange');

                                        /*
                                         * Platzhalter einfügen.
                                         */
                                        context.invoke(
                                            'editor.insertText',
                                            '{{' + placeholder.value + '}}'
                                        );

                                        /*
                                         * Dropdown schließen.
                                         */
                                        $menu.remove();

                                    });

                                    $menu.append($item);
                                });

                                /*
                                 * Dropdown zunächst unsichtbar
                                 * unter dem Editor platzieren.
                                 */
                                $('body').append($menu);

                                /*
                                 * Position des Buttons ermitteln.
                                 *
                                 * Wir verwenden hier bewusst nicht
                                 * Summernotes Popover-/Tooltip-Logik.
                                 */
                                var $button = $(this);

                                var offset = $button.offset();

                                $menu.css({
                                    position: 'absolute',
                                    top: offset.top + $button.outerHeight(),
                                    left: offset.left,
                                    zIndex: 9999
                                });

                                /*
                                 * Klick außerhalb schließt das Menü.
                                 */
                                setTimeout(function () {

                                    $(document).one(
                                        'click.tuefindPlaceholders',
                                        function (event) {

                                            if (
                                                !$(event.target).closest(
                                                    '.tuefind-placeholders-menu'
                                                ).length
                                            ) {
                                                $menu.remove();
                                            }

                                        }
                                    );

                                }, 0);
                            }

                        }).render();
                    });


                    this.destroy = function () {
                        $('.tuefind-placeholders-menu').remove();
                        $(document).off(
                            'click.tuefindPlaceholders'
                        );
                    };
                }
            });
        },

        _transformerXhr: null,
        _transformerTimer: null,

        TransformPageContent: function(pageContent) {
            var self = this;

            return new Promise(function(resolve) {
                // clear any existing timer to avoid multiple requests
                clearTimeout(self._transformerTimer);

                // if there's an ongoing AJAX request, abort it to avoid race conditions
                if (self._transformerXhr && self._transformerXhr.readyState !== 4) {
                    self._transformerXhr.abort();
                }

                // wait for 300ms before sending the request to avoid sending too many requests in quick succession
                self._transformerTimer = setTimeout(function() {
                    self._transformerXhr = $.ajax({
                        url: VuFind.path + '/AJAX/JSON?method=CmsPageContentTransformer',
                        type: 'POST',
                        data: { content: pageContent },
                        dataType: 'json'
                    })
                    .done(function(response) {
                        if (response && response.data && response.data.content !== undefined) {
                            resolve(response.data.content);
                        } else {
                            resolve(pageContent);
                        }
                    })
                    .fail(function(xhr, status) {
                        if (status !== 'abort') {
                            console.warn('TransformPageContent error or timeout:', status);
                        }
                        resolve(pageContent);
                    });
                }, 300);
            });
        }
    },

    FileManager: {
        Init: function() {

            $(document).on('hide.bs.modal', '.modal', function () {
                if (this.contains(document.activeElement) || document.activeElement === this) {
                    document.activeElement.blur();
                }
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

    GetAJAXDocs: function(ajaxCmsDocsBlockClass='', modeType='') {
        const className = ajaxCmsDocsBlockClass.trim() || 'AJAXCMSDocsBlock';
        const $block =$(`.${className}`);
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
            if (response && response.data) {
                // if response.data is an array, join it into a single string; otherwise, use it as is
                const html = Array.isArray(response.data) ? response.data.join('') : response.data;
                $block.html(html);
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
    GetDocs: function() {
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
    GetImages: function() {
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
                                    <a href="#" class="text-center d-block text-default cms_preview col-6" data-bs-toggle="modal" data-bs-target="#previewModal">
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
