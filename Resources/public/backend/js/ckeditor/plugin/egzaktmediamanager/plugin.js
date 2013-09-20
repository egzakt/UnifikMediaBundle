CKEDITOR.plugins.add('egzaktmediamanager', {
    init: function( editor ) {
        editor.addCommand( 'openmediamanager', new CKEDITOR.command( editor, {
            exec: function( editor ){
                if ('undefined' === typeof egzaktMediaScript){
                    var loader = new DynamicLoader();
                    loader.addScript('egzaktmediascript', '/bundles/egzaktmedia/backend/js/media_select.js');
                    loader.addScript('twigjsscript', '/bundles/egzaktsystem/backend/js/twig.js');
                    loader.addStyle('egzaktmediastyle', '/bundles/egzaktmedia/backend/css/media.css');
                    loader.load(function(){
                        $.mediaManager();
                        $.mediaManager.loadCk(editor);
                    });
                }else{
                    $.mediaManager.loadCk(editor);
                }
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


