var mediaManagerInit = true;
var mediaManagerSelectedMediaArray = [];
var mediaManagerSelectedMedia = {};
var mediaManagerTriggeringElement;
var mediaManagerScriptsBinded = false;
var mediaManagerIsCk = false;
var mediaManagerFolderId = 'base';
var mediaManagerFilters = {
    resetPage: true,
    page: 1,
    type: 'any',
    text: '',
    date: 'newer'
};
var mediaManagerAjaxLoader;
var mediaManagerEditValue;
var mediaManagerModal;
var mediaManagerIsLibrary = false;

// Create the media select container

$('body').append($('<div id="media_select_modal_container"><div id="media_select_modal" title="Medias"></div></div>'));
mediaManagerModal = $('#media_select_modal');
$('body').append($('<div id="media_notice_modal_container"><div id="media_notice_modal"></div></div>'));
var mediaManagerNoticeModal = $('#media_notice_modal');
$('body').append($('<div id="media_edit_modal_container"><div id="media_edit_modal"><input id="media_edit_value" type=text></div></div>'));
var mediaManagerEditModal = $('#media_edit_modal');
$('body').append($('<div id="media_delete_modal_container"><div id="media_delete_modal"></div></div>'));
var mediaManagerDeleteModal = $('#media_delete_modal');

var mediaManagerLoadLibrary = function(){

    mediaManagerModal = $('#mediaManager');

    mediaManagerIsLibrary = true;
    mediaManagerIsCk = true;
    mediaManagerLoad(mediaManagerInitialize);
    mediaManagerInit = false;
    mediaManagerAjaxLoader = $('#media_ajax_loader');

    mediaManagerAjaxLoader.hide();
};

$('.select_media').click(function(){
    mediaManagerTriggeringElement = $(this);

    if (mediaManagerInit || mediaManagerIsCk) {
        mediaManagerInit = true;
        mediaManagerIsCk = false;
        mediaManagerScriptsBinded = false;
        mediaManagerFilters.type = $(this).data('media-type');
        mediaManagerLoad(mediaManagerShow);
    } else {
        mediaManagerShow();
    }

    mediaManagerInit = false;
});

$('.media_button.remove').click(function(){

    var removeButton = $(this);

    removeButton.parent().parent().find('.input_media').val('');

    var img = removeButton.parent().parent().find('.image_media');
    img.attr('src', 'http://placehold.it/200x150&text=' + img.data('media-placeholder-trans'));

    removeButton.hide();
});

var mediaManagerInitialize = function(){
    mediaManagerSelectedMediaArray = [];
    mediaManagerSelectedMedia = {};

    if (mediaManagerFilters.resetPage) {
        mediaManagerFilters.page = 1;
    }

    mediaManagerFilters.resetPage = true;

    $('#media_details_inner').hide();
    $('#selection_count').hide();
    $('#welcome_message').show();

};

var mediaManagerLoadCk = function (editor) {

    mediaManagerTriggeringElement = editor;

    if (mediaManagerInit || !mediaManagerIsCk) {
        mediaManagerInit = true;
        mediaManagerScriptsBinded = false;
        mediaManagerIsCk = true;
        mediaManagerLoad(mediaManagerShow);
    } else {
        mediaManagerShow();
    }

    mediaManagerInit = false;
};

var mediaManagerLoad = function (callback) {

    mediaManagerSelectedMediaArray = [];

    $.ajax({
        url: Routing.generate('flexy_media_backend_load'),
        data: {
            folderId: mediaManagerFolderId,
            type: mediaManagerFilters.type,
            text: mediaManagerFilters.text,
            date: mediaManagerFilters.date,
            page: (mediaManagerFilters.resetPage) ? 1 : mediaManagerFilters.page,
            view: (mediaManagerIsCk) ? 'ckeditor' : 'mediafield',
            init: mediaManagerInit
        },
        async: (!mediaManagerInit),
        dataType: 'json',
        success: function (data) {

            // Append html content
            if (mediaManagerInit) {
                mediaManagerModal.html($(data.html));

                // Load tree nav
                mediaManagerNavigationLoad(data.tree);
                mediaManagerLoadBind();

            } else {

                var divMediaList = $('#media_list');

                if (divMediaList.hasClass('edit')) {
                    divMediaList.hide();
                    divMediaList.removeClass('edit');
                    divMediaList.html($(data.html));

                    divMediaList.show();
                    $('#media_details').show();
                } else {
                    divMediaList.html($(data.html));
                }

                mediaManagerAjaxLoader.hide();
                mediaManagerInitialize();
                mediaManagerLoadBind();
            }

        },
        error: function () {

            // Append html content
            mediaManagerModal.html($('<h2>Internal Server Error</h2>'));

        }
    });

    if (false == mediaManagerScriptsBinded) {
        mediaManagerBind();
        mediaManagerScriptsBinded = true;
    }

    if (undefined != callback) {
        callback();
    }
};

