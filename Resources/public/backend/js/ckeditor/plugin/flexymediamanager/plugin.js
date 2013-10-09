CKEDITOR.plugins.add('flexymediamanager', {
    init: function( editor ) {
        editor.addCommand( 'openmediamanager', new CKEDITOR.command( editor, {
            exec: function( editor ){

                $('#loading').show();

                $(document).ready(function(){
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
                        loader1.addScript('aviary', 'http://feather.aviary.com/js/feather.js');

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

                                loader3.load(function(){
                                    mediaManagerLoadCk(editor);
                                });
                            });
                        });
                    }else{
                        mediaManagerLoadCk(editor);
                    }
                });

            },
            allowedContent: 'iframe[!width, !height, !src, data-mediaid, frameborder, allowfullscreen]; img[data-mediaid, !src, alt]; a[data-mediaid, !href]' //http://docs.ckeditor.com/#!/guide/dev_allowed_content_rules
        }));

        if ( editor.ui.addButton ) {
            editor.ui.addButton( 'Insert_media', {
                label: 'Insert media',
                command: 'openmediamanager',
                toolbar: 'link',
                icon: this.path + 'insert_media.png'
            });
        }
    },

    insertMedia: function( editor, media ) {
        switch (media.type){
            case 'image':
                editor.insertHtml('<img data-mediaid="' + media.id + '" src="'+ media.url + '">');
                break;
            case 'document':
                editor.insertHtml('<a data-mediaid="' + media.id + '" href="' + media.url + '">' + media.name + '</a>' );
                break;
            case 'video':
                editor.insertHtml('<iframe data-mediaid="' + media.id + '" width="560" height="315" frameborder="0"  allowfullscreen src="' + media.url + '"></iframe>');
                break;
            case 'embedvideo':
                editor.insertHtml('<iframe data-mediaid="' + media.id + '" width="560" height="315" frameborder="0"  allowfullscreen src="' + media.url + '"></iframe>');
                break;
        }
    }
});


