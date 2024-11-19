# seat-calendar

[![Latest Stable Version](https://img.shields.io/packagist/v/hermesdj/seat-calendar.svg?style=for-the-badge)](https://packagist.org/packages/hermesdj/seat-calendar)
[![Downloads](https://img.shields.io/packagist/dt/hermesdj/seat-calendar?style=for-the-badge)](https://packagist.org/packages/hermesdj/seat-calendar)
[![Core Version](https://img.shields.io/badge/SeAT-5.0.x-blue?style=for-the-badge)](https://github.com/eveseat/seat)
[![License](https://img.shields.io/github/license/hermesdj/seat-calendar?style=for-the-badge)](https://github.com/hermesdj/seat-calendar/blob/master/LICENCE)

Calendar plugin for EVE SeAT

# Features

* Create/Update/Cancel/Delete/Close & Tag operations
* Register to operations
* SeAT Notifications integration (slack & discord)
* Permissions
* Sync with discord event and sync discord event participants to the calendar op (only work for main characters)
* Auto sync paps from fleet members ingame every 15 minutes (only if the fleet commander is registered on SeAT and his
  token is working)
* Doctrine support if you install https://github.com/hermesdj/seat-fitting/releases. Since 1.0.6, the plugin also supports the official version https://github.com/eveseat-plugins/seat-fitting 5.0.8+

# Known limitations

Email notifications are not done.

# Release

https://packagist.org/packages/hermesdj/seat-calendar

# Compatibility

| SeAT Core | Calendar | Branch                                                          |
|-----------|----------|-----------------------------------------------------------------|
| 5.x       | 1.x      | [master](https://github.com/hermesdj/seat-calendar/tree/master) |

# Installation

* `composer require hermesdj/seat-calendar` in the SeAT root directory
* `php artisan vendor:publish --force`
* `php artisan migrate`
* `php artisan db:seed --class=Seat\\Kassie\\Calendar\\database\\seeds\\CalendarSettingsTableSeeder`
* `php artisan db:seed --class=Seat\\Kassie\\Calendar\\database\\seeds\\CalendarTagsSeeder`
* `php artisan db:seed --class=Seat\\Kassie\\Calendar\\database\\seeds\\ScheduleSeeder`

## Discord

The version compatible with SeAT 5.x comes with a discord integration able to sync calendar events on a discord server.
A bot must be setup for this to work. The bot can read the participant of the event on discord and mark the main character
of the account matched to the op. It only works if you are using seat-discord-connector.

## Create Bot on Discord Developer Portal

- Go to the [following url](https://discordapp.com/developers/applications) in order to create an application and
  retrieve bot token.
- Give it a name and suitable description; so user will be able to know what it is related to later.
- On sidebar, click on **Oauth2** > **General** and hit the **Add Redirect** button twice and seed spawned field with
  the address bellow :
    - `{seat-public-url}/calendar/setting/discord/callback`
- On sidebar click on **Bot** and hit the **Add Bot** button
    - Check **Public Bot**
    - Check **Requires OAuth2 Code Grant**
    - Check **Server Members Intent**

## Since 1.3.2 of forked project

Since 1.3.2, the PAP mechanism has been implemented. You need `esi-fleets.read_fleet.v1` into your requested scopes
list.

# Feedbacks or support

@jaysgaming2023 on eve-seat discord  
jays.gaming.contact@gmail.com
Jay Fendragon/Kyra Skeako in game

# Screenshots

## Main display

![Main display](./img/main_display.png "Main display")

## Details of an operation

![Details of an operation](./img/operation_details.png "Details of an operation")

## Customize your tags

![Customize your tags](./img/tags_creation.png "Customize your tags")
![Customize your tags](./img/tags_management.png "Manage your tags")

## Slack integration

![Slack integration](http://i.imgur.com/zV2w9sx.png "Slack integration")

## Pap feature

![Paps charts](https://user-images.githubusercontent.com/648753/34275321-0af18d90-e69d-11e7-9a93-31c07f4b303c.png "Paps charts")
![Paps character tracking](https://user-images.githubusercontent.com/648753/34328226-dc165886-e8d9-11e7-8084-731b0d674f8d.png "Paps character tracking")
