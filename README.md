EgzaktMediaBundle
=================

Installation
----------------
1. Install it via composer, load it in the kernel and load the routing
2. Add '@EgzaktMediaBundle/Resources/public/backend/js/dynamic_loader.js' in the javascript in the system bundle, since the media manager popup can be required the Dynamic Loader and it can be call everywhere in the backend

Utilisation
----------------

### CKEditor
To activate the media manager with CKEditor once the bundle is loaded, you must register the plugin in the ckeditor config:

    external_plugins:
        egzaktmediamanager:
        path: bundles/egzaktmedia/backend/js/ckeditor/plugin/egzaktmediamanager

You must also add the `'Insert_media'` button in one toolbar. 
An example of a complete config is: 

    trsteel_ckeditor:
      language: %locale%
      startup_outline_blocks: false
      entities: false
      external_plugins:
          egzaktmediamanager:
              path: bundles/egzaktmedia/backend/js/ckeditor/plugin/egzaktmediamanager
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

### Entity relation
It is possible to add a many-to-one relationsheep be some entity an a Media. By example, we can add a thumbanil to a news entity:

    manyToOne:
      thumbnail:
        targetEntity: Egzakt\MediaBundle\Entity\Media
        
Then, the only thing to do is to specify the type of the field in the form, and pass the methode used to access the entity:

    $builder ->add('thumbnail', 'media_select', array('media_method' => 'thumbnail'));
    
It is also possible to specify which type of media can be selected, using `'types' => array('image'))` option. For now, the possible values are 'image', 'document', and 'media'. 
The [news bundle](https://github.com/yanickouellet/EgzaktNewsBundle/tree/media-manager) provides a complete example.
