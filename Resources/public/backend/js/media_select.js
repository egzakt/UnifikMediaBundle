$().ready(function(){
    $('.select-media, .create-media').click(function(){
        $.fancybox.triggeringElement = $(this);
    });

    $('.select-media').fancybox({
        autoDimensions: false,
        width: '60%',
        height: '80%'
    });
    $('.create-media').fancybox({
        autoDimensions: false,
        width: '60%',
        height: '60%',
        scrolling: 'no'
    })
});