UnifikMediaBundle
=================

Installation
----------------
1. Install it via composer, load it in the kernel and load the routing
2. Add '@UnifikMediaBundle/Resources/public/backend/js/dynamic_loader.js' in the javascript in the system bundle, since the media manager popup can be required the Dynamic Loader and it can be call everywhere in the backend

Utilisation
----------------

### CKEditor
To activate the media manager with CKEditor once the bundle is loaded, you must register the plugin in the ckeditor config:

    external_plugins:
        unifikmediamanager:
        path: bundles/unifikmedia/backend/js/ckeditor/plugin/unifikmediamanager

You must also add the `'Insert_media'` button in one toolbar. 
An example of a complete config is: 

    trsteel_ckeditor:
      language: %locale%
      startup_outline_blocks: false
      entities: false
      external_plugins:
          unifikmediamanager:
              path: bundles/unifikmedia/backend/js/ckeditor/plugin/unifikmediamanager
      transformers: ['strip_js', 'strip_css', 'strip_comments']
      toolbar: ['document', 'clipboard', 'editing', 'basicstyles', 'paragraph', 'links', 'insert', 'styles', 'tools']
      toolbar_groups:
          document: ['Source', '-', 'Save', '-', 'Templates']
          clipboard: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo']
          editing: ['Find', 'Replace', '-', 'SelectAll']
          basicstyles: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']
          paragraph: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']
          links: ['Link', 'Unlink', 'Anchor']
          insert: ['Image', 'Insert_media', 'Flash', 'Table', 'HorizontalRule']
          styles: ['Styles', 'Format']
          tools: ['Maximize', 'ShowBlocks']
      width: 800
      height: 300

### Stof Doctrine Extensions
To get the uploader work, you need to activate the uploadable functionality in `app/config/config.yml`:

    # Stof doctrine extension
	stof_doctrine_extensions:
    	orm:
        	default:
            	uploadable: true
            	
### Liip Imagine Bundle
Here the minimum Liip Imagine config to have the media manager work:

In `app/config/config.yml`:

	liip_imagine:
    	filter_sets:
       	 	media_thumb:
            	quality: 75
            	filters:
                	thumbnail: { size: [120, 120], mode: outbound }
        	media_thumb_large:
            	quality: 75
            	filters:
                	thumbnail: { size: [250, 250], mode: outbound }
        	media_thumb_editor:
            	quality: 75
            	filters:
                	relative_resize: { heighten: 500 }
                	
In `app/config/routing.yml`:

	_imagine:
    	resource: .
    	type:     imagine

### Fos Js Routing

This bundle need [FosJsRoutingBundle](https://github.com/FriendsOfSymfony/FOSJsRoutingBundle) to work proprely. See the bundle doc to know how implement it.

### Entity relation
It is possible to add a many-to-one relationsheep be some entity an a Media. By example, we can add a thumbanil to a news entity:

    manyToOne:
      thumbnail:
        targetEntity: Unifik\MediaBundle\Entity\Media
        
Then, the only thing to do is to specify the type of the field in the form, and pass the methode used to access the entity:

    $builder ->add('thumbnail', 'media_select', array('media_method' => 'thumbnail'));
    
It is also possible to specify which type of media can be selected, using `'types' => array('image'))` option. For now, the possible values are 'image', 'document', and 'media'. 
The [news bundle](https://github.com/yanickouellet/UnifikNewsBundle/tree/media-manager) provides a complete example.
