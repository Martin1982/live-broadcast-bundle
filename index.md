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
various channels like Twitch, YouTube and Facebook (referred to as Output or Channels).

The basic "Input" will be a file that can be read. Other inputs will be created thereafter.

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

To test these prerequisites the Symfony command `livebroadcaster:test:shell` can be used after the installation described below.

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

## Facebook Live

### Step 1: Create a Facebook App
Create a Facebook app on https://developers.facebook.com with the following permissions:

- user_videos
- user_events
- user_managed_groups
- manage_pages
- publish_actions
- Live-Video API

### Step 2: Add your App ID and Secret to app/config/parameters.yml
    parameters:
        live_broadcast_fb_app_id: '{application_id}'
        live_broadcast_fb_app_secret: '{application_secret}'

### Step 3: Add the Facebook application id to your config.yml:
	live_broadcast:
	    facebook:
	        application_id: '%live_broadcast_fb_app_id%'
	        application_secret: '%live_broadcast_fb_app_secret%'

### Step 4 (Sonata users only): Load the app ID in Twig
    twig:
        globals:
            live_broadcast_facebook_app_id: '%live_broadcast_fb_app_id%'   

## YouTube Live

### Step 1: Request API access
Login to https://console.developers.google.com/ and enable the 'YouTube Data API v3'.

Setup oAuth Credentials for your server. In case you're using the Sonata Admin from this
bundle the redirect URI's path is `<your domain>/admin/channel/youtube/oauthprovider`

### Step 2: Add your Client ID and Secret and redirect route to app/config/parameters.yml
    parameters:
        live_broadcast_yt_client_id: '{application_id}'
        live_broadcast_yt_client_secret: '{application_secret}'
        live_broadcast_yt_redirect_route: '{your_oauth_handler_url or admin_martin1982_livebroadcast_channel_basechannel_youtubeoauth}'

### Step 3: Add the YouTube API info to your config.yml:
	live_broadcast:
	    youtube:
            client_id: '%live_broadcast_yt_client_id%'
            client_secret: '%live_broadcast_yt_client_secret%'
            redirect_route: '%live_broadcast_yt_redirect_route%'

### Step 4 (Sonata users only): Add the Sonata block to your blocks config:
    
    sonata_block:
        blocks:
        sonata.block.service.youtubeauth:
            contexts:   [admin]
             
## Add new output platforms

### Step 1: Create a new Channel Entity in Entity/Channel that extends the BaseChannel (for e.g. ChannelNew)

### Step 2: Create a new StreamOutput service in Service/StreamOutput that implements the OutputInterface (for e.g. OutputNew)

### Step 3: Configure the service with the output tag in Resources/config/services.yml
    live.broadcast.output.new:
        class: Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputNew
        tags:
            - { name: live.broadcast.output, platform: 'New' }

### Step 4: (Optional) Add a new form for the Channel in Admin/ChannelAdmin.php
``` php
protected function configureFormFields(FormMapper $formMapper)
{
    if ($subject instanceof ChannelNew) {
        $formMapper->add('...', 'text', array('label' => '...'));
    }
}
```

### Step 5: (Optional) Add the sub class for the channelAdmin in Resources/config/admin.yml for 
    sonata.admin.channel
        calls:
            - [setSubClasses, [ { "Name": Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelNew } ] ]
