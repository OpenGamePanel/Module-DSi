Help for Ubuntu systems (For other systems you can find a similar way...).

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