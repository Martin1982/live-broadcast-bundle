# Live Broadcast Bundle

*As we're stabilizing the bundle please note that database schema and config changes may occur*

[![Build Status](https://travis-ci.org/Martin1982/live-broadcast-bundle.svg?branch=master)](https://travis-ci.org/Martin1982/live-broadcast-bundle)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/6027a50f-06cf-4989-8267-9f481e838b2a/mini.png)](https://insight.sensiolabs.com/projects/6027a50f-06cf-4989-8267-9f481e838b2a)

[![Latest Stable Version](https://poser.pugx.org/martin1982/live-broadcast-bundle/v/stable)](https://packagist.org/packages/martin1982/live-broadcast-bundle)
[![Total Downloads](https://poser.pugx.org/martin1982/live-broadcast-bundle/downloads)](https://packagist.org/packages/martin1982/live-broadcast-bundle)
[![Latest Unstable Version](https://poser.pugx.org/martin1982/live-broadcast-bundle/v/unstable)](https://packagist.org/packages/martin1982/live-broadcast-bundle)
[![License](https://poser.pugx.org/martin1982/live-broadcast-bundle/license)](https://packagist.org/packages/martin1982/live-broadcast-bundle)

## Table of contents

- [About](#about)
- [Prerequisites](#prerequisites)
- [Basic installation](#basic-installation)
- [Enabling Facebook Live](#enabling-facebook-live)
- [Enabling YouTube Live](#enabling-youtube-live)
- [Add new output platforms](#add-new-output-platforms)

## About

The Live Broadcast Bundle will make it possible to plan live video streams to
various channels like Twitch, YouTube Live, Facebook Live, UStream and Live.Ly (referred to as Output or Channels).

As "Input" we support files, URL's or existing RTMP streams.

For more info you can view the latest recorded presentation below, check the demo project at https://github.com/Martin1982/live-broadcast-demo or read on;

[![IMAGE ALT TEXT](http://img.youtube.com/vi/axutXblArhM/0.jpg)](http://www.youtube.com/watch?v=axutXblArhM "High quality live broadcasting with PHP by @Martin1982 at @PHPamersfoort")

## Prerequisites

The Broadcaster needs a few commands;

* `ffmpeg 3.x or higher`

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

To test these prerequisites the Symfony command `livebroadcaster:test:shell` can be used after the installation described below.

## Basic installation

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

### Step 4 (Sonata users only): Activate the Sonata Admin module

To make planning available through an admin interface it is recommended to use the Sonata Admin bundle.

### Step 5 (Optional): Start the broadcast!

To start the broadcast scheduler you can run the following command:

```bash
$ php app/console livebroadcaster:broadcast
```

### FFMpeg log directory
To view the output of FFMpeg you need to configure a log directory in your `app/config/config.yml`.
 
     live_broadcast:
        ffmpeg:
            log_directory: '%kernel.logs_dir%'

### Event loop
You can use this configuration to set the event loop timer:

    live_broadcast:
        eventloop:
            timer: 5

### Thumbnailer setup
Setup the following config for thumbnails:
    
    live_broadcast:
        thumbnail:
            upload_directory: '%kernel.root_dir%/../web/uploads/thumbnails'
            web_path: '/uploads/thumbnails'

## Enabling Facebook Live

### Step 1: Create a Facebook App
Create a Facebook app on https://developers.facebook.com with the following permissions:

- user_videos
- user_events
- user_managed_groups
- manage_pages
- publish_actions
- Live-Video API

### Step 2: Edit your `app/config/config.yml` with the following configuration:
    live_broadcast:
        facebook:
            application_id: YourFacebookAppId
            application_secret: YourFacebookAppSecret

### Step 3 (Sonata users only): Sonata config update 
Add the Sonata block to your blocks config:

    sonata_block:
        blocks:
        sonata.block.service.facebookauth:
            contexts:   [admin]

## Enabling YouTube Live

### Step 1: Request API access
Login to https://console.developers.google.com/ and enable the 'YouTube Data API v3'.

Setup oAuth Credentials for your server. In case you're using the Sonata Admin from this
bundle the redirect URI's path is `<your domain>/admin/channel/youtube/oauthprovider`

### Step 2: Update config
Add the YouTube API info to your config.yml:

    live_broadcast:
        youtube:
            client_id: YourGoogleOauthClientId
            client_secret: YourGoogleOauthClientSecret
            redirect_route: admin_martin1982_livebroadcast_channel_abstractchannel_youtubeoauth

### Step 3 (Sonata users only): Sonata config update 
Add the Sonata block to your blocks config:

    sonata_block:
        blocks:
        sonata.block.service.youtubeauth:
            contexts:   [admin]
             
### Step 4: Update parameters 
Add these lines to your parameters.yml (used for generating a thumbnail URL)

    parameters:
        router.request_context.host: broadcast.com
        router.request_context.scheme: https
    
## Add new output platforms

### Step 1: Create channel entity 
Create a new Channel Entity in Entity/Channel that extends the AbstractChannel (e.g. ChannelNew)

### Step 2: Create streamoutput service 
Create a new StreamOutput service in Service/StreamOutput that implements the OutputInterface (e.g. OutputNew)

### Step 3: Configure your service 
Configure the service with the output tag in Resources/config/services.yml

    live.broadcast.output.new:
        class: Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputNew
        tags:
            - { name: live.broadcast.output, platform: 'New' }

### Step 4 (Sonata users only): Create Sonata form
Add a new form for the Channel in Admin/ChannelAdmin.php

``` php
protected function configureFormFields(FormMapper $formMapper)
{
    if ($subject instanceof ChannelNew) {
        $formMapper->add('...', 'text', array('label' => '...'));
    }
}
```

### Step 5 (Sonata users only): Create channeladmin subclass 
Add the sub class for the channelAdmin in Resources/config/admin.yml for 

    sonata.admin.channel
        calls:
            - [setConfiguredSubclasses, [ { "Name": Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelNew } ] ]
