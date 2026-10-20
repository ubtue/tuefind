var CMS_STATIC = {
    FileManager: {
        Init: function() {
            
            $(document).on('hide.bs.modal', '.modal', function () {
                if (this.contains(document.activeElement) || document.activeElement === this) {
                    document.activeElement.blur();
                }
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
