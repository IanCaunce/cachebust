#Cachebust

This package adds a unique hash to the URI of an asset which updates each time the file changes. It will work with any static asset such as images, stylesheets and javascript. This is helpful for situations when the webserver sets expiry headers on files but updates needs to be pushed immediately to the user.

There are three methods of cachebusting an asset using this package. They are:

###File (default)
The hash is added to the filename of the asset. For example:
<pre>/js/<b>a4bb8768</b>.application.js</pre>

###Path
The hash is added to the path of the asset. For example:
<pre>/js/<b>a4bb8768</b>/application.js</pre>

###Query
The hash is added as a query parameter of the asset's URI. For example: 
<pre>/js/application.js?c=<b>a4bb8768</b></pre>

##Installation

The cachebust package can be install via [Composer](https://getcomposer.org/) by running:

```
    composer require iancaunce/cachebust 1.*
```

Or requiring the `iancaunce/cachebust` package in your project's `composer.json`.

```
{
    "require": {
        "iancaunce/cachebust": "1.*"
    }
}
```

And then running:

```
composer update iancaunce/cachebust
```

##Configuration
To configure the cachebust class, pass through an array of options when instantiating it.

###enabled
**Type**: `Boolean`  
**Default**: `true`  

Enables or disables cachebusting of assets.

###bustMethod
**Type**: `string`  
**Default**: `file`  
**Allowed Values**: `file`, `path`, `query`  

This dictates the busting method used. See [Configuration Notes](#configuration-notes).

###useFileContents  
**Type**: `Boolean`  
**Default**: `false`  

If set to true, the asset's contents will be used to generate the unique hash. This could have a memory impact for large assets as it will be held in memory whilst generating the hash.

###algorithm  
**Type**: `string`  
**Default**: `crc32`  
**Allowed Values**: See [PHP.net](http://php.net/manual/en/function.hash-algos.php) for supported hashing algorithms.  

Sets the hashing algorithm to be used.

###seed
**Type**: `string`  
**Default**: `a4bb8768`  

This can be any string you like. Its purpose is to allow you to invalidate the cache of an asset by changing the hash without updating the file. Simply change this string, and a new hash will be generated.

###publicDir
**Type**: `string`  
**Default**: ''  

Sets the path to the public directory of the assets.

###prefix
**Type**: `string`  
**Default**: ''  

This string prefixes the hash for `file` and `path` busting methods. You only need to set this if you serve non busted assets which also have a hash as part of the filename or path.

###queryParam
**Type**: `string`  
**Default**: 'c'  

The query parameter used when using the query busting method.

##Configuration Notes

For `file` and `path` busting, you will need to add a rewrite rule to your `.htaccess` file if you are running `Apache` or a location block if you are running `Nginx`.

###Apache

####File
```
<IfModule mod_rewrite.c>
    RewriteRule ^(.*\/)[0-9a-f]{8}\.(.*)$ $1$2 [DPI]
</IfModule>
```

####Path
```
<IfModule mod_rewrite.c>
    RewriteRule ^(.*\/)[0-9a-f]{8}\/(.*)$ $1$2 [DPI]
</IfModule>
```

####Nginx
Make sure is it the first location block in your configuration file.

####File
```
location ~* "^(.*\/)[0-9a-f]{8}\.(.*)$" {
    try_files $uri $1$2;
}
```

####Path
```
location ~* "^(.*\/)[0-9a-f]{8}\/(.*)$" {
    try_files $uri $1$2;
}
```

The default hashing alogorithm used is `crc32` which has a hash length of 8 characters. If you would like to used a different hashing algorithm, you can do so by changing it in the configuration. Update the number in curly brackets to match the length of your chosen algorithm.

For example, if I wanted to use `md5`, my regex would become something like:
<pre>
^(.*\/)[0-9a-f]{<b>32</b>}\/(.*)$
</pre>

If you use a prefix, you will need to add this to the regular expression followed by a dash. For example:

####File
<pre>
^(.*\/)<b>prefix-</b>[0-9a-f]{8}\.(.*)$
</pre>

####Path
<pre>
^(.*\/)<b>prefix-</b>[0-9a-f]{8}\/(.*)$
</pre>

Alternatively, you can use the cachebust class to generate the regex for you using your current configuration. For Example:

```
    $cachebust = new IanCaunce\Cachebust\Cachebust([
        'bustMethod' => 'path',
        'algorithm' => 'md5',
        'prefix' => 'cache'
    ]);

    print $cachebust->genRegex();
```

##Usage

```
    //Add the namespace to the top of the file
    use IanCaunce\Cachebust\Cachebust;
    
    //Set your options
    $options = [
       // ...
    ];
    
    //Instantiate an instance of the cachebust class
    $cachebust = new Cachebust($options);
    
    //Bust your asset.
    <link rel="stylesheet" href="<?=$cachebust->asset('/js/application.min.js')?>">

```

Some assets may exist locations other than your main public directory. You can pass the public directory of individual assets to the `asset` function as the second parameter.

```
<link rel="stylesheet" href="<?=$cachebust->asset('/js/application.min.js', 'some/other/public/directory')?>">
```