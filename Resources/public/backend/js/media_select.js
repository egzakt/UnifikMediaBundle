(function($){
    var listContent = {}, selectedMedia;

    $.mediaManager = function () {
        $('.create-media').fancybox({
            autoDimensions: false,
            width: '60%',
            height: '48%',
            scrolling: 'no',
            href: Routing.generate('egzakt_media_backend_media_upload_fancybox')
        });

        $.mediaManager.template = twig({
            id: "media",
            href: '/bundles/egzaktmedia/backend/js/templates/media.twig',
            async: false
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

        if (!listContent.medias) {
            $.post( Routing.generate('egzakt_media_backend_media_list_ajax'), null, function(response){
                listContent.medias = response.medias;
                $.mediaManager.show();
            });
        } else {
            $.mediaManager.show();
        }
    };

    $.mediaManager.show = function() {
        if ($.mediaManager.isCk) {
            listContent.mediaType = ['image', 'video', 'document'];
        } else {
            listContent.mediaType = $.mediaManager.triggeringElement.data('media-type');
        }

        $.fancybox({
            content: $.mediaManager.template.render(listContent),
            autoDimensions: false,
            width: '90%',
            height: 600
        });
        $('.media img').click(function(e){
            e.preventDefault();

            var div = $(this).parent();
            var divDetails = $('#media-details-inner');

            $('.media-selected').removeClass('media-selected');
            div.addClass('media-selected');

            selectedMedia = listContent.medias[div.data('media-manager-id')];
            divDetails.find('h3').html(selectedMedia.name);
            divDetails.find('a#edit-media-link').attr('href', selectedMedia.editLink);
            divDetails.find('img').attr('src', selectedMedia.pathLarge);
            divDetails.find('#file-size').find('span').html((selectedMedia.size / 1024).toFixed(2));

            if ('image' == selectedMedia.type) {
                var imageFormat = $('#image-format');
                imageFormat.find('span').html(selectedMedia.width + ' X ' + selectedMedia.height);
                imageFormat.show();
            } else {
                divDetails.find('#image-format').hide();
            }

            if (null != selectedMedia.caption) {
                var fileCaption = $('#file-caption');
                fileCaption.find('span').html(selectedMedia.caption);
                fileCaption.show();
            } else {
                $('#file-caption').hide();
            }

            $('#welcome-message').hide();
            divDetails.show();
        });

        $('#insert-media').click(function(e){
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

        parent.find('.input-media').val(selectedMedia.id);
        parent.find('.image-media').attr('src', selectedMedia.path);
        $.fancybox.close();
    };

    $.mediaManager.insertCk = function() {
        CKEDITOR.plugins.get('egzaktmediamanager').insertMedia($.mediaManager.triggeringElement, selectedMedia);
        $.fancybox.close();
        this.isCk = false;
    };
}(jQuery));
