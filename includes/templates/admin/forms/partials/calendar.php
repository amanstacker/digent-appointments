<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
$calendar_style  = $form['settings']['calendar_style'] ?? 'modern';
$design          = $form['settings']['design'] ?? 'flat';
$slot_position   = $form['settings']['slot_position'] ?? ( 'flat' === $design ? 'inside' : 'outside' );
$is_inline_slots = ( 'inline' === $calendar_style || ( 'flat' === $design && 'inside' === $slot_position ) );
?>
<div class="dgap-calendar disabled dgap-calendar-style-<?php echo esc_attr( $calendar_style ); ?>" data-slot-position="<?php echo esc_attr( $is_inline_slots ? 'inside' : 'outside' ); ?>">
	<div class="dgap-calendar-header">
		<button type="button" class="dgap-prev-month" type="button">&lsaquo;</button>
		<span class="dgap-month-label"></span>
		<button type="button" class="dgap-next-month" type="button">&rsaquo;</button>
	</div>

	<div class="dgap-calendar-weekdays">
		<span><?php echo esc_html__( 'Mon', 'digent-appointments' ); ?></span>
		<span><?php echo esc_html__( 'Tue', 'digent-appointments' ); ?></span>
		<span><?php echo esc_html__( 'Wed', 'digent-appointments' ); ?></span>
		<span><?php echo esc_html__( 'Thu', 'digent-appointments' ); ?></span>
		<span><?php echo esc_html__( 'Fri', 'digent-appointments' ); ?></span>
		<span><?php echo esc_html__( 'Sat', 'digent-appointments' ); ?></span>
		<span><?php echo esc_html__( 'Sun', 'digent-appointments' ); ?></span>
	</div>

	<div class="dgap-calendar-days">
		
	</div>
</div>
