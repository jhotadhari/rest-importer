=== REST Importer ===
Tags: REST,import,remote,json
Donate link: http://waterproof-webdesign.info/donate
Contributors: jhotadhari
Tested up to: 4.7.3
Requires at least: 4.7
Stable tag: trunk
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Get remote data and save it as posts or users. Customize the way the data gets stored.


== Description ==

Access remote data via REST and save it as WordPress Posts or Users.

UI to customize the import. Shape any kind of json response to fit your desired structure and field names.

Completly free. Fork it on github (https://github.com/jhotadhari/rest-importer).


## How to use:
* Make a backup of your database! In case you import to much trash to wrong places and assign the values to wrong fields. 
* Install REST Importer and go to "Tools" -> "REST Importer" -> "Sources", add a source and save. 
* Switch to tab "Requests & Import", add a request, choose the source, select "Print as admin notice" and push the "Request/Import" button. On success, you'll see the response as some kind of tree structure. 
* Switch to tab "Value Mapping" add a "Map" and customize the way the data gets imported. Rebuild the the response structure and assign the nodes to post/user options and meta-options.
* Switch to tab "Requests & Import", edit the request and select "Save response".


## Currently supported authorisation:
* none
* OAuth 1.0a


## Coming soon:
* Import as cron job 
* More filter and hooks 
* Wiki and documentation 
* More authorisation types 


## Good to know:
* Sensitive data (passwords, keys, secrets) will be stored encrypted in the database.


## Thanks for beautiful ressoucres:
* CMB2 (https://github.com/WebDevStudios/CMB2)
* Integration CMB2-qTranslate (https://github.com/jmarceli/integration-cmb2-qtranslate)
* CMB2 Conditionals (https://github.com/jcchavezs/cmb2-conditionals)
* OAuth 1 PHP Library (https://github.com/EHER/OAuth)
* jstree (https://github.com/vakata/jstree/)
* jsTreeGrid (https://github.com/deitch/jstree-grid)


## Requirements:
* php 5.6
* cURL


== Installation ==
Requirements:
* php 5.6

Upload and install this Plugin the same way you\'d install any other plugin. 
Go to "Tools" -> "REST Importer".

== Screenshots ==
1. http://waterproof-webdesign.info/wp-content/uploads/2017/03/sources.png
2. http://waterproof-webdesign.info/wp-content/uploads/2017/03/mapping.png
3. http://waterproof-webdesign.info/wp-content/uploads/2017/03/import.png

== Changelog ==

= 0.0.1 =
yeeaaa first version, hurray

