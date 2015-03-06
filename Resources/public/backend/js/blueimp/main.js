// JQUERY UPLOADER
$(function(){
    var fileCount = 0;
    var successes = 0;
    var fails = 0;

    // Initialize the jQuery File Upload widget:
    $('#fileupload').fileupload({
        autoUpload: true,
        url: Routing.generate('unifik_media_backend_media_upload')
    }).bind('fileuploaddone', function(e, data) {
        fileCount++;
        successes++;
        //console.log('fileuploaddone');
        if (fileCount === data.getNumberOfFiles()) {
            //console.log('all done, successes: ' + successes + ', fails: ' + fails);

            fileCount = 0;
            successes = 0;
            fails = 0;

            clearQueue();
            $('a.library').click();
        }
    }).bind('fileuploadfail', function(e, data) {
        fileCount++;
        fails++;
        //console.log('fileuploadfail');
        if (fileCount === data.getNumberOfFiles()) {
            //console.log('all done, successes: ' + successes + ', fails: ' + fails);

            fileCount = 0;
            successes = 0;
            fails = 0;

            clearQueue();
            $('a.library').click();
        }
    });
});
