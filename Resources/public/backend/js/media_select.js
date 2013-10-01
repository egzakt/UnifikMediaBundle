$(function(){

    // Create the media select container
    var modal = '<div id="media_select_modal_container"><div id="media_select_modal" title="Medias"></div></div>';
    $('body').append($(modal));

    var listContent = {}, selectedMedia;

    $.mediaManager = function () {

        $('.select-media').click(function(){
            $.mediaManager.load($(this));
        });
    };

    $.mediaManager.loadCk = function (editor) {
        this.isCk = true;
        this.load(editor);
    };

    $.mediaManager.load = function (trigerringElement) {
        this.triggeringElement = trigerringElement;

        if (undefined == listContent.html) {

            $.ajax({
                url: Routing.generate('egzakt_media_backend_media_list_ajax'),
                data: { type: ($.mediaManager.isCk) ? 'all' : $.mediaManager.triggeringElement.data('media-type') },
                dataType: 'json',
                success: function (data) {
                    listContent.html = data.html;
                    listContent.medias = data.medias;
                    $.mediaManager.show();
                },
                error: function () {
                    listContent.html = '<h2>Internal Server Error</h2>';
                    $.mediaManager.show();
                }
            });

        } else {
            $.mediaManager.show();
        }
    };

    $.mediaManager.show = function() {

        if ($.mediaManager.isCk) {
            listContent.mediaType = ['image', 'video', 'document', 'embedvideo'];
        } else {
            listContent.mediaType = $.mediaManager.triggeringElement.data('media-type');
        }

        var modal = $('#media_select_modal');

        // Append html content
        modal.html($(listContent.html));

        $(document).on('click', '.ui-widget-overlay', function(){ modal.dialog('close'); });

        modal.dialog({
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

        // FILTERS SELECTION SCRIPT

        if ($.mediaManager.isCk) {

            $('#media_list .media').hide();
            $('#media_list .media_image').show();

            $('#media_filters').on('click', '.media_filter', function(e){

                e.preventDefault();

                $('#uploader_wrapper').hide();
                $('#media_wrapper').show();

                var type = $(e.target).data('media-type');

                $('#media_list').find('.media').hide();
                $('#media_list').find('.media_' + type).show();

            });

        }

        // JQUERY UPLOADER

        // Initialize the jQuery File Upload widget:
        $('#fileupload').fileupload({
            // Uncomment the following to send cross-domain cookies:
            //xhrFields: {withCredentials: true},
            url: Routing.generate('egzakt_media_backend_media_upload')
        });


        // Load existing files:
        $('#fileupload').addClass('fileupload-processing');
        $.ajax({
            // Uncomment the following to send cross-domain cookies:
            //xhrFields: {withCredentials: true},
            url: $('#fileupload').fileupload('option', 'url'),
            dataType: 'json',
            context: $('#fileupload')[0]
        }).always(function () {
            $(this).removeClass('fileupload-processing');
        }).done(function (result) {
            $(this).fileupload('option', 'done')
                .call(this, null, {result: result});
        });

        // UPLOADER BUTTON

        $('#media_filters').on('click', '.upload', function(e){

            e.preventDefault();

            $('#media_wrapper').hide();
            $('#uploader_wrapper').show();

        });

        // IMAGE SELECTION SCRIPT

        $('.media img').click(function(e){
            e.preventDefault();

            var div = $(this).parent();
            var divDetails = $('#media_details_inner');

            $('.media_selected').removeClass('media_selected');
            div.addClass('media_selected');

            selectedMedia = listContent.medias[div.data('media-manager-id')];
            divDetails.find('h3').html(selectedMedia.name);
            divDetails.find('a#edit_media_link').attr('href', selectedMedia.editLink);
            divDetails.find('img').attr('src', selectedMedia.pathLarge);
            divDetails.find('#file_size').find('span').html((selectedMedia.size / 1024).toFixed(2));

            if ('image' == selectedMedia.type) {
                var imageFormat = $('#image_format');
                imageFormat.find('span').html(selectedMedia.width + ' X ' + selectedMedia.height);
                imageFormat.show();
            } else {
                divDetails.find('#image_format').hide();
            }

            if (null != selectedMedia.caption) {
                var fileCaption = $('#file_caption');
                fileCaption.find('span').html(selectedMedia.caption);
                fileCaption.show();
            } else {
                $('#file_caption').hide();
            }

            $('#welcome_message').hide();
            divDetails.show();
        });

        $('#insert_media').click(function(e){
            e.preventDefault();
            $.mediaManager.insert();
        });

        $('.media img').dblclick(function(e) {
            e.preventDefault();
            $.mediaManager.insert();
        });

    };

    $.mediaManager.insert = function() {
        if (this.isCk){
            this.insertCk();
            return;
        }

        var parent = this.triggeringElement.parent();

        parent.find('.input_media').val(selectedMedia.id);
        parent.find('.image_media').attr('src', selectedMedia.path);

        $('#media_select_modal').dialog('close');
    };

    $.mediaManager.insertCk = function() {
        CKEDITOR.plugins.get('egzaktmediamanager').insertMedia($.mediaManager.triggeringElement, selectedMedia);

        $('#media_select_modal').dialog('close');

        this.isCk = false;
    };

});