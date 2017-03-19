

== Description ==

Access remote data via REST and save it as WordPress Posts or Users.

UI to customize the import. Shape any kind of json response to fit your desired structure and field names.

> REST Importer on [GitHub](https://github.com/jhotadhari/rest-importer)

> REST Importer [wiki](https://github.com/jhotadhari/rest-importer/wiki)

= How to use =

* Make a backup of your database! In case you import to much trash to wrong places and assign the values to wrong fields. 
* Install REST Importer and go to "Tools" -> "REST Importer" -> "Sources", add a source and save. 
* Switch to tab "Requests & Import", add a request, choose the source, select "Print as admin notice" and push the "Request/Import" button. On success, you'll see the response as some kind of tree structure. 
* Switch to tab "Value Mapping" add a "Map" and customize the way the data gets imported. Rebuild the the response structure and assign the nodes to post/user options and meta-options.
* Switch to tab "Requests & Import", edit the request and select "Save response".


= Currently supported authorisation =
* none
* OAuth 1.0a


= Coming soon =
* More filter and hooks 
* Wiki and documentation 
* More authorisation types 


= Thanks for beautiful ressoucres =
* [CMB2](https://github.com/WebDevStudios/CMB2)
* [Integration CMB2-qTranslate](https://github.com/jmarceli/integration-cmb2-qtranslate)
* [CMB2 Conditionals](https://github.com/jcchavezs/cmb2-conditionals)
* [OAuth 1 PHP Library](https://github.com/EHER/OAuth)
* [jstree](https://github.com/vakata/jstree/)
* [jsTreeGrid](https://github.com/deitch/jstree-grid)
* This Plugin is based on the [generator-pluginboilerplate](https://github.com/jhotadhari/generator-pluginboilerplate)


Good to know:

* Sensitive data (passwords, keys, secrets) will be stored encrypted in the database.

== Installation ==
### Requirements:
* php 5.6
* cURL
* JavaScript needs to be enabled for the settings page (if all settings are done, you may disable js again)

Upload and install this Plugin the same way you'd install any other plugin. 
Go to "Tools" -> "REST Importer".

== Screenshots ==
1. Sources
2. How to store the Response in the database
3. Request and Import

== Upgrade Notice ==

This Plugin is still in early development. Reality might be in movement.