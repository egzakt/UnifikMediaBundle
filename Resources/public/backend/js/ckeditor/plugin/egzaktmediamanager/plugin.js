CKEDITOR.plugins.add('egzaktmediamanager', {
    init: function( editor ) {
        editor.addCommand( 'openmediamanager', new CKEDITOR.command( editor, {
            exec: function( editor ){

                $('#loading').show();

                $(document).ready(function(){
                    if ('undefined' === typeof egzaktmediascript){

                        var loader = new DynamicLoader();
                        loader.addStyle('pluploadqueuestyle', '/bundles/egzaktmedia/backend/css/jquery.plupload.queue.css');
                        loader.addStyle('uipluploadsytle', '/bundles/egzaktmedia/backend/css/jquery.ui.plupload.css');
                        loader.addStyle('egzaktmediastyle', '/bundles/egzaktmedia/backend/css/media_select.css');
                        loader.addScript('pluploadfull', '/bundles/egzaktmedia/backend/js/plupload/plupload.full.js');
                        loader.addScript('uipluploadscript', '/bundles/egzaktmedia/backend/js/plupload/jquery.ui.plupload/jquery.ui.plupload.js');
                        loader.addScript('pluploadqueuescript', '/bundles/egzaktmedia/backend/js/plupload/jquery.plupload.queue/jquery.plupload.queue.js');
                        loader.addScript('egzaktmediascript', '/bundles/egzaktmedia/backend/js/media_select.js');

                        loader.load(function(){
                            $.mediaManager();
                            $.mediaManager.loadCk(editor);
                        });
                    }else{
                        $.mediaManager.loadCk(editor);
                    }
                });

            },
            allowedContent: 'iframe[!width, !height, !src, data-mediaid, frameborder, allowfullscreen]; img[data-mediaid, !src, alt]; a[data-mediaid, !href]' //http://docs.ckeditor.com/#!/guide/dev_allowed_content_rules
        }));

        if ( editor.ui.addButton ) {
            editor.ui.addButton( 'Insert_media', {
                label: 'Insert media',
                command: 'openmediamanager',
                toolbar: 'link'
            });
        }
    },

    insertMedia: function( editor, media ) {
        switch (media.type){
            case 'image':
                editor.insertHtml('<img data-mediaid="' + media.id + '" src="'+ media.mediaUrl + '">');
                break;
            case 'document':
                editor.insertHtml('<a data-mediaid="' + media.id + '" href="' + media.mediaUrl + '">' + media.name + '</a>' );
                break;
            case 'video':
                editor.insertHtml('<iframe data-mediaid="' + media.id + '" width="560" height="315" frameborder="0"  allowfullscreen src="' + media.mediaUrl + '"></iframe>');
                break;
            case 'embedvideo':
                editor.insertHtml('<iframe data-mediaid="' + media.id + '" width="560" height="315" frameborder="0"  allowfullscreen src="' + media.embedUrl + '"></iframe>');
                break;
        }
    }
});