var mediaManagerNavigationLoad = function (tree) {

    $('#folder_tree').dynatree({
        onActivate: function(node) {
            mediaManagerAjaxLoader.show();
            mediaManagerFolderId = node.data.key;
            mediaManagerLoad();
        },
        generateIds: true,
        idPrefix: "dynatree-id-",
        dnd: {
            revert: true, // true: slide helper back to source if drop is rejected
            onDragStart: function(node) {
                /** This function MUST be defined to enable dragging for the tree.
                 *  Return false to cancel dragging of node.
                 */
                return (node.data.isFolder && node.data.key != 'base');
            },
            autoExpandMS: 500,
            preventVoidMoves: true, // Prevent dropping nodes 'before self', etc.
            onDragEnter: function(node, sourceNode) {
                return node.data.isFolder;
            },
            onDrop: function(node, sourceNode, hitMode, ui, draggable) {

                var type;
                var sourceIds = [];
                var targetId;

                if (sourceNode) {

                    sourceNode.move(node, 'over');
                    type = 'folder';
                    sourceIds = [sourceNode.data.key];
                    targetId = node.data.key;
                } else {

                    var media = ui.helper.find('.media');

                    media.each(function(){
                        var mediaId = $(this).data('media-id');

                        $('#media_item_' + mediaId).remove();
                        sourceIds.push(mediaId);
                    });

                    type = 'media';

                    targetId = node.data.key;
                }

                $.ajax({
                    url: Routing.generate('flexy_media_backend_move'),
                    data: {
                        type: type,
                        sourceIds: sourceIds,
                        targetId: targetId
                    }
                });
            }
        },
        children: [ // Pass an array of nodes.
            {key: 'base', title: 'Medias', activate:true, expand:true, isFolder: true,
                children: (tree == {}) ? false : tree
            }
        ],
        debugLevel: 0 // 0:quiet, 1:normal, 2:debug
    });
};

var mediaManagerEdit = function (submit) {

    var route;

    switch (mediaManagerSelectedMedia.type) {
        case 'image':
            route = 'flexy_media_backend_image_edit';
            break;
        case 'document':
            route = 'flexy_media_backend_document_edit';
            break;
        case 'video':
            route = 'flexy_media_backend_video_edit';
            break;
        case 'embedvideo':
            route = 'flexy_media_backend_embed_video_edit';
            break
    }

    mediaManagerAjaxLoader.show();

    var data = {mediaId: mediaManagerSelectedMedia.id};

    if (submit) {
        data = $('#edit_form').serialize() + '&mediaId=' + mediaManagerSelectedMedia.id;
    }

    $.ajax({
        method: (submit) ? 'POST' : 'GET',
        url: Routing.generate(route),
        data: data,
        dataType: 'json',
        success: function (data) {

            var divMediaList = $('#media_list');

            divMediaList.html($(data.html));
            divMediaList.show();

            mediaManagerAjaxLoader.hide();

            $('#edit_form').on('submit', function() {

                mediaManagerEdit(true);

                return false; // j'empêche le navigateur de soumettre lui-même le formulaire
            });

        },
        error: function () {

            // Append html content
            mediaManagerModal.html($('<h2>Internal Server Error</h2>'));

        }
    });
};

var mediaManagerShow = function () {

//    $(document).on('click', '.ui-widget-overlay', function(){ mediaManagerModal.dialog('close'); });

    mediaManagerInitialize();

    mediaManagerModal.dialog({
        modal: true,
        dialogClass: 'media_select',
        width: '90%',
        height: 630,
        minHeight: 0,
        minWidth: 400,
        position: {
            my: 'left top',
            at: 'left top',
            of: window
        },
        buttons: {
            Close: function() {
                $( this ).dialog( 'close' );
            }
        }
    });

    mediaManagerAjaxLoader = $('#media_ajax_loader');

    mediaManagerAjaxLoader.hide();

};

