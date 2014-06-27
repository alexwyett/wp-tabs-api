Wordpress Tabs API plugin
===========

This plugin uses the tabs api client to give a wordpress site access to the tabs api.

# Note: This is purely for demonstration purposes

## Installation
1: Clone/download the repo into your `/wp-content/plugins` directory.

2: If you have cloned the repo, run `git submodule init; git submodule update;` to download the external libraries. If not, you will need to create a libraries folder inside the `/wp-content/plugins/wp-tabs-api` folder and manually copy the repos https://github.com/alexwyett/aw-form-fields and https://github.com/CarltonSoftware/tabs-api-client (note, this client is a commercial product).  The folder structure should look like this now:

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

3: Next you'll need to prime the custom content cache.  Login to wordpress and install the plugin.  After installation, navigate to the admin menu and locate the plugin's [settings screen](https://github.com/alexwyett/wp-tabs-api/blob/master/assets/admin-1.jpg).

4: Add in your api connection settings and save.

5: After a screen refresh, you'll see a Property Sync menu option.  Navigate to this and click on the Update Property Index. This will create all of the custom cottage content types which you can theme.  There are example templates within the plugin which you can look at to get started.

6: The [Cottage Theme Repo](https://github.com/alexwyett/cottage-theme) contains a full implementation of the plugin which can be used a reference.
