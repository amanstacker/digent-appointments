<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class DGAP_Slot_Generator {

	/**
	 * Merge available slots across multiple matching connections (e.g. morning + evening shifts).
	 */
	public static function generate_slots_from_connections( $connections, $service, $date, $include_booked = false ) {
		if ( empty( $connections ) || ! is_array( $connections ) ) {
			return [];
		}

		$merged = [];
		foreach ( $connections as $schedule ) {
			$slots = self::generate_slot_from_schedule( $schedule, $service, $date, $include_booked );
			foreach ( $slots as $slot ) {
				if ( ! isset( $merged[ $slot['start'] ] ) || ( ! empty( $merged[ $slot['start'] ]['disabled'] ) && empty( $slot['disabled'] ) ) ) {
					$merged[ $slot['start'] ] = $slot;
				}
			}
		}

		ksort( $merged );
		return array_values( $merged );
	}

	/**
	 * Generate slots based on schedule, service, and date
	 *
	 * @param array  $schedule       Connection data including availability
	 * @param array  $service        Service data including duration and buffers
	 * @param string $date           Date for which to generate slots (Y-m-d format)
	 * @param bool   $include_booked Whether to include booked/full slots marked as disabled
	 *
	 * @return array Generated slots with start, end, label, and disabled flag
	 */
	public static function generate_slot_from_schedule( $schedule, $service, $date, $include_booked = false ) {

		$slots = [];
		$availability = json_decode( $schedule['availability'], true );
		if ( empty( $availability ) ) {
			return $slots;
		}

		$today_str = current_time( 'Y-m-d' );
		if ( $date < $today_str ) {
			return $slots;
		}

		$advanced_days = absint( $service['advanced_booking'] ?? 0 );
		if ( $advanced_days > 0 ) {
			$min_date = gmdate( 'Y-m-d', strtotime( $today_str . ' +' . $advanced_days . ' days' ) );
			if ( $date < $min_date ) {
				return $slots;
			}
		}

		$service_id  = absint( $service['id'] ?? 0 );
		$daily_limit = absint( $service['daily_limit'] ?? 0 );
		if ( $service_id > 0 && $daily_limit > 0 ) {
			global $wpdb;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Request-specific daily limit check.
			$daily_count = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->prefix}dgap_bookings WHERE service_id = %d AND booking_date = %s AND status IN ('pending', 'confirmed', 'reserved')",
					$service_id,
					$date
				)
			);
			if ( $daily_count >= $daily_limit ) {
				return $slots;
			}
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

		$breaks = is_array( $day['breaks'] ?? null ) ? $day['breaks'] : [];

		// Intersect with Location business hours & breaks
		$location_id = absint( $schedule['location_id'] ?? 0 );
		if ( $location_id > 0 ) {
			global $wpdb;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Request-specific location hours check.
			$loc_hours_raw = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT business_hours FROM {$wpdb->prefix}dgap_locations WHERE id = %d AND status = 1",
					$location_id
				)
			);
			$loc_hours = $loc_hours_raw ? json_decode( $loc_hours_raw, true ) : null;
			if ( is_array( $loc_hours ) && isset( $loc_hours[ $day_key ] ) ) {
				$loc_day = $loc_hours[ $day_key ];
				if ( empty( $loc_day['status'] ) || 'open' !== $loc_day['status'] ) {
					return $slots;
				}
				if ( ! empty( $loc_day['open'] ) ) {
					$loc_open = strtotime( $date . ' ' . $loc_day['open'] );
					if ( $loc_open && $loc_open > $day_start ) {
						$day_start = $loc_open;
					}
				}
				if ( ! empty( $loc_day['close'] ) ) {
					$loc_close = strtotime( $date . ' ' . $loc_day['close'] );
					if ( $loc_close && $loc_close < $day_end ) {
						$day_end = $loc_close;
					}
				}
				if ( $day_start >= $day_end ) {
					return $slots;
				}
				if ( ! empty( $loc_day['breaks'] ) && is_array( $loc_day['breaks'] ) ) {
					$breaks = array_merge( $breaks, $loc_day['breaks'] );
				}
			}
		}

		// Respect active Worker & Service Vacations on this date
		$worker_id = absint( $schedule['worker_id'] ?? 0 );
		if ( $worker_id > 0 || $service_id > 0 ) {
			global $wpdb;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Request-specific vacation check.
			$vacations = $wpdb->get_results(
				"SELECT type, entity_ids, dates FROM {$wpdb->prefix}dgap_vacations WHERE status = 1",
				ARRAY_A
			);
			foreach ( (array) $vacations as $vac ) {
				$eids = json_decode( $vac['entity_ids'] ?? '[]', true );
				if ( ! is_array( $eids ) ) {
					continue;
				}
				$eids = array_map( 'absint', $eids );
				$applies = ( 'worker' === ( $vac['type'] ?? '' ) && $worker_id > 0 && in_array( $worker_id, $eids, true ) )
					|| ( 'service' === ( $vac['type'] ?? '' ) && $service_id > 0 && in_array( $service_id, $eids, true ) );
				if ( ! $applies ) {
					continue;
				}
				$vdates = json_decode( $vac['dates'] ?? '[]', true );
				if ( ! is_array( $vdates ) ) {
					continue;
				}
				foreach ( $vdates as $vd ) {
					if ( ( $vd['date'] ?? '' ) !== $date ) {
						continue;
					}
					if ( 'full' === ( $vd['mode'] ?? 'full' ) ) {
						return $slots;
					}
					if ( 'time' === ( $vd['mode'] ?? '' ) && ! empty( $vd['time_start'] ) && ! empty( $vd['time_end'] ) ) {
						$breaks[] = [
							'start' => $vd['time_start'],
							'end'   => $vd['time_end'],
						];
					}
				}
			}
		}

		// Service settings
		$duration       = absint( $service['duration'] );
		$slot_step      = ! empty( $service['slot_step'] ) && absint( $service['slot_step'] ) > 0
			? absint( $service['slot_step'] )
			: $duration;
		$buffer_before  = absint( $service['buffer_before'] );
		$buffer_after   = absint( $service['buffer_after'] );

		if ( $duration <= 0 || $slot_step <= 0 ) {
			return $slots;
		}

		$duration_sec = $duration * MINUTE_IN_SECONDS;
		$step_sec     = $slot_step * MINUTE_IN_SECONDS;
		$buffer_b_sec = $buffer_before * MINUTE_IN_SECONDS;
		$buffer_a_sec = $buffer_after * MINUTE_IN_SECONDS;

		$connection_id = absint( $schedule['id'] ?? 0 );
		$capacity    = max( 1, absint( $schedule['capacity'] ?? 1 ) );
		$now_ts      = current_time( 'timestamp' );
		$is_today    = ( $date === $today_str );

		$existing_bookings = [];
		if ( $connection_id > 0 ) {
			global $wpdb;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Request-specific booked slot check.
			$existing_bookings = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT start_time, end_time FROM {$wpdb->prefix}dgap_bookings WHERE connection_id = %d AND booking_date = %s AND status IN ('pending', 'confirmed', 'reserved')",
					$connection_id,
					$date
				),
				ARRAY_A
			);
		}

		$general_settings = get_option( 'dgap_general_settings', [] );
		$time_format      = ( '12' === (string) ( $general_settings['time_format'] ?? '24' ) ) ? 'g:i A' : 'H:i';
		$slot_label_style = $general_settings['slot_label_style'] ?? 'from_to';

		for ( $start = $day_start; ; $start += $step_sec ) {

			$booking_start = $start;
			$booking_end   = $booking_start + $duration_sec;

			$effective_start = $booking_start - $buffer_b_sec;
			$effective_end   = $booking_end + $buffer_a_sec;

			if ( $effective_end > $day_end ) {
				break;
			}

			// Skip past slots on today's date
			if ( $is_today && $booking_start <= $now_ts ) {
				continue;
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

			// Check whether slot reached connection capacity
			$is_booked_full = false;
			if ( ! empty( $existing_bookings ) ) {
				$slot_start_hi = gmdate( 'H:i', $booking_start );
				$slot_end_hi   = gmdate( 'H:i', $booking_end );
				$overlap_count = 0;

				foreach ( $existing_bookings as $bk ) {
					$bk_start = substr( (string) $bk['start_time'], 0, 5 );
					$bk_end   = substr( (string) $bk['end_time'], 0, 5 );
					if ( $bk_start < $slot_end_hi && $bk_end > $slot_start_hi ) {
						$overlap_count++;
					}
				}

				if ( $overlap_count >= $capacity ) {
					if ( ! $include_booked ) {
						continue;
					}
					$is_booked_full = true;
				}
			}

			$label = ( 'from' === $slot_label_style )
				? date_i18n( $time_format, $booking_start )
				: sprintf(
					'%s - %s',
					date_i18n( $time_format, $booking_start ),
					date_i18n( $time_format, $booking_end )
				);

			$slots[] = [
				'start'    => date_i18n( 'H:i', $booking_start ),
				'end'      => date_i18n( 'H:i', $booking_end ),
				'label'    => $label,
				'disabled' => $is_booked_full,
			];
		}

		return $slots;
	}

	public static function is_slot_available( $connection_id, $date, $start_time, $end_time ) {
		
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching --Reason Dynamic data is getting fetched here.
		$capacity = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT capacity FROM {$wpdb->prefix}dgap_connections WHERE id = %d",
				$connection_id
			)
		);
		$capacity = max( 1, $capacity );

		$start_time = ( strlen( (string) $start_time ) === 5 ) ? $start_time . ':00' : (string) $start_time;
		$end_time   = ( strlen( (string) $end_time ) === 5 ) ? $end_time . ':00' : (string) $end_time;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching --Reason Dynamic data is getting fetched here.
		$count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->prefix}dgap_bookings
				WHERE connection_id = %d
				AND booking_date = %s
				AND status IN ('pending', 'confirmed', 'reserved')
				AND (
					start_time < %s
					AND end_time > %s
				)",
				$connection_id,
				$date,
				$end_time,
				$start_time
			)
		);

		return $count < $capacity;
	}

}