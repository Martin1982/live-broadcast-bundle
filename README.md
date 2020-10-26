# Live Broadcast Bundle

[![Build status](https://travis-ci.org/Martin1982/live-broadcast-bundle.svg?branch=master)](https://travis-ci.org/Martin1982/live-broadcast-bundle)
[![Latest stable version](https://poser.pugx.org/martin1982/live-broadcast-bundle/v/stable)](https://packagist.org/packages/martin1982/live-broadcast-bundle)

[![License](https://poser.pugx.org/martin1982/live-broadcast-bundle/license)](https://packagist.org/packages/martin1982/live-broadcast-bundle)
[![Total downloads](https://poser.pugx.org/martin1982/live-broadcast-bundle/downloads)](https://packagist.org/packages/martin1982/live-broadcast-bundle)

## Table of contents

- [About](#about)
- [Prerequisites](#prerequisites)
- [Basic installation](#basic-installation)
- [Enabling Facebook Live](#enabling-facebook-live)
- [Enabling YouTube Live](#enabling-youtube-live)
- [Add new output platforms](#add-new-output-platforms)
- [Using an admin GUI](#using-an-admin-gui)

## About

The Live Broadcast Bundle will make it possible to plan live video streams to
various websites/apps like Twitch, YouTube Live, Facebook Live.

You are able to stream from various inputs. This package supports files, URL's or existing RTMP streams.

For more info you can view the latest recorded presentation below, check the demo project at https://github.com/Martin1982/live-broadcast-demo or read on;

[![IMAGE ALT TEXT](http://img.youtube.com/vi/axutXblArhM/0.jpg)](http://www.youtube.com/watch?v=axutXblArhM "High quality live broadcasting with PHP by @Martin1982 at @PHPamersfoort")

## Prerequisites

To test these prerequisites the Symfony command `livebroadcaster:test:shell` can be used after the installation described below.
If you like to check this manually the Broadcaster needs a few commands on an operating system;

All OS's:
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

## Basic installation

This bundle is available on Packagist. You can then install it using Composer:

```bash
$ composer require martin1982/live-broadcast-bundle
```

Use Doctrine to update your database schema with the broadcasting entities, when upgrading it is recommended to use migrations.

To start the broadcast scheduler you can run the following command:

```bash
$ php bin/console livebroadcaster:broadcast
```

To run the scheduler as a long-running process it's recommended to use the messenger middleware to keep the database
connection alive as described at https://symfony.com/doc/current/messenger.html

### FFMpeg log directory
To view the output of FFMpeg you need to configure a log directory in your `config/packages/live_broadcast.yaml`.
 
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
            upload_directory: '%kernel.project_dir%/public/uploads/thumbnails'
            web_path: '/uploads/thumbnails'

## Enabling Facebook Live
Create a Facebook app on https://developers.facebook.com with the following permissions:

- user_videos
- user_events
- user_managed_groups
- manage_pages
- publish_actions
- Live-Video API

Edit your `config/packages/live_broadcast.yaml` with the following configuration:

    live_broadcast:
        facebook:
            application_id: YourFacebookAppId
            application_secret: YourFacebookAppSecret

## Enabling YouTube Live

Login to https://console.developers.google.com/ and enable the 'YouTube Data API v3'.

Add the YouTube API info to your config.yml:

    live_broadcast:
        youtube:
            client_id: YourGoogleOauthClientId
            client_secret: YourGoogleOauthClientSecret
              
Add these lines to your parameters.yml (used for generating a thumbnail URL)

    parameters:
        router.request_context.host: broadcast.com
        router.request_context.scheme: https
    
## Add new output platforms

Create a new Channel Entity in Entity/Channel that extends the AbstractChannel (e.g. ChannelNew)

Create a new StreamOutput service in Service/StreamOutput that implements the OutputInterface (e.g. OutputNew)

Configure the service with the output tag in config/services.yml

    live.broadcast.output.new:
        class: Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputNew
        tags:
            - { name: live.broadcast.output, platform: 'New' }

## Using an admin GUI

This bundle comes without a web frontend interface, to make use of an admin package you can pick the one to your liking;
* Sonata Admin: https://github.com/Martin1982/live-broadcast-sonata-admin-bundle
* Easyadmin: https://github.com/Martin1982/live-broadcast-easyadmin-bundle
