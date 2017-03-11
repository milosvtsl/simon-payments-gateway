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
User    paylogic2
Pass    eVw{P7mphBn
```

## Installing

```
$ git checkout dev;
$ mysql -p < site/spg/spg.sql;
```

## Editing the Site Template

Open site/spg/test.html in a browser.

Edit the files:
```
web/view/theme/spg/assets/spg-theme.css
web/view/theme/spg/assets/spg-theme.js
```