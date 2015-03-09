if ('undefined' === typeof unifikmediascript){

    var loader1 = new DynamicLoader();

    loader1.addStyle('blueimpgallery', '/bundles/unifikmedia/backend/css/blueimp/blueimp-gallery.min.css');
    loader1.addStyle('blueimpuploader', '/bundles/unifikmedia/backend/css/blueimp/jquery.fileupload-ui.css');
    loader1.addStyle('unifikmediastyle', '/bundles/unifikmedia/backend/css/media_select.css');
    loader1.addStyle('simplepaginationstyle', '/bundles/unifikmedia/backend/css/simplePagination.css');
    loader1.addStyle('dynatreestyle', '/bundles/unifikmedia/backend/css/ui.dynatree.css');
    loader1.addStyle('jquerycontextmenustyle', '/bundles/unifikmedia/backend/css/jquery.contextMenu.css');
    loader1.addStyle('select2style', '/bundles/unifikmedia/backend/css/select2/select2.css');


    loader1.addScript('blueimptmpl', '/bundles/unifikmedia/backend/js/tmpl.min.js');
    loader1.addScript('blueimploadimage', '/bundles/unifikmedia/backend/js/blueimp/load-image.all.min.js');
    loader1.addScript('blueimpcanvastoblob', '/bundles/unifikmedia/backend/js/blueimp/canvas-to-blob.min.js');
    loader1.addScript('blueimpgallery', '/bundles/unifikmedia/backend/js/blueimp/jquery.blueimp-gallery.min.js');
    loader1.addScript('blueimptransport', '/bundles/unifikmedia/backend/js/blueimp/jquery.iframe-transport.js');
    loader1.addScript('blueimpfileupload', '/bundles/unifikmedia/backend/js/blueimp/jquery.fileupload.js');
    loader1.addScript('jquerycookiescript', '/bundles/unifikmedia/backend/js/jquery.cookie.js');
    loader1.addScript('dynatreescript', '/bundles/unifikmedia/backend/js/jquery.dynatree.js');
    loader1.addScript('aviaryscript', '/bundles/unifikmedia/backend/js/feather.js');
    loader1.addScript('jqueryuipositionscript', '/bundles/unifikmedia/backend/js/jquery.ui.position.js');
    loader1.addScript('jquerycontextmenuscript', '/bundles/unifikmedia/backend/js/jquery.contextMenu.js');
    loader1.addScript('select2script', '/bundles/unifikmedia/backend/js/select2/select2.min.js');

    loader1.load(function(){

        var loader2 = new DynamicLoader();

        loader2.addScript('blueimpprocess', '/bundles/unifikmedia/backend/js/blueimp/jquery.fileupload-process.js');


        loader2.load(function(){

            var loader3 = new DynamicLoader();

            loader3.addScript('blueimpimage', '/bundles/unifikmedia/backend/js/blueimp/jquery.fileupload-image.js');
            loader3.addScript('blueimpaudio', '/bundles/unifikmedia/backend/js/blueimp/jquery.fileupload-audio.js');
            loader3.addScript('blueimpvideo', '/bundles/unifikmedia/backend/js/blueimp/jquery.fileupload-video.js');
            loader3.addScript('blueimpvalidate', '/bundles/unifikmedia/backend/js/blueimp/jquery.fileupload-validate.js');
            loader3.addScript('blueimpui', '/bundles/unifikmedia/backend/js/blueimp/jquery.fileupload-ui.js');
            loader3.addScript('blueimpjqueryui', '/bundles/unifikmedia/backend/js/blueimp/jquery.fileupload-jquery-ui.js');
            loader3.addScript('unifikmediascript', '/admin/media/main.js');

            loader3.load();
        });
    });
}
