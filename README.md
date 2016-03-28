Yii simple queue worker
=======================
Yii2 extension to provide SQL based queue worker

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist macklus/yii2-simple-queue "*"
```

or add

```
"macklus/yii2-simple-queue": "*"
```

to the require section of your `composer.json` file.


Configuration
-------------

Once the extension is installed, configure it define queue component :

```
'queue' => [
  'class' => 'macklus\SimpleQueue\SimpleQueue',
  'connection' => 'db',
  'table' => 'simpleQueue',
  'persistent' => true,
  'duplicate_jobs' => false,
],
```

Optional vars are:

1. connection: Database connection
2. table: Name of database table
3. persistent: If true, no jobs will be delete from database, marked as ENDED as well
4. duplicate_jobs: Search before insert new job to detect similar one exists

Usage
-----

Once the extension is configured, simply use it to put a job on a queue:

```php
Yii::$app->queue->putInTube($queue, [array_of_vars_of_job]);
```

Controller
----------

You need a controller to get (and process) all jobs. In example:

```php
<?php

namespace app\commands;

use macklus\SimpleQueue\SimpleQueueController;

class SqController extends SimpleQueueController
{

    const DELAY_PRIORITY = 1000; //Default priority
    const DELAY_TIME = 60; //Default delay time
    const DELAY_MAX = 3;

    public function listenTubes()
    {
        return ['test'];
    }

    public function actionTest($job)
    {
        $jobData = $job->getData();
        // do some stuff
        return self::DELETE;
    }
}
```
