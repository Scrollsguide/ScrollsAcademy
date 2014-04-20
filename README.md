# Scrolls Academy website

Source code for the Scrolls Academy website.

## Installation

### Prerequisites

- Apache >= 2.2.20
  - mod_headers enabled
  - mod_expires enabled
  - mod_rewrite enabled
- PHP >= 5.3.6 with PDO
- MySQL >= 14.14

### Configuration

ScrollsAcademy can only run in the root of a website or subdomain. Clone this repository to your server and point Apache to `your_installation_directory/public_html`. Make sure the .htaccess is being used by setting `AllowOverride All` in your Apache website settings.

Change `config/config.ini` to use your database settings.
Run `install/db.sql` with your SQL installation to configure your database.

The folders `cache` and `public_html/assets/cache` should be writeable.

## Admin

The admin panel is available at `your_website_address.tld/admin`, where you can login using admin/admin for username and password. From there, you can add guides, change the homepage looks and add series. 

### Adding accounts

You can integrate access to the admin panel with your own website's account system by extending `classes/Security/AccountProvider.php` and implementing the authenticate function. In the config file, set `accountprovider` to your own class's name. See `extensions/DemoAccountProvider.php` for an example.
