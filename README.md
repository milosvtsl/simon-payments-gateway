# Simon Payments Gateway

PHP/MySQL Integrated Payment Processor written by Ari Asulin

`© 2017 Simon Payments, LLC. All rights reserved.`

## Prerequisites

```
PHP > 5.5.0
(extensions: php-curl php-soap php-imap php-mcrypt php-pgsql)
```

```
MySQL > 5.5
User    paylogic2@localhost
Pass    eVw{P7mphBn
```

## Installing

```
$ git checkout dev;
$ mysql -p < site/spg/spg.sql; 
$ mysql -p < "GRANT ALL ON *.* TO 'paylogic2'@'localhost' IDENTIFIED BY PASSWORD '*1D6352F7787D249137604DCE6CFC43B1D82B8715' WITH GRANT OPTION;"
```

## Editing the Site Template without deployment

1. Open site/spg/test.html in a browser.

2. Edit the files:
```
web/view/theme/spg/assets/spg-theme.css
web/view/theme/spg/assets/spg-theme.js
```

3. Commit & Push
```
$ git add .;
$ git commit -m "Site Theme Changes";
$ git push;
```
