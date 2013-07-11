$().ready(function(){
    $.mediaManager.init();
});

(function($){
    $.mediaManager = function () {

    };

    $.mediaManager.init = function() {
        $('.create-media').fancybox({
            autoDimensions: false,
            width: '60%',
            height: '60%',
            scrolling: 'no'
        });

        $.mediaManager.template = twig({
            id: "media",
            href: mediaTemplateUrl,
            async: false
        });

        $('.create-media').click(function(){
            $.mediaManager.triggeringElement = $(this);
            $.mediaManager.medias = false;
        });

        $('.select-media').click(function(e){
            $.mediaManager.triggeringElement = $(this);

            if (!$.mediaManager.medias) {
                $.post( listAjaxUrl, null, function(response){
                    $.mediaManager.medias = response;
                    $.mediaManager.show();
                } );
            } else {
                $.mediaManager.show();
            }

        });
    };

    $.mediaManager.show = function() {
        $.fancybox({
            content: $.mediaManager.template.render($.mediaManager.medias),
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

            $.mediaManager.selected = div.find('input[name=id]').val();
            var selectedMedia = $.mediaManager.medias.medias[$.mediaManager.selected];
            divDetails.find('h4').html(selectedMedia.name);
            divDetails.find('a#edit-media-link').attr('href', selectedMedia.editLink);
            divDetails.find('img').attr('src', selectedMedia.path);
            divDetails.show();
        });

        $('#insert-media').click(function(e){
            e.preventDefault();
            $.mediaManager.insert();
        });
    };

    $.mediaManager.insert = function() {
        var parent = $.mediaManager.triggeringElement.parent();
        var selectedMedia = $.mediaManager.medias.medias[$.mediaManager.selected];

        parent.find('.input-media').val(selectedMedia.id);
        parent.find('.image-media').attr('src', selectedMedia.path);
        $.fancybox.close();
    };
}(jQuery));
