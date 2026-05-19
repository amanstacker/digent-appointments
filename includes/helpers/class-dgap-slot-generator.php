<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class DGAP_Slot_Generator {

	/**
	 * Generate slots based on schedule, service, and date
	 *
	 * @param array  $schedule Schedule data including availability
	 * @param array  $service  Service data including duration and buffers
	 * @param string $date     Date for which to generate slots (Y-m-d format)
	 *
	 * @return array Generated slots with start, end, and label
	 */
	public static function generate_slot_from_schedule( $schedule, $service, $date ) {

		$slots = [];
		$availability = json_decode( $schedule['availability'], true );
		if ( empty( $availability ) ) {
			return $slots;
		}

		$day_key = strtolower( gmdate( 'l', strtotime( $date ) ) );

		if (
			empty( $availability[ $day_key ] ) ||
			$availability[ $day_key ]['status'] !== 'open'
		) {
			return $slots;
		}

		$day = $availability[ $day_key ];

		$day_start = strtotime( $date . ' ' . $day['open'] );
		$day_end   = strtotime( $date . ' ' . $day['close'] );

		if ( ! $day_start || ! $day_end || $day_start >= $day_end ) {
			return $slots;
		}

		// Service settings
		$duration       = absint( $service['duration'] );
		$slot_step      = absint( $service['slot_step'] );
		$buffer_before  = absint( $service['buffer_before'] );
		$buffer_after   = absint( $service['buffer_after'] );

		if ( $duration <= 0 || $slot_step <= 0 ) {
			return $slots;
		}

		$duration_sec = $duration * MINUTE_IN_SECONDS;
		$step_sec     = $slot_step * MINUTE_IN_SECONDS;
		$buffer_b_sec = $buffer_before * MINUTE_IN_SECONDS;
		$buffer_a_sec = $buffer_after * MINUTE_IN_SECONDS;

		$breaks = $day['breaks'] ?? [];

		for ( $start = $day_start; ; $start += $step_sec ) {

			$booking_start = $start;
			$booking_end   = $booking_start + $duration_sec;

			$effective_start = $booking_start - $buffer_b_sec;
			$effective_end   = $booking_end + $buffer_a_sec;

			if ( $effective_end > $day_end ) {
				break;
			}

			// Skip breaks
			$skip = false;

			foreach ( $breaks as $break ) {
				$break_start = strtotime( $date . ' ' . $break['start'] );
				$break_end   = strtotime( $date . ' ' . $break['end'] );

				if (
					$effective_start < $break_end &&
					$effective_end > $break_start
				) {
					$skip = true;
					break;
				}
			}

			if ( $skip ) {
				continue;
			}

			$slots[] = [
				'start' => date_i18n( 'H:i', $booking_start ),
				'end'   => date_i18n( 'H:i', $booking_end ),
				'label' => sprintf(
					'%s - %s',
					date_i18n( 'H:i', $booking_start ),
					date_i18n( 'H:i', $booking_end )
				),
			];
		}

		return $slots;
	}

	public static function is_slot_available( $schedule_id, $date, $start_time, $end_time ) {
		
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching --Reason Dynamic data is getting fetched here.
		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->prefix}dgap_bookings
				WHERE schedule_id = %d
				AND booking_date = %s
				AND status = 'confirmed'
				AND (
					start_time < %s
					AND end_time > %s
				)",
				$schedule_id,
				$date,
				$end_time,
				$start_time
			)
		);

		return $count == 0;
	}

}