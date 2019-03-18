# BlackCatTools/Yii2-FireFly

This project contains basic extensions for the Yii2 framework.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist blackcattools/yii2-firefly
```

```
composer require --prefer-dist blackcattools/yii2-firefly
```

or add

```json
"blackcattools/yii2-firefly": "~1.0.4"
```

to the `require` section of your composer.json.


Basic Usage
-----------

The following example shows how to use this extension:

```php

use common\component\ConsoleRemoteJson as Console;

$value = 10;   //bits 1 and 3 active

Console::inf0(500);
Console::warn(500);
Console::error(500);

```


