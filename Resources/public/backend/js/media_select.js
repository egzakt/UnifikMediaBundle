var mediaManagerSelectedMedia = {};
var mediaManagerTriggeringElement;
var mediaManagerTypeLoaded = [];
var mediaManagerScriptsBinded = false;
var mediaManagerIsCk = false;

// Create the media select container
$('body').append($('<div id="media_select_modal_container"><div id="media_select_modal" title="Medias"></div></div>'));
var mediaManagerModal = $('#media_select_modal');

$('.select-media').click(function(){
    mediaManagerTriggeringElement = $(this);
    mediaManagerLoad($(this).data('media-type'), true, mediaManagerShow);
});

var mediaManagerLoadCk = function (editor) {
    mediaManagerIsCk = true;
    mediaManagerTriggeringElement = editor;
    mediaManagerLoad('image', true, mediaManagerShow);
};

var mediaManagerLoad = function (type, init, callback) {

    if (-1 == $.inArray(type, mediaManagerTypeLoaded)) {

        $.ajax({
            url: Routing.generate('egzakt_media_backend_media_select_pager'),
            data: {
                type: type,
                page: 1,
                view: (mediaManagerIsCk) ? 'ckeditor' : 'mediafield',
                init: init
            },
            async: false,
            dataType: 'json',
            success: function (data) {

                mediaManagerTypeLoaded.push(type);

                // Append html content
                if (init) {
                    mediaManagerModal.html($(data.html));
                } else {
                    $('#media_list div.' + type).html($(data.html));
                }

            },
            error: function () {

                // Append html content
                mediaManagerModal.html($('<h2>Internal Server Error</h2>'));

            }
        });
    }

    if (false == mediaManagerScriptsBinded) {
        mediaManagerBind();
        mediaManagerScriptsBinded = true;
    }

    if (undefined != callback) {
        callback();
    }
};

var mediaManagerShow = function () {

    $(document).on('click', '.ui-widget-overlay', function(){ mediaManagerModal.dialog('close'); });

    mediaManagerModal.dialog({
        modal: true,
        dialogClass: 'media_select',
        width: 'auto',
        height: 600,
        minHeight: 400,
        minWidth: 400,
        position: {
            my: 'left top',
            at: 'left top',
            of: window
        },
        buttons: {
            Close: function() {
                $( this ).dialog( "close" );
            }
        }
    });

    $('#loading').hide();

};

var mediaManagerBind = function () {

    // FILTERS SELECTION SCRIPT

    if (mediaManagerIsCk) {

        $('#media_filters').on('click', '.media_filter', function(e){

            e.preventDefault();

            var loading = $('#loading');

            loading.show();

            $('#uploader_wrapper').hide();
            $('#media_wrapper').show();

            var type = $(e.target).data('media-type');

            mediaManagerLoad(type, false);

            var mediaList = $('#media_list');

            mediaList.find('div.list').hide();
            mediaList.find('div.' + type).show();

            loading.hide();

        });
    }

    // UPLOADER BUTTON

    $('#media_filters').on('click', '.upload', function(e){

        e.preventDefault();

        $('#media_wrapper').hide();
        $('#uploader_wrapper').show();

        mediaManagerTypeLoaded = [];

    });

    // JQUERY UPLOADER

    // Initialize the jQuery File Upload widget:
    $('#fileupload').fileupload({
        // Uncomment the following to send cross-domain cookies:
        //xhrFields: {withCredentials: true},
        url: Routing.generate('egzakt_media_backend_media_upload')
    });

    // MEDIA SELECTION SCRIPT

    $('#media_list').on('click', '.media img', function(e){
        e.preventDefault();

        var div = $(this).parent();
        var divDetails = $('#media_details_inner');

        $('.media_selected').removeClass('media_selected');
        div.addClass('media_selected');

        mediaManagerSelectedMedia.id = div.data('media-id');
        mediaManagerSelectedMedia.name = div.data('media-name');
        mediaManagerSelectedMedia.type = div.data('media-type');
        mediaManagerSelectedMedia.preview = div.data('media-preview');
        mediaManagerSelectedMedia.url = div.data('media-url');
        mediaManagerSelectedMedia.edit = div.data('media-edit');
        mediaManagerSelectedMedia.size = div.data('media-size');
        mediaManagerSelectedMedia.caption = div.data('media-caption');

        if ('image' == mediaManagerSelectedMedia.type) {
            mediaManagerSelectedMedia.width = div.data('media-width');
            mediaManagerSelectedMedia.height = div.data('media-height');
        }


        divDetails.find('h3').html(mediaManagerSelectedMedia.name);
        divDetails.find('a#edit_media_link').attr('href', mediaManagerSelectedMedia.edit);
        divDetails.find('#aviary_image').attr('src', mediaManagerSelectedMedia.preview);
        divDetails.find('#file_size').find('span').html((mediaManagerSelectedMedia.size / 1024).toFixed(2));

        if ('image' == mediaManagerSelectedMedia.type) {

            $('#edit-aviary').show();

            $('#media_details').on('click' , '#edit-aviary', function(e) {

                $('#aviary_image').addClass('aviary');

                return launchEditor('aviary_image');

            });

            $('#aviary_path').val(mediaManagerSelectedMedia.aviary);

            var imageFormat = $('#image_format');
            imageFormat.find('span').html(mediaManagerSelectedMedia.width + ' X ' + mediaManagerSelectedMedia.height);
            imageFormat.show();
        } else {
            $('#edit-aviary').hide();
            divDetails.find('#image_format').hide();
        }

        if ('' != mediaManagerSelectedMedia.caption) {
            var fileCaption = $('#file_caption');
            fileCaption.find('span').html(mediaManagerSelectedMedia.caption);
            fileCaption.show();
        } else {
            $('#file_caption').hide();
        }

        $('#welcome_message').hide();
        divDetails.show();
    });

    $('#insert_media').click(function(e){
        e.preventDefault();
        mediaManagerInsert();
    });

    $('#media_list').on('dblclick' , '.media img', function(e) {
        e.preventDefault();
        mediaManagerInsert();
    });
}

var mediaManagerInsert = function() {
    if (mediaManagerIsCk){
        mediaManagerInsertCk();
        return;
    }

    var parent = mediaManagerTriggeringElement.parent();

    parent.find('.input_media').val(mediaManagerSelectedMedia.id);
    parent.find('.image_media').attr('src', mediaManagerSelectedMedia.url);

    mediaManagerModal.dialog('close');
};

var mediaManagerInsertCk = function() {
    CKEDITOR.plugins.get('egzaktmediamanager').insertMedia(mediaManagerTriggeringElement, mediaManagerSelectedMedia);

    mediaManagerModal.dialog('close');

    mediaManagerIsCk = false;
};