var mediaManagerBind = function () {

    // VIEW SELECTION SCRIPT

    $('#media_views').on('click', '.library', function(e){

        e.preventDefault();
        mediaManagerAjaxLoader.show();

        $('#uploader_wrapper').hide();
        $('#media_filters').show();
        $('#media_wrapper').show();

        mediaManagerLoad();

    });


    // UPLOADER BUTTON

    $('#media_views').on('click', '.upload', function(e){

        e.preventDefault();

        $('#media_wrapper').hide();
        $('#media_filters').hide();
        $('#uploader_wrapper').show();

    });

    // FILTERS BUTTONS

    $('#media_text_search').on('keyup', function(e){

        mediaManagerFilters.text = $(this).val();

        if (e.keyCode == 13) {
            mediaManagerAjaxLoader.show();
            mediaManagerLoad();
        }
    });

    if (mediaManagerIsCk) {
        $(function() {
            $( "#media_type_filters" ).buttonset();
        });

        $('.type_radio').on('click', function(e){

            mediaManagerFilters.type = $(this).val();

            mediaManagerAjaxLoader.show();
            mediaManagerLoad();

        });
    }

    $(function() {
        $( "#media_date_filters" ).buttonset();
    });

    $('.date_radio').on('click', function(e){

        mediaManagerFilters.date = $(this).val();

        mediaManagerAjaxLoader.show();

        mediaManagerFilters.resetPage = false;
        mediaManagerLoad();

    });

    // JQUERY UPLOADER

    // Initialize the jQuery File Upload widget:
    $('#fileupload').fileupload({
        // Uncomment the following to send cross-domain cookies:
        //xhrFields: {withCredentials: true},
        url: Routing.generate('flexy_media_backend_media_upload')
    });

    // EMBED VIDEO ADD SCRIPT

    var validEmbedMessage = $('#valid_embed_url');
    var invalidEmbedMessage = $('#invalid_embed_url');
    var embedAjaxLoader = $('#embed_ajax_loader');

    validEmbedMessage.hide();
    invalidEmbedMessage.hide();
    embedAjaxLoader.hide();


    $('#add_embed_link').on('click', function(e){

        $('#embed_ajax_loader').show();

        $.ajax({
            method: 'POST',
            url: Routing.generate('flexy_media_backend_embed_video_create'),
            data: {'video_url': $('#video_url').val() },
            dataType: "json",
            success: function(data){
                if (data.error != undefined) {
                    invalidEmbedMessage.fadeIn(500).delay(3000).fadeOut();
                } else {
                    validEmbedMessage.fadeIn(500).delay(3000).fadeOut();
                }
                $('#embed_ajax_loader').hide();
            }
        });

        e.preventDefault();
    });

    // MEDIA SELECTION SCRIPT

    $('#media_list').on('mousedown', '.media td', function(e){
        e.preventDefault();

        var isLeftClick;

        switch (event.which) {
            case 3:
                isLeftClick = false;
                break;
            default:
                isLeftClick = true;
        }

        if (false == isLeftClick && mediaManagerSelectedMediaArray.length < 2) {
            $(e.target).trigger('click');
        }
    });

    $('#media_list').on('click', '.media td', function(e){
        e.preventDefault();

        var div = $(this).parent();
        var divDetails = $('#media_details_inner');

        if (e.metaKey || e.metaKey) {
            if (div.hasClass('media_selected')) {
                div.removeClass('media_selected');

                var index = mediaManagerSelectedMediaArray.indexOf(div.data('media-id'));

                if (index > -1) {
                    mediaManagerSelectedMediaArray.splice(index, 1);
                }

            } else {
                div.addClass('media_selected');
                mediaManagerSelectedMediaArray.push(div.data('media-id'));
            }
        } else {
            $('.media_selected').removeClass('media_selected');
            div.addClass('media_selected');
            mediaManagerSelectedMediaArray = [div.data('media-id')];
        }

        if (mediaManagerSelectedMediaArray.length == 1) {
            mediaManagerSelectedMedia.id = div.data('media-id');
            mediaManagerSelectedMedia.name = div.data('media-name');
            mediaManagerSelectedMedia.type = div.data('media-type');
            mediaManagerSelectedMedia.preview = div.data('media-preview');
            mediaManagerSelectedMedia.thumbnail = div.data('media-thumbnail');
            mediaManagerSelectedMedia.url = div.data('media-url');
            mediaManagerSelectedMedia.caption = div.data('media-caption');

            if ('image' == mediaManagerSelectedMedia.type) {
                mediaManagerSelectedMedia.aviary = div.data('media-aviary');
            }

            divDetails.find('h3').html(mediaManagerSelectedMedia.name);
            divDetails.find('#aviary_image').attr('src', mediaManagerSelectedMedia.preview + '?' + new Date().getTime());

            divDetails.find('a#edit_media_link').on('click', function(e){
                e.preventDefault();

                var divMediaList = $('#media_list');

                divMediaList.hide();
                $('#media_details').hide();
                divMediaList.addClass('edit');

                mediaManagerEdit();
            });

            var divDetailsImage = $('#media_details_image');
            var divDetailsIframe = $('#media_details_iframe');

            switch (mediaManagerSelectedMedia.type) {
                case 'image':
                    $('#aviary_path').val(mediaManagerSelectedMedia.aviary);
                    divDetailsIframe.hide();
                    divDetailsImage.show();
                    break;
                case 'video':
                    divDetailsIframe.find('iframe').attr('src', mediaManagerSelectedMedia.url);
                    divDetailsImage.hide();
                    divDetailsIframe.show();
                    break;
                case 'embedvideo':
                    divDetailsIframe.find('iframe').attr('src', mediaManagerSelectedMedia.url);
                    divDetailsImage.hide();
                    divDetailsIframe.show();
                    break;
                default:
                    divDetailsIframe.hide();
                    divDetailsImage.show();
            }

            if ('' != mediaManagerSelectedMedia.caption) {
                var fileCaption = $('#file_caption');
                fileCaption.find('span').html(mediaManagerSelectedMedia.caption);
                fileCaption.show();
            } else {
                $('#file_caption').hide();
            }

            $('#welcome_message').hide();
            $('#selection_count').hide();
            divDetails.show();

        } else {
            divDetails.hide();

            var welcomeDiv = $('#welcome_message');
            var selectionCountDiv = $('#selection_count');

            if (mediaManagerSelectedMediaArray.length) {
                welcomeDiv.hide();
                selectionCountDiv.find('span').html(mediaManagerSelectedMediaArray.length);
                selectionCountDiv.show();
            } else {
                selectionCountDiv.hide();
                welcomeDiv.show();
            }
        }

    });

    if (false == mediaManagerIsLibrary) {

        $('#insert_media').click(function(e){
            e.preventDefault();
            mediaManagerInsert();
        });

        $('#media_list').on('dblclick' , '.media', function(e) {
            e.preventDefault();
            mediaManagerInsert();
        });
    } else {

        $('#insert_media').hide();

    }

    // AVIARY

    $('#media_details').on('click' , '#edit-aviary', function(e) {

        launchEditor('aviary_image');

    });

    // CONTEXT MENU

    $.contextMenu( {selector: '.dynatree-node',
        build: function($trigger, e){
            return {
                items: {
                    create: {name: 'Create', icon: 'add', callback: function(){

                        var node = $('#' + $trigger.parent().attr('id')).prop('dtnode');
                        if( node ){

                            $.ajax({
                                url: Routing.generate('flexy_media_backend_createFolder'),
                                data: {
                                    parentFolderId: node.data.key
                                },
                                async: false,
                                dataType: 'json',
                                success: function (data) {

                                    if (undefined != data.key && data.key != null) {
                                        node.addChild({
                                            key: data.key,
                                            title: "New Folder",
                                            isFolder: true
                                        });
                                    }
                                }
                            });
                        }
                    }},
                    edit: {name: 'Edit', icon: 'edit', callback: function(){

                        var node = $('#' + $trigger.parent().attr('id')).prop('dtnode');
                        if( node ){

                            if ('base' != node.data.key) {

                                mediaManagerEditModalShow(node.data.title, function(){

                                    $.ajax({
                                        url: Routing.generate('flexy_media_backend_renameFolder'),
                                        data: {
                                            folderId: node.data.key,
                                            folderTitle: mediaManagerEditValue
                                        },
                                        async: false,
                                        dataType: 'json',
                                        success: function (data) {

                                            if (undefined != data.renamed) {

                                                if (data.renamed) {

                                                    node.data.title = mediaManagerEditValue;
                                                    node.render();

                                                } else {
                                                    mediaManagerNoticeModalShow(data.message);
                                                }
                                            }
                                        }
                                    });
                                });
                            }
                        }
                    }},
                    separator1: '---------',
                    delete: {name: 'Delete', icon: 'delete', callback: function(){

                        var node = $('#' + $trigger.parent().attr('id')).prop('dtnode');
                        if( node ){

                            $.ajax({
                                url: Routing.generate('flexy_media_backend_deleteFolder'),
                                data: {
                                    folderId: node.data.key
                                },
                                async: false,
                                dataType: 'json',
                                success: function (data) {

                                    if (undefined != data.removed) {

                                        if (data.removed) {

                                            node.remove();

                                        } else {
                                            mediaManagerNoticeModalShow(data.message);
                                        }
                                    }
                                }
                            });
                        }
                    }}
                }
            };
        }
    });
}

