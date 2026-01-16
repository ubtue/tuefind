$(document).ready(function () {
  $('.collapse-expand-toggle').click(function (e) {
    $(this).parent().toggleClass('active');
    $(this).children('i').toggleClass('fa-arrow-down');
    $(this).children('i').toggleClass('fa-arrow-up');
  });

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
