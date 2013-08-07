CKEDITOR.plugins.add('egzaktmediamanager', {
    init: function( editor ) {
        editor.addCommand( 'openmediamanager', new CKEDITOR.command( editor, {
            exec: function( editor ){
                if ('undefined' === typeof egzaktMediaScript){
                    egzaktMediaScript = $('<script>');
                    egzaktMediaScript.attr('src', '/bundles/egzaktmedia/backend/js/media_select.js');

                    egzaktMediaStyle = $('<link rel="stylesheet">');
                    egzaktMediaStyle.attr('href', '/bundles/egzaktmedia/backend/css/media.css');

                    twigjs = $('<script>');
                    twigjs.attr('src', '/bundles/egzaktsystem/backend/js/twig.js');

                    $('body').append(twigjs);
                    $('body').append(egzaktMediaScript);
                    $('head').append(egzaktMediaStyle);
                    $.mediaManager();
                }

                $.mediaManager.loadCk(editor);
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
                editor.insertHtml('<img data-mediaid="' + media.id + '" src="'+ media.path + '">');
                break;
            case 'document':
                editor.insertHtml('<a data-mediaid="' + media.id + '" href="' + media.mediaUrl + '">' + media.name + '</a>' );
                break;
            case 'video':
                editor.insertHtml('<iframe data-mediaid="' + media.id + '" width="560" height="315" frameborder="0"  allowfullscreen src="' + media.embedUrl + '"></iframe>');
                break;
        }
    }
});


