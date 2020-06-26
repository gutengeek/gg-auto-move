<?php
add_action( 'admin_menu', 'gg_auto_move_auto_move_admin_menu' );
function gg_auto_move_auto_move_admin_menu() {
	add_submenu_page(
		'woocommerce',
		apply_filters( 'gg-auto-move-settings-page-title', esc_html__( 'Auto Move', 'gg-auto-move' ) ),
		apply_filters( 'gg-auto-move-settings-menu-title', esc_html__( 'Auto Move', 'gg-auto-move' ) ),
		'manage_options',
		'gg-auto-move-settings',
		'gg_auto_move_auto_move_page_options'
	);
}

function gg_auto_move_auto_move_page_options() {
	$options            = get_option( 'gg_auto_move_auto_move', [] );
	$schedules          = apply_filters( 'gg_auto_move_schedule_interval_options', [
			'0'                  => esc_html__( 'Never', 'gg-auto-move' ),
			WEEK_IN_SECONDS      => esc_html__( '1 Week', 'gg-auto-move' ),
			2 * DAY_IN_SECONDS   => esc_html__( '2 Days', 'gg-auto-move' ),
			DAY_IN_SECONDS       => esc_html__( '24 Hours', 'gg-auto-move' ),
			12 * HOUR_IN_SECONDS => esc_html__( '12 Hours', 'gg-auto-move' ),
			6 * HOUR_IN_SECONDS  => esc_html__( '6 Hours', 'gg-auto-move' ),
			HOUR_IN_SECONDS      => esc_html__( '1 Hour', 'gg-auto-move' ),
			60                   => esc_html__( '1 Minute', 'gg-auto-move' ),
		]
	);
	$schedule_saved     = isset( $options['schedule'] ) && $options['schedule'] ? $options['schedule'] : '0';
	$category_targeted  = isset( $options['category_target'] ) && $options['category_target'] ? $options['category_target'] : '';
	$category_sourced   = isset( $options['category_source'] ) && $options['category_source'] ? $options['category_source'] : [];
	$number_current     = isset( $options['number_of_products'] ) && $options['number_of_products'] ? $options['number_of_products'] : '-1';
	$orderby_current    = isset( $options['orderby'] ) && $options['orderby'] ? $options['orderby'] : 'date';
	$sort_order_current = isset( $options['sort_order'] ) && $options['sort_order'] ? $options['sort_order'] : 'DESC';
	?>
    <form method="post" action="" novalidate="novalidate">
		<?php wp_nonce_field( 'gg-auto-move-settings-form' ); ?>
        <table class="form-table" role="presentation">
            <tbody>
            <tr>
                <th scope="row"><label for="schedule"><?php esc_html_e( 'Refresh interval', 'gg-auto-move' ) ?></label></th>
                <td>
                    <select name="schedule" id="schedule">
						<?php foreach ( $schedules as $schedule_value => $schedule_name ) : ?>
                            <option value="<?php echo esc_attr( $schedule_value ); ?>" <?php selected( $schedule_value, $schedule_saved, true ); ?>><?php echo esc_html( $schedule_name ); ?></option>
						<?php endforeach; ?>
                    </select>
                </td>
            </tr>

            <tr>
                <th scope="row"><label for="category_source"><?php esc_html_e( 'Source', 'gg-auto-move' ) ?></label></th>
                <td>
					<?php
					$categories = get_terms( [
						'taxonomy'   => 'product_cat',
						'hide_empty' => false,
					] );

					?>
                    <select name="category_source[]" id="category_source" multiple="multiple" data-placeholder="<?php esc_html_e( 'Select categories', 'gg-auto-move' );
					?>">
						<?php foreach ( $categories as $category ) : ?>
                            <option value="<?php echo esc_attr( $category->slug ); ?>" <?php selected( true,
								in_array( $category->slug, $category_sourced ) ); ?>><?php echo esc_html( $category->name ); ?>
                                (<?php echo absint( $category->count ); ?>)
                            </option>
						<?php endforeach; ?>
                    </select>
                </td>
            </tr>

            <tr>
                <th scope="row"><label for="category_target"><?php esc_html_e( 'Target', 'gg-auto-move' ) ?></label></th>
                <td>
					<?php
					$product_category_args = [
						'pad_counts'         => 1,
						'show_count'         => 1,
						'hierarchical'       => 1,
						'hide_empty'         => false,
						'show_uncategorized' => 1,
						'orderby'            => 'name',
						'selected'           => $category_targeted,
						'show_option_none'   => __( 'Select a category', 'gg-auto-move' ),
						'option_none_value'  => '',
						'value_field'        => 'slug',
						'taxonomy'           => 'product_cat',
						'name'               => 'category_target',
						'class'              => 'dropdown_product_cat',
					];

					if ( 'order' === $product_category_args['orderby'] ) {
						$product_category_args['orderby']  = 'meta_value_num';
						$product_category_args['meta_key'] = 'order'; // phpcs:ignore
					}
					wp_dropdown_categories( $product_category_args );
					?>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="number_of_products"><?php esc_html_e( 'Number of Products', 'gg-auto-move' ); ?></label></th>
                <td>
                    <input type="number" name="number_of_products" min="1" step="1" value="<?php echo esc_attr( $number_current ); ?>">
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="orderby"><?php esc_html_e( 'Order by', 'gg-auto-move' ) ?></label></th>
                <td>
					<?php
					$orderbys = [
						'date'  => esc_html__( 'Date', 'gg-auto-move' ),
						'ID'    => esc_html__( 'ID', 'gg-auto-move' ),
						'title' => esc_html__( 'Product Name', 'gg-auto-move' ),
					];
					?>
                    <select name="orderby" id="orderby">
						<?php foreach ( $orderbys as $orderby_key => $orderby_value ) : ?>
                            <option value="<?php echo esc_attr( $orderby_key ); ?>" <?php selected( $orderby_key, $orderby_current ); ?>>
								<?php echo esc_html( $orderby_value ); ?>
                            </option>
						<?php endforeach; ?>
                    </select>
                </td>
            </tr>

            <tr>
                <th scope="row"><label for="sort_order"><?php esc_html_e( 'Sort order', 'gg-auto-move' ) ?></label></th>
                <td>
					<?php
					$sort_orders = [
						'desc' => esc_html__( 'DESC', 'gg-auto-move' ),
						'asc'  => esc_html__( 'ASC', 'gg-auto-move' ),
					];
					?>
                    <select name="sort_order" id="sort_order">
						<?php foreach ( $sort_orders as $sort_order_key => $sort_order_value ) : ?>
                            <option value="<?php echo esc_attr( $sort_order_key ); ?>" <?php selected( $sort_order_key, $sort_order_current ); ?>>
								<?php echo esc_html( $sort_order_value ); ?>
                            </option>
						<?php endforeach; ?>
                    </select>
                </td>
            </tr>
            </tbody>
        </table>
        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'gg-auto-move' ); ?>"></p></form>
    <script>
        jQuery( 'select' ).select2();
    </script>
	<?php
}

function gg_auto_move_auto_move_save_settings() {
	if ( ! isset( $_GET['page'] ) || 'gg-auto-move-settings' !== sanitize_text_field( $_GET['page'] ) ) {
		return;
	}

	$nonce = ! empty( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

	if ( ! $nonce ) {
		return;
	}

	if ( ! wp_verify_nonce( $nonce, 'gg-auto-move-settings-form' ) ) {
		wp_die( __( 'Permission denied.', 'wpopal' ) );
	}

	update_option( 'gg_auto_move_auto_move', gg_auto_move_clean( $_POST ) );
}

add_action( 'admin_init', 'gg_auto_move_auto_move_save_settings' );