var mediaManagerLoadBind = function(){
    // DRAGGABLE

    $('.media').draggable({
        connectToDynatree: true,
        cursorAt: { top: -5, left:-5 },
        helper: function(){
            var selected = $('.media.media_selected');
            if (selected.length < 2) {
                selected = $(this);
            }

            var container = $('<table/>').attr('id', 'draggingContainer').addClass('media');
            container.append(selected.clone());
            return container;
        }
    });

    // CONTEXT MENU

    $.contextMenu( {selector: '.media',
        build: function($trigger, e){
            return {
                items: {
                    insert: (mediaManagerIsLibrary) ? {disabled: true} : {
                        name: 'Insert',
                        icon: 'add',
                        disabled: (mediaManagerSelectedMediaArray.length > 1),
                        callback: function(){
                            mediaManagerInsert();
                        }
                    },
                    selectall: {
                        name: 'Select All',
                        callback: function(){

                            mediaManagerSelectedMediaArray = [];

                            var medias = $trigger.parent().find('.media');

                            medias.each(function(){
                                var media = $(this);
                                media.addClass('media_selected');
                                mediaManagerSelectedMediaArray.push(media.data('media-id'));


                                var selectionCountDiv = $('#selection_count');
                                selectionCountDiv.find('span').html(mediaManagerSelectedMediaArray.length);

                                $('#media_details_inner').hide();
                                $('#welcome_message').hide();
                                selectionCountDiv.show();

                            });
                        }
                    },
                    separator1: '---------',
                    edit: {
                        name: 'Edit',
                        icon: 'edit',
                        disabled: (mediaManagerSelectedMediaArray.length > 1),
                        callback: function(){

                            var divMediaList = $('#media_list');

                            divMediaList.hide();
                            $('#media_details').hide();
                            divMediaList.addClass('edit');

                            mediaManagerEdit();
                        }
                    },
                    editimage: (mediaManagerSelectedMedia.type != 'image') ? false : {
                        name: 'Edit Image',
                        icon: 'edit',
                        disabled: (mediaManagerSelectedMediaArray.length > 1),
                        callback: function(){
                            launchEditor('aviary_image');
                        }
                    },
                    duplicate: {
                        name: 'Duplicate',
                        icon: 'copy',
                        disabled: (mediaManagerSelectedMediaArray.length > 1),
                        callback: function(){

                            mediaManagerAjaxLoader.show();

                            var mediaIds;

                            mediaIds = mediaManagerSelectedMediaArray;

                            $.ajax({
                                url: Routing.generate('flexy_media_backend_duplicate'),
                                data: {
                                    mediaIds: mediaIds
                                },
                                dataType: 'json',
                                success: function (data) {
                                    mediaManagerFilters.resetPage = false;
                                    mediaManagerLoad();
                                }
                            });
                        }
                    },
                    separator2: '---------',
                    delete: {name: 'Delete', icon: 'delete', callback: function(){

                        mediaManagerAjaxLoader.show();

                        var mediaIds;

                        mediaIds = mediaManagerSelectedMediaArray;


                        $.ajax({
                            url: Routing.generate('flexy_media_backend_deleteMedia'),
                            data: {
                                mediaIds: mediaIds
                            },
                            dataType: 'json',
                            success: function (data) {

                                mediaManagerAjaxLoader.hide();

                                if (undefined != data.message) {

                                    mediaManagerDeleteModalShow(data.message, function(){

                                        mediaManagerAjaxLoader.show();

                                        $.ajax({
                                            url: Routing.generate('flexy_media_backend_deleteMedia'),
                                            data: {
                                                mediaIds: mediaIds,
                                                delete: true
                                            },
                                            dataType: 'json',
                                            success: function (data) {

                                                for (i = 0; i < mediaIds.length; i++) {
                                                    $('#media_item_' + mediaIds[i]).remove();
                                                }

                                                mediaManagerInitialize();

                                                mediaManagerAjaxLoader.hide();

                                            }
                                        });
                                    });
                                }
                            }
                        });
                    }}
                }
            };
        }
    });
};

