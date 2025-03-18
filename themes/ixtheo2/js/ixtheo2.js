TueFind2.ExpandDropdownsOnHover = true;

var IxTheo2 = {
    ScrollToAnchor: function() {
        let is_anchor = false;
        let content_block_element = $("#content").html();
        let index_page = $(".index-page").html();
        let ix2_search_form = $(".ix2-searchForm").html();
        let URL = $(location).attr("href");
        let anchor_name = '';
        const find_anchor = URL.split("#");
        let default_scroll = 100;
        let is_js_anchor = false;
        let is_more_anchor = false;
        if(find_anchor.length == 2) {
            is_anchor = true;
            anchor_name = find_anchor[1];
            const find_in_anchor = anchor_name.split("_");
            if(find_in_anchor.length == 2) {
                if(find_in_anchor[0] == 'js'){
                    is_js_anchor = true;
                    anchor_name = find_in_anchor[1];
                }
                if(find_in_anchor[0] == 'more'){
                    is_more_anchor = true;
                    anchor_name = find_in_anchor[1];
                }
            }
        }
        if($('#searchForm').html() != undefined) {
            if(is_anchor === false) {
                //$(".searchForm_lookfor:visible").focus();
            }else{
                if(content_block_element != undefined && index_page == undefined && ix2_search_form != undefined && anchor_name.length > 0) {
                    let search_form_element = document.getElementById(anchor_name);
                    let y = search_form_element.getBoundingClientRect().top + window.scrollY;
                    let toggle_block = $('#'+anchor_name);
                    if(is_js_anchor === true) {
                        y = toggle_block.prev().offset().top;
                        toggle_block.addClass('in');
                    }
                    if(is_more_anchor === true) {
                        y = toggle_block.parent().offset().top;
                        toggle_block.parent().find('.read-more-trigger').click();
                    }
                    window.scroll({
                        top: y-default_scroll,
                        behavior: 'smooth'
                    });
                }else{
                    //$(".searchForm_lookfor:visible").focus();
                }
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
    },

    // Initial drawing of the map
    DrawMap: function(group,partnersArray=[]) {

        cmap = L.map('map');

        L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors',
        }).addTo(cmap);

        let markers = {
            'comm': {
                'zoom': 4,
                'targetCoordinates': {
                    lat: "47",
                    lon: "8"
                },
                'color': '#495057',
                'icon': 'fa-solid fa-people-group'
            },
            'bibliographien': {
                'zoom': 5,
                'targetCoordinates': {
                    lat: "49",
                    lon: "7.110015253718861"
                },
                'color': '#495057',
                'icon': 'fa-solid fa-bookmark'
            },
            'bibliotheken': {
                'zoom': 4,
                'targetCoordinates': {
                    lat: "46",
                    lon: "-30"
                },
                'color': '#495057',
                'icon': 'fa-solid fa-book-open'
            },
            'ver': {
                'zoom': 8,
                'targetCoordinates': {
                    lat: "52.08",
                    lon: "7.110015253718861"
                },
                'color': '#495057',
                'icon': 'fa-solid fa-people-roof'
            },
            'fachin': {
                'zoom': 6,
                'targetCoordinates': {
                    lat: "50.3",
                    lon: "10"
                },
                'color': '#495057',
                'icon': 'fa-solid fa-circle-nodes'
            }
        };

        // Set map's center to target with zoom 3.
        let zoom = 3;
        let targetCoordinates = {
            lat: "50.72319649759073",
            lon: "7.110015253718861"
        }

        partnersArray.forEach(function(oneParter){

            let groupMarker = markers[oneParter.group];

            let myIcon = L.divIcon({
                html: '<i class="fa '+groupMarker.icon+'" style="color: '+groupMarker.color+'"></i>',
                className: "icons-"+oneParter.group,
                iconSize: [30, 30]
            });

            let iconOptions = {
                title: oneParter.name,
                draggable:false,
                icon:myIcon
            }

            let marker = new L.Marker([oneParter.lat, oneParter.lon] , iconOptions);

            if(group != 'all') {
                if(group == oneParter.group) {
                    marker.addTo(cmap);
                    zoom = groupMarker['zoom'];
                    targetCoordinates = groupMarker['targetCoordinates'];
                }
            }else{
                marker.addTo(cmap);
            }

            marker.bindPopup(oneParter.popup).openPopup();
        });

        // Target's GPS coordinates.
        var target = L.latLng(targetCoordinates['lat'], targetCoordinates['lon']);

        cmap.setView(target, zoom);
    }

}; //end Ixtheo2

// Enable bootstrap3 popovers

$(function () {

    var defaultScroll = $(window).scrollTop();
    if(defaultScroll > 50) {
        $('.navigation').addClass('sticky-header');
    }

    $('[data-toggle="popover"]').popover();

    IxTheo2.ChangeHandlerMenuSearchForm();
    IxTheo2.ScrollToAnchor();

    IxTheo2.IxTheoSimpleGalley(true, 'a.thumbnail');

    $('.logo-tooltip').tooltip({
        position: {
          my: "center bottom-5",
          at: "center top",
          using: function( position, feedback ) {
            $( this ).css( position );
            $( "<div>" )
              .addClass( "arrow" )
              .addClass( feedback.vertical )
              .addClass( feedback.horizontal )
              .appendTo( this );
          }
        },
        content: function() {
          var element = $( this );
            return element.find('.ix-copyright-name').html();
        }
    });

    /* ========================================================================= */
    /*	Page Preloader
    /* ========================================================================= */
    $(window).on('load', function () {
        $('#preloader').fadeOut('slow', function () {
          $(this).remove();
        });
      });
      // navbarDropdown
      if ($(window).width() < 992) {
            $('#navigation .dropdown-toggle').on('click', function () {
                $(this).siblings('.dropdown-menu').animate({
                    height: 'toggle'
                }, 300);
            });
      }
      /*
      /* ========================================================================= */
      /*	On scroll fade/bounce effect
      /* ========================================================================= */
      var scroll = new SmoothScroll('a[href*="#"]');

      // -----------------------------
      //  On Scroll
      // -----------------------------
      $(window).scroll(function () {  
        var scroll = $(window).scrollTop();
        if (scroll > 50) {
          $('.navigation').addClass('sticky-header');
        } else {
          $('.navigation').removeClass('sticky-header');
        }
      });
    
      $('.ix2-search-form-bottom-nav .bibel-button').click(function(){
        $('.searchForm_type option').each(function(){
            if($(this).val() == 'BibleRangeSearch') {
                $(this).attr('selected',true);
            }
        })
        $(this).parent().addClass('active');
      });

      if($('.ix2-searchForm .searchForm_type').val() == 'BibleRangeSearch') {
        $('.ix2-search-form-bottom-nav .bibel-button').parent().addClass('active');
      }

});
