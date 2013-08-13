CKEDITOR.plugins.add('egzaktmediamanager', {
    init: function( editor ) {
        editor.addCommand( 'openmediamanager', new CKEDITOR.command( editor, {
            exec: function( editor ){
                if ('undefined' === typeof egzaktMediaScript){
                    var egzaktMediaScript = document.createElement('script');
                    egzaktMediaScript.src = '/bundles/egzaktmedia/backend/js/media_select.js';
                    egzaktMediaScript.type = 'text/javascript';

                    //Wait until the script is fully loaded before calling the media manager
                    egzaktMediaScript.onload = function(){
                        $.mediaManager();
                        $.mediaManager.loadCk(editor);
                    };
                    //Our great friend IE doesn't support onload event, a special event is needed just for him
                    egzaktMediaScript.onreadystatechange = function(){
                        if('loaded' == this.readyState) {
                            $.mediaManager();
                            $.mediaManager.loadCk(editor);
                        }
                    };

                    document.getElementsByTagName('body')[0].appendChild(egzaktMediaScript);

                    var egzaktMediaStyle = document.createElement('link')
                    egzaktMediaStyle.href = '/bundles/egzaktmedia/backend/css/media.css';
                    egzaktMediaStyle.rel = 'stylesheet';

                    var twigjs = document.createElement('script');
                    twigjs.src = '/bundles/egzaktsystem/backend/js/twig.js';
                    twigjs.type = 'text/javascript';

                    var body = document.getElementsByTagName('body');
                    body.appendChild(twigjs);
                    body.appendChild(egzaktMediaScript);
                    document.getElementByTagName('head')[0].appendChild(egzaktMediaStyle);

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


