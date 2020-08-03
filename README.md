Dynamic Server Image for OGP by MadMakz

About:
Creates updating images about current gameserver server status.
Similar to the imagesignatures known from GameTracker.com or Game-Monitor.com.

Features:
- Native OGP Support
- Image cacheing
- Supports Game/Mod based backgrounds
- 3 Sizes

Requirements:
- PHP-GD module
- Apache mod_rewrite

https://opengamepanel.org/forum/viewthread.php?thread_id=819


### Help for Ubuntu systems (For other systems you can find a similar way...).

1. Use the following command to enable mod_rewrite in Apache:

sudo a2enmod rewrite


--------------------
2. Edit the file /etc/apache2/sites-enabled/000-default,
or the file that correspond to your domain,
find the text block that describes the main directory properties,
by default is "/var/www" and you should change:

"AllowOverride None" to "AllowOverride All".



--------------------
3.Give Write permision to this folders:

modules/dsi/images
modules/dsi/cache


--------------------
4. Restart Apache using:

sudo /etc/init.d/apache2 restart