var mediaManagerInsert = function() {

    if (mediaManagerIsCk){
        mediaManagerInsertCk();
    } else {
        mediaManagerTriggeringElement.parent().find('.input_media').val(mediaManagerSelectedMedia.id);
        mediaManagerTriggeringElement.parent().find('.remove').show();
        mediaManagerTriggeringElement.attr('src', mediaManagerSelectedMedia.thumbnail);
    }

    mediaManagerModal.dialog('close');
};

var mediaManagerInsertCk = function() {
    CKEDITOR.plugins.get('flexymediamanager').insertMedia(mediaManagerTriggeringElement, mediaManagerSelectedMedia);

    mediaManagerModal.dialog('close');

    mediaManagerIsCk = false;
};

// AVIARY

var featherEditor = new Aviary.Feather({
    apiKey: 'i3kui99ayvje8cix',
    apiVersion: 2,
    maxSize: 800, // Output image size (default 800x800 px)
    displayImageSize: true,
    tools: 'draw,text,enhance,frames,effects,stickers,crop,resize,warmth,orientation,brightness,focus,warmth,contrast,saturation,sharpness,splash,whiten,redeye,blemish',
    appendTo: '',
    onSave: function(imageID, newURL) {
        $('#aviary_ajax_loader').show();
        var img = $('#'+imageID);
        $.get($('#aviary_path').val(), {
            image: newURL
        }, function(data){
            img.fadeOut();
            img.attr('src', img.attr('src') + '?' + new Date().getTime());
            $('#aviary_ajax_loader').hide();
            img.fadeIn();
        });
    },
    onError: function(errorObj) {
        alert(errorObj.message);
    },
    onClose: function() {
        var img = $('#aviary_image');
        img.removeAttr('class');
        img.removeAttr('sytle');
    }
});

