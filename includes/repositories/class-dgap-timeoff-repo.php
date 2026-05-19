<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class DGAP_Timeoff_Repo {

	private static function table() {
		global $wpdb;
		return $wpdb->prefix . 'dgap_timeoff';
	}

	public static function get_all() {
		global $wpdb;	
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Fetching all records from custom table; no user input involved and caching is not required here.
		return $wpdb->get_results( "SELECT * FROM " . self::table() . " ORDER BY id DESC", ARRAY_A );
	}

	public static function get( $id ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Fetching a single record by specific ID from custom table; result is request-specific and caching is not required here.
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . self::table() . " WHERE id=%d", $id ), ARRAY_A );
	}

	public static function insert( $data ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->insert(
			self::table(),
			[
				'name'       => $data['name'],
				'type'       => $data['type'],
				'entity_ids' => wp_json_encode( $data['entity_ids'] ),
				'dates'      => wp_json_encode( $data['dates'] ),
				'status'     => $data['status'],
			],
			[ '%s','%s','%s','%s','%d' ]
		);

		return (int) $wpdb->insert_id;
	}

	public static function update( $id, $data ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Updating a specific record by ID in custom table; caching is not required here.
		return $wpdb->update(
			self::table(),
			[
				'name'       => $data['name'],
				'type'       => $data['type'],
				'entity_ids' => wp_json_encode( $data['entity_ids'] ),
				'dates'      => wp_json_encode( $data['dates'] ),
				'status'     => $data['status'],
			],
			[ 'id' => $id ],
			[ '%s','%s','%s','%s','%d' ],
			[ '%d' ]
		);
	}

	public static function delete( $id ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Deleting a specific record by ID from custom table; caching is not required here.
		return $wpdb->delete( self::table(), [ 'id' => $id ], [ '%d' ] );
	}
}
