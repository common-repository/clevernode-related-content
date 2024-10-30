// Main JS
jQuery(document).ready(function($) {

    // Initiate Color Picker
    $('.wp-color-picker-field').wpColorPicker();

    $("#element_clevernode_connect").on('submit', function(e) {
        $(e.target).find('input[type="submit"]').attr('disabled', 'disabled');
    });

});
// Banner + Modal
jQuery(document).ready(function ($) {
    // Banner
    var banner = $('.clevernode-banner'),
        closeBanner = $('#close-banner'),
        bannerCookie = $.cookie('clevernode-banner');

    if(bannerCookie !== 'dismiss') {
        setTimeout(function() {
            banner.removeClass('hide');
        }, 3000);
    }

    closeBanner.on('click', function(e){
        e.preventDefault();
        $(e.target).parents('.clevernode-banner').addClass('hide');

        $.cookie('clevernode-banner', 'dismiss', {
            expires: 7,
            path: '/',
        });
    });

    // Modal
    var modal = $(".clevernode-modal"),
        closeModal = $("#close-modal");

    setTimeout(function() {
        modal.addClass('hide');
    }, 8000);

    closeModal.on('click', function(e){
        e.preventDefault();
        $(e.target).parents('.clevernode-modal').addClass('hide');
    });
});

// Media file uploader
jQuery(document).ready(function($) {    

    $('.wpsa-browse').on('click', function (event) {
        event.preventDefault();

        var self = $(this);

        // Create the media frame.
        var file_frame = wp.media.frames.file_frame = wp.media({
            title: self.data('uploader_title'),
            button: {
                text: self.data('uploader_button_text'),
            },
            multiple: false
        });

        file_frame.on('select', function () {
            attachment = file_frame.state().get('selection').first().toJSON();
            self.prev('.wpsa-url').val(attachment.url).change();
        });

        // Finally, open the modal
        file_frame.open();
    });

    // Media image uploader
    $('body').on( 'click', '.wp-media-upl', function(e){

        e.preventDefault();

        var button = $(this),
        custom_uploader = wp.media({
            title: 'Insert image',
            library : {
                // uploadedTo : wp.media.view.settings.post.id, // attach to the current post?
                type : 'image'
            },
            button: {
                text: 'Use this image' // button label text
            },
            multiple: false
        }).on('select', function() { // it also has "open" and "close" events
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            button.html('<img src="' + attachment.sizes.medium.url + '">').removeClass('button button-small')
            .parent().find('input.image-data').val(attachment.id);
            button.parent().find('.wp-media-rmv').css({'display':'inline-block'});
        }).open();
    
    });

    // on remove button click
    $('body').on('click', '.wp-media-rmv', function(e){
        e.preventDefault();

        var button = $(this);
        button.parent().find('input.image-data').val('');
        button.hide().parent().find('.wp-media-upl').html('Upload image').addClass('button button-small');
    });

});
// Radio switch
jQuery(document).ready(function($) {

    var radioSwitch = $('.radio-switch input[type=radio], .checkbox-switch input[type=checkbox]');
    var subscription = $('input#subscription_active');

    $('.switch-no').hide();
    $('.switch-yes').hide();
    $('.switch-default').show();

    subscription.on( 'change', function(e) {
        var submit = $(e.target).parents("form").find(".input-holder"),
            nav = $(".nav-tab-wrapper");
        if( $(e.target).val() === 'yes' || this.checked ) {
            submit.show();
            nav.find('.switch-yes').show();
            nav.find('.switch-no').hide();
        } else {
            submit.hide();
            nav.find('.switch-no').show();
            nav.find('.switch-yes').hide();
        }
    });

    radioSwitch.on( 'change', function(e){
        var group = $(e.target).parents(".group"),
            group_id = group.attr("id"),
            elem = group.parents('.metabox-holder').find(`#element_${group_id}`);

        if( $(e.target).attr('type') === 'radio' && $(e.target).val() === 'yes' || $(e.target).attr('type') === 'checkbox' && this.checked ) {
            check_switch_on( elem );

            group.find('.switch-yes').show();
            group.find('.switch-no').hide();
        } else {
            check_switch_off( elem );

            group.find('.switch-no').show();
            group.find('.switch-yes').hide();
        }
    });

    // Check switch "on" for elements
    function check_switch_on(item) {
        if ( item.hasClass('switch-yes') ) {
            item.show().addClass('on').removeClass('off');
        } else {
            item.hide().addClass('off').removeClass('on');
        }
    }

    // Check switch "off" for elements
    function check_switch_off(item) {
        if ( item.hasClass('switch-no') ) {
            item.show().addClass('on').removeClass('off');
            //item.prev().find("input[type=submit]").parents('.input-holder').hide();
        } else {
            item.hide().addClass('off').removeClass('on');
            //item.prev().find("input[type=submit]").parents('.input-holder').css({"display":"block"});
        }
    }

});
// Settings Nav
jQuery(document).ready(function($) {

    // Switches option sections
    $('.group').hide();
    var activetab = '';
    if (typeof(localStorage) != 'undefined' ) {
        activetab = localStorage.getItem("activetab");
    }

    // if url has section id as hash then set it as active or override the current local storage value
    if(window.location.hash){
        activetab = window.location.hash;
        if (typeof(localStorage) != 'undefined' ) {
            localStorage.setItem("activetab", activetab);
        }
    }

    if (activetab != '' && $(activetab).length ) {
        var activepartup = $(activetab+'-part-top');
        var activepartdw = $(activetab+'-part-bottom');
        $(activetab).add(activepartup).add(activepartdw).fadeIn();
    } else {
        $('.group:first').fadeIn();
    }
    $('.group .collapsed').each(function(){
        $(this).find('input:checked').parent().parent().parent().nextAll().each(
        function(){
            if ($(this).hasClass('last')) {
                $(this).removeClass('hidden');
                return false;
            }
            $(this).filter('.hidden').removeClass('hidden');
        });
    });

    if (activetab != '' && $(activetab + '-tab').length ) {
        $(activetab + '-tab').addClass('nav-tab-active');
    }
    else {
        $('.nav-tab-wrapper a:first').addClass('nav-tab-active');
    }
    $('.nav-tab-wrapper a').click(function(evt) {
        $('.nav-tab-wrapper a').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active').blur();
        var clicked_group = $(this).attr('href');
        var partup = $(clicked_group+'-part-top');
        var partdw = $(clicked_group+'-part-bottom');
        if (typeof(localStorage) != 'undefined' ) {
            localStorage.setItem("activetab", $(this).attr('href'));
        }
        $('.group').hide();
        $(clicked_group).add(partup).add(partdw).fadeIn();
        evt.preventDefault();

        const url = new URL(window.location);
        window.history.pushState({}, '', $(this).attr('href'));
    });

});