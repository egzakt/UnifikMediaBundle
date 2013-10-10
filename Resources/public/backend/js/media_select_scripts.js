if ('undefined' === typeof flexymediascript){

    var loader1 = new DynamicLoader();

    loader1.addStyle('blueimpgallery', 'http://blueimp.github.io/Gallery/css/blueimp-gallery.min.css');
    loader1.addStyle('blueimpuploader', '/bundles/flexymedia/backend/css/blueimp/jquery.fileupload-ui.css');
    loader1.addStyle('flexymediastyle', '/bundles/flexymedia/backend/css/media_select.css');
    loader1.addStyle('simplepaginationstyle', '/bundles/flexymedia/backend/css/simplePagination.css');


    loader1.addScript('blueimptmpl', 'http://blueimp.github.io/JavaScript-Templates/js/tmpl.min.js');
    loader1.addScript('blueimploadimage', 'http://blueimp.github.io/JavaScript-Load-Image/js/load-image.min.js');
    loader1.addScript('blueimpcanvastoblob', 'http://blueimp.github.io/JavaScript-Canvas-to-Blob/js/canvas-to-blob.min.js');
    loader1.addScript('blueimpgallery', 'http://blueimp.github.io/Gallery/js/jquery.blueimp-gallery.min.js');
    loader1.addScript('blueimptransport', '/bundles/flexymedia/backend/js/blueimp/jquery.iframe-transport.js');
    loader1.addScript('blueimpfileupload', '/bundles/flexymedia/backend/js/blueimp/jquery.fileupload.js');
    loader1.addScript('simplepagination', '/bundles/flexymedia/backend/js/jquery.simplePagination.js');
    loader1.addScript('jquerycookiescript', '/bundles/flexymedia/backend/js/jquery.cookie.js');
    loader1.addScript('dynatreescript', '/bundles/flexymedia/backend/js/jquery.dynatree.js');
    loader1.addScript('aviaryscript', 'http://feather.aviary.com/js/feather.js');

    loader1.load(function(){

        var loader2 = new DynamicLoader();

        loader2.addScript('blueimpprocess', '/bundles/flexymedia/backend/js/blueimp/jquery.fileupload-process.js');


        loader2.load(function(){

            var loader3 = new DynamicLoader();

            loader3.addScript('blueimpimage', '/bundles/flexymedia/backend/js/blueimp/jquery.fileupload-image.js');
            loader3.addScript('blueimpaudio', '/bundles/flexymedia/backend/js/blueimp/jquery.fileupload-audio.js');
            loader3.addScript('blueimpvideo', '/bundles/flexymedia/backend/js/blueimp/jquery.fileupload-video.js');
            loader3.addScript('blueimpvalidate', '/bundles/flexymedia/backend/js/blueimp/jquery.fileupload-validate.js');
            loader3.addScript('blueimpui', '/bundles/flexymedia/backend/js/blueimp/jquery.fileupload-ui.js');
            loader3.addScript('blueimpjqueryui', '/bundles/flexymedia/backend/js/blueimp/jquery.fileupload-jquery-ui.js');
            loader3.addScript('flexymediascript', '/bundles/flexymedia/backend/js/media_select.js');

            loader3.load();
        });
    });
}
