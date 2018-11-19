FilesFinderBundle
======================

Information
-----------

Bundle to find files by content on Linux and Windows. 

###Interfaces
* REST API. Use NelmioApiDocBundle to read API documentation for bundle.
* Console command. Use `php app/console files_finder:search 'path to folder' 'text to search'` in command line.
* UI. Find files using web interface on `/search` route.

Enable the Bundle
-------------------------

Enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

    <?php
        // app/AppKernel.php
        
        // ...
        class AppKernel extends Kernel
        {
            public function registerBundles()
            {
                $bundles = array(
                    // ...
                    new JMS\SerializerBundle\JMSSerializerBundle(),
                    new FOS\RestBundle\FOSRestBundle(),
                    new Nelmio\ApiDocBundle\NelmioApiDocBundle(),
                    new Massmedia\FilesFinderBundle\FilesFinderBundle(),
                    new Bazinga\Bundle\RestExtraBundle\BazingaRestExtraBundle(),
                );
        
                // ...
            }
        
            // ...
        }
        
Configure
-----------------

###Routes file:

    # app/config/routing.yml
    NelmioApiDocBundle:
      resource: "@NelmioApiDocBundle/Resources/config/routing.yml"
      prefix:   /api/doc
      
###Configure rest:

    # app/config/config.yml
    fos_rest:
        param_fetcher_listener: true
        body_listener: true
        view:
            view_response_listener: 'force'
        format_listener:
            rules:
                - { path: ^/api, priorities: [ json ], fallback_format: json, prefer_extension: true }
                - { path: '^/', priorities: [ 'html', '*/*'], fallback_format: ~, prefer_extension: true }
                
Tests
-----
Run `phpunit` inside project `app` directory.