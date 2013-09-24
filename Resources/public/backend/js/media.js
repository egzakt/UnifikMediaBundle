$(function(){

    bindScripts();

});

function bindScripts() {
    // Reload button
    $('#refresh_media').on('click', function(){
        window.location.reload();
    });

    // Thumbnail, preview
    $('.image-thumbnail').fancybox();
    $('.fancy-video').fancybox({
        'type': 'iframe'
    });

    // Bulk actions
    $('input.checkall').click( function () {
        $(this).closest('table').find(':checkbox').prop('checked', this.checked);
    });

    deleteScript();
}