<?php
function gg_auto_move_auto_move_get_product_ids() {
	$options            = get_option( 'gg_auto_move_auto_move', [] );
	$allowed_categories = isset( $options['category_source'] ) && $options['category_source'] ? $options['category_source'] : [];
	$number_current     = isset( $options['number_of_products'] ) && $options['number_of_products'] ? $options['number_of_products'] : '-1';
	$orderby            = isset( $options['orderby'] ) && $options['orderby'] ? $options['orderby'] : 'date';
	$sort_order         = isset( $options['sort_order'] ) && $options['sort_order'] ? $options['sort_order'] : 'DESC';

	if ( $allowed_categories ) {
		$args = [
			'post_type'   => 'product',
			'post_status' => 'publish',
			'numberposts' => $number_current,
			'tax_query'   => [
				[
					'taxonomy' => 'product_cat',
					'field'    => 'slug',
					'terms'    => $allowed_categories,
				],
			],
			'orderby'     => $orderby,
			'order'       => $sort_order,
		];

		$products = get_posts( $args );

		if ( $products ) {
			return wp_list_pluck( $products, 'ID' );
		}
	}

	return [];
}

function gg_auto_move_create_cron_jobs() {
	wp_clear_scheduled_hook( 'gg_auto_move_corn' );
	wp_clear_scheduled_hook( 'gg_auto_move_update' );

	if ( ! wp_next_scheduled( 'gg_auto_move_update' ) ) {
		wp_schedule_event( time(), 'gg_auto_move_corn', 'gg_auto_move_update' );
	}
}

add_filter( 'cron_schedules', 'gg_auto_move_cron_schedules' );
function gg_auto_move_cron_schedules( $schedules ) {
	$options  = get_option( 'gg_auto_move_auto_move', [] );
	$interval = isset( $options['schedule'] ) ? $options['schedule'] : 0;

	$schedules['gg_auto_move_corn'] = [
		'display'  => __( 'GG Woo Auto Move Update Interval', 'gg-auto-move' ),
		'interval' => $interval,
	];

	return $schedules;
}

/**
 * Clean cron jobs when saving settings.
 *
 * @param $old_value
 * @param $value
 */
function gg_auto_move_clean_cron_jobs( $old_value, $value ) {
	$update_schedule = isset( $value['schedule'] ) ? $value['schedule'] : 0;
	$old_schedule    = isset( $old_value['schedule'] ) ? $old_value['schedule'] : 0;

	if ( $update_schedule !== $old_schedule ) {
		wp_clear_scheduled_hook( 'gg_auto_move_cron_auto_move' );
		if ( $update_schedule ) {
			add_filter( 'cron_schedules', 'gg_auto_move_cron_schedules' );

			if ( ! wp_next_scheduled( 'gg_auto_move_cron_auto_move' ) ) {
				wp_schedule_event( time(), 'gg_auto_move_corn', 'gg_auto_move_cron_auto_move' );
			}
		}
	}
}

add_action( 'update_option_gg_auto_move_auto_move', 'gg_auto_move_clean_cron_jobs', 10, 2 );

function gg_auto_move_cron_auto_move() {
	try {
		$options         = get_option( 'gg_auto_move_auto_move', [] );
		$category_target = isset( $options['category_target'] ) ? $options['category_target'] : '';

		if ( $category_target ) {
			if ( ! $cat = term_exists( $category_target, 'product_cat' ) ) {
				return;
			}

			if ( ! function_exists( 'wc_get_product' ) ) {
				return;
			}

			$ids = gg_auto_move_auto_move_get_product_ids();
			if ( $ids ) {
				gg_auto_move_write_log( sprintf( 'GG Woo HR: Move product ids: %1$s to category id: %2$s', implode( ', ', $ids ), $cat['term_id'] ) );

				foreach ( $ids as $pid ) {
					$product = wc_get_product( $pid );
					if ( ! $product ) {
						continue;
					}

					wp_set_post_terms( $product->get_id(), $cat['term_id'], 'product_cat' );
				}
			}
		}
	} catch ( Exception $e ) {

	}
}

add_action( 'gg_auto_move_cron_auto_move', 'gg_auto_move_cron_auto_move' );

if ( ! function_exists( 'gg_auto_move_write_log' ) ) {

	/**
	 * Write log.
	 *
	 * @param $log
	 */
	function gg_auto_move_write_log( $log ) {
		if ( true === WP_DEBUG ) {
			if ( is_array( $log ) || is_object( $log ) ) {
				error_log( print_r( $log, true ) );
			} else {
				error_log( $log );
			}
		}
	}
}
