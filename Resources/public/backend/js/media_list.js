$().ready(function(){
    $('.media-link, .media img').click(function(e){
        e.preventDefault();

        var div = $(this).parent();
        var divDetails = $('#media-details-inner');

        $('.media-selected').removeClass('media-selected');
        div.addClass('media-selected');

        divDetails.find('h4').html(div.find('input[name=name]').val());
        divDetails.find('a#edit-media-link').attr('href', div.find('input[name=edit-link]').val());
        divDetails.find('img').attr('src', div.find('img').attr('src'));
        divDetails.find('input[name=selected-id]').val(div.find('input[name=id]').val());

        divDetails.show();
    });

    $('.media').hide();
    $('.media-image').css('display', 'inline-block');
    $('.media-filter').click(function(e){
        e.preventDefault();
        var type = $(this).find('input').val();
        $('.media').fadeOut().delay(600).parent().find('.media-'+type).css({
            opacity: 0,
            display:'inline-block'
        }).animate({opacity: 1}, 1000);
    });

    $('#insert-media').click(function(e){
        e.preventDefault();

        var details = $('#media-details-inner');
        var id = details.find('input[name=selected-id]').val();
        var src = details.find('img').attr('src');

        var parent = $.fancybox.triggeringElement.parent();
        parent.find('.input-media').val(id);
        parent.find('.image-media').attr('src', src);
        $.fancybox.close();
    });
});
