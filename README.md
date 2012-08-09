
README for ColladaFragmenter
-------

ColladaFragmenter by Owen Mundy owenmundy.com


License
-------
This code is in the public domain


Description
-------
Parse a 3D COLLADA (.dae) file and export a set number of geometries as new COLLADA (.dae) files.


Requirements
-------
PHP 5.3+ recommended, SimpleXML support required

It is imperative that you run this application from the command line if possible. 
The difference in speed is dramatic. For example...

    $ php file.php


Usage
-------

    $ColladaFragmenter = new ColladaFragmenter();
    $ColladaFragmenter->fragment($importfile, 44, 53, true, false, $exportdir);