UnifikMediaBundle
=================

Installation
----------------

Using **composer** file :

    "require": {
        //...,
        "unifik/media-bundle": "dev-master"
    },

Add the following line to **AppKernel.php** :

    new Unifik\MediaBundle\UnifikMediaBundle(),

In **app/config/routing.yml** add the following:

    unifik_media_backend:
        resource: "@UnifikMediaBundle/Resources/config/routing_backend.yml"
        prefix:   /admin/media

To activate the media manager with CKEditor once the bundle is loaded, you must register the plugin in the ckeditor config (**app/config/config.yml**):

    external_plugins:
        unifikmediamanager:
            path: bundles/unifikmedia/backend/js/ckeditor/plugin/unifikmediamanager

You must also add the `'Insert_media'` button in one toolbar. 
An example of config: 

    trsteel_ckeditor:
      toolbar_groups:
          [...]
          insert: ['Insert_media', 'Image', 'Flash', 'Table', 'HorizontalRule']
          [...]

To get the proper media select field in your form, your need to add those lines in your form theme:

    {% block media_select_widget %}
        {% include 'UnifikMediaBundle:Backend/Form:fields.html.twig' with {'widget_attributes': block('widget_attributes')} %}
    {% endblock %}

This bundle use [FOSRoutingBundle](https://github.com/FriendsOfSymfony/FOSJsRoutingBundle).
So you need to include the following js files:
    
    {% javascripts
        'bundles/fosjsrouting/js/router.js'
        'js/fos_js_routes.js'
    %}
    <script src="{{ asset_url }}"></script>
    {% endjavascripts %}
    
And run this command: `app/console fos:js-routing:dump`
    
Bundle requirements
----------------

* unifik/doctrine-behaviors-bundle
* liip/imagine-bundle
* friendsofsymfony/jsrouting-bundle

Add Media field
----------------
To link media with entity, add the manyToOne relation as follow:

    manyToOne:
      myMedia:
        targetEntity: Unifik\MediaBundle\Entity\Media

To generate de media field, add this in your form type:

    ->add('image2', 'media_select')
    
You can also force a media type:
(available types are: image, video, embedvideo and document)

    ->add('image2', 'media_select', array('type' => 'image))
