# Autologin for Magento 2

This module is an Autologin implementation for Magento2 allowing you to enter automatically using the token returned by the login call via API

## Installing in your Magento

Follow this guide: https://getcomposer.org/doc/05-repositories.md#vcs

1) Add to repositories of composer.json in your project:

```json
  "adiacent": {
      "type": "vcs",
      "url": "https://srvgitlab.apra.it/adiacent/magento2-autologin"
  }
```

2) Add to require of composer.json in your project:
  
```json
  "adiacent/autologin": "dev-master"
```

3) Run command:
  
```sh
php composer.phar update
```

4) Run command:
  
```sh
php bin/magento setup:upgrade
```

## Using in your Magento

1) Activate API Integration in your Magento

2) Call Login Customer API with url:
   http://your.magento.it/rest/V1/integration/customer/token/ 
   and with username and password like this guide explains:
   https://devdocs.magento.com/guides/v2.4/get-started/authentication/gs-authentication-token.html

3) Using your token to call the url:
   http://your.magento.it/adiacent_autologin/?token=XXXXXXXXXXXXXXXXXXXXX

  You are logged in!! 