Wordpress Tabs API plugin
===========

This plugin uses the tabs api client to give a wordpress site access to the tabs api.

Note: This is purely for demonstration purposes

## Installation
1. Clone/download the repo into your `/wp-content/plugins` directory.
2. If you have cloned the repo, run git submodule init; git submodule update;` to download the external libraries. If not, you will need to create a libraries folder inside the `/wp-content/plugins/wp-tabs-api` folder and manually copy the repos https://github.com/alexwyett/aw-form-fields and https://github.com/CarltonSoftware/tabs-api-client (note, this client is a commercial product).  The folder structure should look like this now:
```
  wp-content ->
    wp-tabs-api ->
      libraries ->
        aw-form-fields
          src
          ..
          ..
          ..
        tabs-api-client
          src
          ..
          ..
          ..
```
3. Next you'll need to prime the custom content cache.  Login to wordpress and install the plugin.  After installation, navigate to the admin menu.
