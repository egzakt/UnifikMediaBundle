$(function(){

    // Create the media select container
    var modal = '<div id="media_select_modal_container"><div id="media_select_modal" title="Medias"></div></div>';
    $('body').append($(modal));

    var listContent = {}, selectedMedia;

    $.mediaManager = function () {
        $('.create-media').fancybox({
            autoDimensions: false,
            width: '60%',
            height: '48%',
            scrolling: 'no',
            href: Routing.generate('egzakt_media_backend_media_upload_fancybox')
        });

        $('.create-media').click(function(){
            $.mediaManager.triggeringElement = $(this);
            listContent.medias = false;
        });

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
            height: 'auto',
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

        // PLUPLOAD

        $("#uploader").plupload({
            // General settings
            runtimes : 'html5,gears,flash,silverlight',
            url : Routing.generate('egzakt_media_backend_media_upload_fancybox'),
            max_file_size : '20mb',
            chunk_size : 0, // chunk disabled cause we don't handle it
            unique_names : true,

            // Flash settings
            flash_swf_url : '/plupload/js/plupload.flash.swf',

            // Silverlight settings
            silverlight_xap_url : '/plupload/js/plupload.silverlight.xap'
        });

        var uploader = $('#uploader').plupload('getUploader');

        uploader.bind('Error', function(up, error){
            $('.plupload_header_text').html(error.message);
        });

        uploader.bind('FileUploaded', function(up, file, data){
            var response = $.parseJSON(data.response);
            if(response.error){
                up.trigger('error', response);
            }
            $('.plupload_header_text').html(response.message);
            file.name = '<a href="' + response.url + '">' + file.name  + "</a>";
        });

        $('form#pluploader-form').submit(function(e) {
            var uploader = $('#uploader').plupload('getUploader');

            if (uploader.files.length > 0) {
                uploader.bind('StateChanged', function() {
                    if (uploader.files.length === (uploader.total.uploaded + uploader.total.failed)) {
                        $('form')[0].submit();
                    }
                });

                uploader.start();
            } else {
                alert('{% trans %}You must at least upload one file.{% endtrans %}');
            }

            return false;
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