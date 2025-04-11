<?php

if ( ! defined( 'AUS_TARGET_DAYS')) {
	define( 'AUS_TARGET_DAYS', [ 1, 2, 3, 4, 5] );
}

if ( ! defined( 'AUS_TARGET_HOUR')) {
	define( 'AUS_TARGET_HOUR', 01 );
}

add_action('init', 'disable_auto_updates_as_needed');
add_action('init', 'set_auto_update_schedule');

/**
 * Disables WordPress automatic updates on certain days of the week.
 *
 * @return void
 */
function disable_auto_updates_as_needed(): void {
	$currentDayOfWeek = intval( current_datetime()->format( 'N' ) );

	$dayIsOk            = in_array( $currentDayOfWeek, AUS_TARGET_DAYS );
	$disableAutoUpdates = ! $dayIsOk;

	if ( $disableAutoUpdates ) {
		add_filter( 'automatic_updater_disabled', '__return_true' );
	} else {
		add_filter( 'automatic_updater_disabled', '__return_false' );
	}
}

/**
 * Schedules an automated daily update at a specific time, ensuring it aligns with the target schedule.
 *
 * This method verifies the current schedule for automated updates and adjusts it if necessary.
 * If an update is already scheduled but does not match the desired schedule, it clears the existing schedule
 * and creates a new one at the specified time. If no update is scheduled, it initiates a new schedule.
 *
 * @return void
 * @throws DateMalformedStringException // This is handled gracefully but adding here to appease PhpStorm.
 */
function set_auto_update_schedule(): void {
	$targetHour = AUS_TARGET_HOUR;
	$now        = current_datetime();
	$timezone   = $now->getTimezone();

	$currentHour = $now->format( 'G' );

	// If we're in or past the target hour, we need to schedule auto updates to start tomorrow.
	if ( $currentHour < $targetHour ) {
		$targetDay = $now->format( 'Y-m-d' );
	} else {
		try {
			$targetDay = $now->modify( '+1 day' )?->format( 'Y-m-d' );
		} catch ( DateMalformedStringException $e ) {
			error_log('Could not modify current date to get target day: ' . $e->getMessage());
			
			$targetDay = null;
		}
	}

	if ( ! $targetDay ) {
		error_log('Could not determine target day');
		
		return;
	}

	$targetTime         = new DateTime( "{$targetDay} {$targetHour}:30:00", $timezone );
	$targetTimestamp    = $targetTime->getTimestamp();
	$scheduledTimestamp = wp_next_scheduled( 'wp_maybe_auto_update' );
	$shouldSchedule     = false;

	// If auto update is scheduled, we need to check it to make sure it's scheduled for our desired time.
	if ( $scheduledTimestamp ) {
		$diff = abs( $scheduledTimestamp - $targetTimestamp );

		// 5-minute wiggle room.
		if ( $diff > 300 ) {
			$shouldSchedule = true;
			wp_clear_scheduled_hook( 'wp_maybe_auto_update' );
		}
	} else {
		$shouldSchedule = true;
	}

	if ( $shouldSchedule ) {
		wp_schedule_event( $targetTimestamp, 'daily', 'wp_maybe_auto_update' );
	}
}