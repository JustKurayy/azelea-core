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
 - Better error/exception handling
 - Better debug data / dd() function
 - Model/Entity manager
    1. CLI needs to create them 
    2. CLI needs to push them to db
    3. Migrations needs to be dynamically created
 - System to ensure translation of sites go super easy
    1. Android's app translation system can be used as example
    2. The translation folder should either be in src or in the root
    3. Text should be dynamically callable in the php file with help of Azelea\Core functions
    4. 
 - Asset loading
    1. Assets should be loaded from the asset folder in the root, not in /public
 - FTP & WebDav Support
 - Session management
 - Cookie manager
