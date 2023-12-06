# PHP Photo Gallery with pagination and mobile responsive ready.

Based on https://github.com/beamtic/php-photo-gallery

Simplified with only the basic code needed to display the gallery in your website.

## Screenshots

### Main Gallery

![Main Gallery](https://raw.githubusercontent.com/saudiqbal/PHP-photo-gallery-with-pagination/main/php-photo-gallery.jpg)

### Categories

![Main Gallery](https://raw.githubusercontent.com/saudiqbal/PHP-photo-gallery-with-pagination/main/php-photo-gallery-categories.jpg)

### Image display

![Image display](https://raw.githubusercontent.com/saudiqbal/PHP-photo-gallery-with-pagination/main/php-photo-gallery-view.jpg)

**PHP Photo Gallery with pagination** takes advantage of PHP's build-in capabilities and file-handling to create a modern image gallery, allowing us to create categories, and organize them into categories.

## Requirements
The gallery requires GD library to, among other things, create thumbnails from uploaded images. Some hosts will already have this installed by default. If your host does not have it installed, it is recommend you either shift to cloud hosting, or find a shared host that does have it installed.

Some hosts already handle permissions by default, others will not. In the latter case, you will have to change permissions on your own (I.e.: chown & chmod). If this is not possible, change to another hosting provider!

You might also need to change the **upload_max_filesize** and **post_max_size** in php.ini, but again, sometimes your hosting will already allow you to upload larger files. Obviously, it is bad to upload big image files because they generally take too long time to load, and they will slow down the gallery for your visitors. It is recommended you resize images before uploading.

## Installation
Installing the photo gallery is simple. You can either download a **.7z** file from the project website (phpphotogallery.com), or you can clone this git repository.

Create two folders inside the installation directory named
gallery
thumbnails

Now use your ftp client to upload your image gallery folders inside the gallery folder and then go to generate-thumbnails.php to generate thumbnails, you need to click on each folder name once to create thumbnails for each folder, if later on you add more pictures to your folder just click the folder name again in generate-thumbnails.php file.

After extracting the zip and moving the files to your server, you just need to add the usual permissions with **chmod**. _*I.e:* chmod 777 -R /var/www/mysite/gallery/_

It is recommended that you setup groups instead of just allowing everyone (777), so instead you may want to do something like:
> sudo chown -R www-data:www-data /var/www/mysite/gallery/

> sudo chmod -R 775 /var/www/mysite/gallery/

The photo gallery relies on gdlib. If your server does not already have it installed, you may be able to install it with the following command:

>sudo apt-get install php7.0-gd

**Note.** The above is just an example, the exact steps required on your own setup might be different. Some shared hosts will not need any modifications to permissions. In addition, you might also need to adjust the **upload_max_filesize** and **post_max_size** settings in _php.ini_. Have fun!

## Links:

https://phpphotogallery.com/ – PHP Photo Gallery Homepage

