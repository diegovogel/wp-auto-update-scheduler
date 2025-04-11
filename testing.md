Setting up a testing environment for this isn't worth it, so here's a list of manual tests for verifying functionality after making changes to the codebase.

## Setup
On a local WP site:
- Install this plugin.
- Install WP Crontrol.
- Start a Tinkerwell session against the site and add the snippet below.

```php
$auto_updater_disabled =
  defined("AUTOMATIC_UPDATER_DISABLED") && AUTOMATIC_UPDATER_DISABLED;
$filter_disabled = apply_filters(
  "automatic_updater_disabled",
  $auto_updater_disabled
);

if ($filter_disabled) {
  echo "Automatic updater is disabled (constant or filter).";
} else {
  echo "Automatic updater is enabled.";
}
```

## Tests
- [ ] Use `wp-config` to change the hour. Verify the `wp_maybe_auto_update()` schedule changed in WP Crontrol.
- [ ] Remove the hour constant from `wp-config`. Verify the schedule returned to the plugin's default in WP Crontrol.
- [ ] Use `wp-config` to change the allowed days so updates are **disabled** today. Run the Tinkerwell snippet to verify updates are disabled.
- [ ] Use `wp-config` to change the allowed days so updates are **enabled** today. Run the Tinkerwell snippet to verify updates are enabled.