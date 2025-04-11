# WordPress Auto Update Scheduler

A little must-use plugin for scheduling auto-updates. By default, when auto-updates are enabled in WordPress, they happen every day at an arbitrary time (whenever it was first initialized). This plugin allows you to easily control the days when updates are allowed and the time of day when they happen.

Specifically, it does two things:
1. Manipulates the schedule for `wp_maybe_auto_update()`.
2. Controls `automatic_updater_disabled` with a high-priority filter.

A few things to note:
- You still need to enable auto updates for individual plugins. This plugin only controls the sites overall ability to do auto-updates.
- This plugin is meant to override other things that try to control `automatic_updater_disabled`. Thus the high priority.

## Installation

Copy `wp-auto-update-scheduler.php` into your site's must-use plugins directory (`/wp-content/mu-plugins` by default). You don't need any of the other files in this repo.

## Usage

Out of the box, this plugin sets the update time to 1:30 am and only allows updates Monday - Friday.

> NOTE: this uses your site's configured timezone, which is UTC by default. You can change it in **Settings > General**.

You can change the default behavior with the constants below. Add these to `wp-config.php` if needed.

### AUS_TARGET_DAYS
Use this to change the days of the week when auto-updates are enabled. It should be an array of numbers, where 1 is Monday and 7 is Sunday. 

**Example**: to only allow updates on Tuesdays and Thursdays, set the constant to `[2, 4]`.

### AUS_TARGET_HOUR
Use this to change the hour of the day when auto-updates are enabled. It should be a number between 0 and 23. Updates will be scheduled for the 30-minute mark of the specified hour.

### AUS_PRIORITY
Use this to change the priority of the filter that controls whether auto-updates are enabled. It's 100 by default. If the plugin is trying to enable/disable auto-updates and something is overriding it, try increasing this number. If instead you want something else to override this plugin, it's probably best to just not use this plugin.

## License

This project is licensed under the MIT License.
See the LICENSE file for details.
© 2025 Diego Vogel