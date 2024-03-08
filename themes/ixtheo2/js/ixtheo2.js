TueFind2.ExpandDropdownsOnHover = true;

var IxTheo2 = {
    ScrollToSearchForm: function() {
        if($('#searchForm').html() != undefined) {
            let content_block_element = $("#content").html();
            let index_page = $(".index-page").html();
            let search_form_element = document.getElementById("searchForm");
            let ix2_search_form = $(".ix2-searchForm").html();
            if(search_form_element.length > 0 && content_block_element != undefined && index_page == undefined && ix2_search_form != undefined) {
                const y = search_form_element.getBoundingClientRect().top + window.scrollY;
                window.scroll({
                top: y-100,
                behavior: 'smooth'
                });
            }
        }
    },
    ChangeHandlerMenuSearchForm: function() {
        $('.handlers-menu a').click(function() {
            $('#searchForm_typeCaption').html($(this).html());
            $('#searchForm_type').attr('value', $(this).data('value'));
            return false;
        })
    },
}; //end Ixtheo2

// Enable bootstrap3 popovers

$(function () {
    $('[data-toggle="popover"]').popover();

    IxTheo2.ScrollToSearchForm();
    IxTheo2.ChangeHandlerMenuSearchForm();

    if($('.ixtheo2-form').html() == undefined && $('.relbib-form').html() == undefined) { //for now disabled
        $(".searchForm_lookfor:visible").focus();
    }
});
