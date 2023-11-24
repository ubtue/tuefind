TueFind2.ExpandDropdownsOnHover = true;

var IxTheo2 = {
    ScrollToSearchForm: function() {
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
    },
}; //end Ixtheo2

// Enable bootstrap3 popovers

$(function () {
    $('[data-toggle="popover"]').popover();

    IxTheo2.ScrollToSearchForm();

    $(".searchForm_lookfor:visible").focus();

});
