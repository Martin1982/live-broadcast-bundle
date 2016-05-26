Live Broadcast Bundle
=====================

*This project is still in experimental phase and in no way production ready.
Usage should be limited to development work.*

The Live Broadcast Bundle will make it possible to plan live streams to
various channels like Twitch, Youtube and Facebook (referred to as Output or Channels).

The basic "Input" will be a file that can be read. Other inputs will be created thereafter.

## Installation

### Step 1: Download SolrBundle

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

## Prerequisites

The Broadcaster currently only supports Linux based OS's and needs a few commands;

* `ffmpeg`
* `ps`
* `grep`
* `kill`

To test these prerequisites the Symfony command `livebroadcaster:test:shell` can be used.

