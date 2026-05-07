$(document).ready(function () {
    // handle checkbox to enable/disable grouping
    $('#collapse-expand-checkbox').change(function (e) {
        var status = this.checked;
        $.ajax({
            dataType: 'json',
            method: 'POST',
            url: VuFind.path + '/AJAX/JSON?method=collapseExpandCheckbox',
            data: { 'status': status },
            success: function () {
                // reload the page
                window.location.reload(true);
            }
        });
    });
});
