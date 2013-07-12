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

                    $().ready(function(){
                        $.mediaManager();
                        $.mediaManager.loadCk(editor);
                    });
                }
            }
        }));

        if ( editor.ui.addButton ) {
            editor.ui.addButton( 'Insert media', {
                label: 'Insert media',
                command: 'openmediamanager'
            });
        }
    },

    insertMedia: function( editor, image ) {
        editor.insertHtml('<img src="'+ image.path + '">');
    }
});


