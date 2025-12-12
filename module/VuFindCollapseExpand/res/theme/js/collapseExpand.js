$(document).ready(function groupItems() {
    $('.results-group-toggle').click(function (e) {
        $(this).parent().toggleClass('active');
        $(this).children('i').toggleClass('fa-arrow-down');
        $(this).children('i').toggleClass('fa-arrow-up');
    });

    // handle checkbox to enable/disable grouping
    $('#grouping-checkbox').change(function (e) {
        var status = this.checked;
        $.ajax({
            dataType: 'json',
            method: 'POST',
            url: VuFind.path + '/AJAX/JSON?method=groupingCheckbox',
            data: { 'status': status },
            success: function () {
                // reload the page
                window.location.reload(true);
            }

        });
    });
});
