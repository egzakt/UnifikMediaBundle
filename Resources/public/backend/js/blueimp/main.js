// JQUERY UPLOADER

$(function(){

    // Initialize the jQuery File Upload widget:
    $('#fileupload').fileupload({
        // Uncomment the following to send cross-domain cookies:
        //xhrFields: {withCredentials: true},
        url: Routing.generate('egzakt_media_backend_media_upload')
    });

});