function launchEditor(id) {

    $('#aviary_image').addClass('aviary');

    featherEditor.launch({
        image: id
    });
}

var mediaManagerNoticeModalShow = function (message) {

    mediaManagerNoticeModal.html(message);

    mediaManagerNoticeModal.dialog({
        modal: true,
        dialogClass: 'media_notice',
        buttons: {
            Ok: function() {
                $( this ).dialog( 'close' );
            }
        }
    });
};

var mediaManagerEditModalShow = function (value, callback) {

    var input = $('#media_edit_value');
    input.val(value);

    mediaManagerEditModal.dialog({
        modal: true,
        dialogClass: 'media_edit',
        buttons: {
            Save: function() {

                mediaManagerEditValue = input.val();
                input.val('');
                $( this ).dialog( 'close' );

                callback();
            },
            Cancel: function() {
                $( this ).dialog( 'close' );
            }
        }
    });
};

var mediaManagerDeleteModalShow = function (message, callback) {

    mediaManagerDeleteModal.html(message);

    mediaManagerDeleteModal.dialog({
        modal: true,
        width: 'auto',
        maxWidth: 800,
        maxHeight: 500,
        dialogClass: 'media_delete',
        buttons: {
            Delete: function() {

                $( this ).dialog( 'close' );

                callback();
            },
            Cancel: function() {
                $( this ).dialog( 'close' );
            }
        }
    });
};