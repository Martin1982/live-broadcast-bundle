Live Broadcast Bundle
=====================

*This project is still in experimental phase and in no way production ready.
Usage should be limited to development work.*

[![Build Status](https://travis-ci.org/Martin1982/live-broadcast-bundle.svg?branch=master)](https://travis-ci.org/Martin1982/live-broadcast-bundle)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/6027a50f-06cf-4989-8267-9f481e838b2a/mini.png)](https://insight.sensiolabs.com/projects/6027a50f-06cf-4989-8267-9f481e838b2a)

[![Latest Stable Version](https://poser.pugx.org/martin1982/live-broadcast-bundle/v/stable)](https://packagist.org/packages/martin1982/live-broadcast-bundle)
[![Total Downloads](https://poser.pugx.org/martin1982/live-broadcast-bundle/downloads)](https://packagist.org/packages/martin1982/live-broadcast-bundle)
[![Latest Unstable Version](https://poser.pugx.org/martin1982/live-broadcast-bundle/v/unstable)](https://packagist.org/packages/martin1982/live-broadcast-bundle)
[![License](https://poser.pugx.org/martin1982/live-broadcast-bundle/license)](https://packagist.org/packages/martin1982/live-broadcast-bundle)

The Live Broadcast Bundle will make it possible to plan live streams to
various channels like Twitch, Youtube and Facebook (referred to as Output or Channels).

The basic "Input" will be a file that can be read. Other inputs will be created thereafter.

## Installation

### Step 1: Download Bundle

This bundle will be made available on Packagist. You can then install it using Composer:

```bash
$ composer require martin1982/live-broadcast-bundle
```

### Step 2: Enable the bundle

Next, enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Martin1982\LiveBroadcastBundle\LiveBroadcastBundle(),
    );
}
```

### Step 3: Update your database schema

Use doctrine to update your database schema with the broadcasting entities

### Step 4 (Optional): Activate the Sonata Admin module

To make planning available through an admin interface it is recommended to use the Sonata Admin bundle.

### Step 5 (Optional): Start the broadcast!

To start a broadcast you can use a console command, you can add this to a cronjob to automate your broadcast schedule.

```bash
$ app/console livebroadcaster:broadcast
```

## Prerequisites

The Broadcaster needs a few commands;

* `ffmpeg`

On Linux:
* `ps`
* `kill`

On Mac:
* `ps`
* `grep`
* `kill`

On Windows:
* `tasklist`
* `taskkill`

To test these prerequisites the Symfony command `livebroadcaster:test:shell` can be used.
