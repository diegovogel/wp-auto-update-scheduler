<?php

add_action('init', 'disable_auto_updates_as_needed');
add_action('init', 'set_auto_update_schedule');

/**
 * Disables WordPress automatic updates on certain days of the week.
 *
 * @return void
 */
function disable_auto_updates_as_needed(): void {
	$currentDayOfWeek = intval( current_datetime()->format( 'N' ) );
	$targetDays       = [ 1, 2, 3, 4, 5 ]; // Weekdays

	$dayIsOk            = in_array( $currentDayOfWeek, $targetDays );
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
 * @throws Exception If the target day cannot be determined.
 * @throws DateMalformedStringException
 */
function set_auto_update_schedule(): void {
	$targetHour = 01;
	$now        = current_datetime();
	$timezone   = $now->getTimezone();

	$currentHour = $now->format( 'G' );

	// If we're in or past the target hour, we need to schedule auto updates to start tomorrow.
	if ( $currentHour < $targetHour ) {
		$targetDay = $now->format( 'Y-m-d' );
	} else {
		$targetDay = $now->modify( '+1 day' )?->format( 'Y-m-d' );
	}

	if ( ! $targetDay ) {
		throw new Exception( 'Could not determine target day.' );
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