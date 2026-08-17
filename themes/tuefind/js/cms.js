var CMS = {

    InitEditor: function() {
        $('.cms-form-update').on('submit', function () {
            var editor = $('.editor');
            // check if codeview is activated
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
            CMS.TransformPageContent(pageContent).then(transformedContent => {
                $('#exampleModal .preview_body').html(transformedContent);
            });
        });

         $(document).on('click', '.copyImageURL', function(thisEvent) {
            let fullPATH = $(this).data('full-path');
            let ajaxImagePreURL = '/AJAX/JSON?method=CmsDocs&action=getImageContent&full-path='+fullPATH;
            $('.note-image-url').val(ajaxImagePreURL);
            $('.note-image-btn').click();
        })

        $(document).on('click', '.copyDocumentURL', function(thisEvent) {
            thisEvent.preventDefault();

            let fullPATH = $(this).data('full-path');
            let ajaxFilePreURL = '/AJAX/JSON?method=CmsDocs&action=getFileContent&full-path='+fullPATH;
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
};
