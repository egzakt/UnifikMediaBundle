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
                    twigjs.attr('src', '/bundles/egzaktmedia/backend/js/twig.js');

                    $('body').append(twigjs);
                    $('body').append(egzaktMediaScript);
                    $('head').append(egzaktMediaStyle);
                    $.mediaManager();
                }

                $.mediaManager.loadCk(editor);
            }
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
                editor.insertHtml('<img src="'+ media.path + '">');
                break;
            case 'document':
                editor.insertHtml('<a href="' + media.mediaUrl + '">' + media.name + '</a>' );
                break;
        }
    }
});


