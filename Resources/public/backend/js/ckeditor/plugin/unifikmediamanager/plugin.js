CKEDITOR.plugins.add('unifikmediamanager', {
    init: function( editor ) {

        $(function(){

            if ('undefined' === typeof unifikmediaselectscript){

                var loader = new DynamicLoader();

                loader.addScript('unifikmediaselectscript', '/bundles/unifikmedia/backend/js/media_select_scripts.js');

                loader.load();
            }
        });

        editor.addCommand( 'openmediamanager', new CKEDITOR.command( editor, {
            exec: function( editor ){

                if ('undefined' !== typeof unifikmediaselectscript){

                    mediaManagerLoadCk(editor);
                }
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


