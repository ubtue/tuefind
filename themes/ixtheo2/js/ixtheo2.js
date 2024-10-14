TueFind2.ExpandDropdownsOnHover = true;

var IxTheo2 = {
    ScrollToSearchForm: function() {
        if($('#searchForm').html() != undefined) {
            let anchor = false;
            let content_block_element = $("#content").html();
            let index_page = $(".index-page").html();
            let search_form_element = document.getElementById("searchForm");
            let ix2_search_form = $(".ix2-searchForm").html();
            let URL = $(location).attr("href");
            const find_anchor = URL.split("#");
            if(find_anchor.length == 2) {
                anchor = true;
            }
            if(search_form_element.length > 0 && content_block_element != undefined && index_page == undefined && ix2_search_form != undefined && anchor === false) {
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
        });
    },

    IxTheoSimpleGalley: function(setIDs, setClickAttr) {
        var current_image,
        selector,
        counter = 0;

        $('#show-next-image, #show-previous-image').click(function() {
            if($(this).attr('id') == 'show-previous-image'){
                current_image--;
            } else {
                current_image++;
            }

            selector = $('[data-image-id="' + current_image + '"]');
            updateGallery(selector);
        });

        function updateGallery(selector) {
            let $sel = selector;
            console.log($sel.data('image'));
            current_image = $sel.data('image-id');
            $('#image-gallery-caption').text($sel.data('caption'));
            $('#image-gallery-title').text($sel.data('title'));
            $('#image-gallery-image').attr('src', $sel.data('image'));
            //disableButtons(counter, $sel.data('image-id'));
        }

        if(setIDs == true){
            $('[data-image-id]').each(function(){
                counter++;
                $(this).attr('data-image-id',counter);
            });
        }
        $(setClickAttr).on('click',function(){
            updateGallery($(this));
        });
    }

}; //end Ixtheo2

// Enable bootstrap3 popovers

$(function () {
    $('[data-toggle="popover"]').popover();

    IxTheo2.ScrollToSearchForm();
    IxTheo2.ChangeHandlerMenuSearchForm();

    if($('.ixtheo2-form').html() == undefined && $('.relbib-form').html() == undefined) {
        $(".searchForm_lookfor:visible").focus();
    }

    IxTheo2.IxTheoSimpleGalley(true, 'a.thumbnail');

});
