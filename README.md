# Azelea Core

### What is this repo?
This repo is for the core of the lightweight PHP framework named Azelea.

### How do I run this?
This repo is for the composer package, which you can install 
with ```composer require azelea/core```.
You also require a template for the framework. One can be found
on my GitHub Page.

### How do I contribute to this?
Currently I have no idea if others can contribute to this repo. If you can, any help is appreciated. You can also contribute to the azelea-test repo in my profile.

### ToDo List
Stuff that needs to be implemented:
 - Extend dd() function with better information parsing
 - Model/Entity manager
    1. CLI needs to create them 
    2. CLI needs to push them to db
    3. Migrations needs to be dynamically created
    4. User creation
    5. User login
 - FTP & WebDav Support
 - Built-In AJAX
 - Session management
    1. User ID 
 - Cookie manager
 - Templater
    1. Create @extends('base.loom.php') function
    2. Create @body and @endbody tags
    3. include base and extend @body tags in template files
