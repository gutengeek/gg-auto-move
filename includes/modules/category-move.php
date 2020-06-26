<?php
add_action( 'manage_posts_extra_tablenav', 'gg_auto_move_add_move_category_form' );
function gg_auto_move_add_move_category_form( $which ) {
	if ( 'bottom' === $which ) {
		return;
	}

	global $typenow;

	if ( 'product' !== $typenow ) {
		return;
	}

	?>
    <div class="alignleft actions">
        <input type="text" name="gg_auto_move_pids" placeholder="<?php esc_attr_e( 'Type a comma-separated list of product IDs. Ex: 1, 2, 3', 'gg-auto-move' ); ?>" style="width: 380px;">
        <input type="submit" name="gg_auto_move_move" id="gg_auto_move_move" class="button" value="Move"></div>
	<?php
}

add_action( 'parse_query', 'gg_auto_move_move' );
function gg_auto_move_move( $wp ) {
	global $pagenow;

	if ( 'edit.php' !== $pagenow
	     || 'product' !== $wp->query_vars['post_type']
	     || ! isset( $_GET['gg_auto_move_move'] )
	     || ! $_GET['gg_auto_move_move']
	     || ! isset( $_GET['product_cat'] )
	     || ! sanitize_text_field( $_GET['product_cat'] )
	) {
		return;
	}

	$gg_auto_move_pids = isset( $_GET['gg_auto_move_pids'] ) ? sanitize_text_field( $_GET['gg_auto_move_pids'] ) : '';
	$selected_products = isset( $_GET['post'] ) ? gg_auto_move_clean( $_GET['post'] ) : [];

	if ( ! $gg_auto_move_pids && ! $selected_products ) {
		return;
	}

	$pids = [];

	if ( $gg_auto_move_pids ) {
		$pids = explode( ',', $gg_auto_move_pids );
		$pids = array_map( 'trim', $pids );
		$pids = array_map( 'absint', $pids );
	}

	if ( $selected_products ) {
		$pids = array_merge( $pids, $selected_products );
	}

	$cat_slug = sanitize_text_field( $_GET['product_cat'] );

	try {
		if ( ! $cat = term_exists( $cat_slug, 'product_cat' ) ) {
			return;
		}

		if ( ! function_exists( 'wc_get_product' ) ) {
			return;
		}

		foreach ( $pids as $pid ) {
			$product = wc_get_product( $pid );
			if ( ! $product ) {
				continue;
			}

			wp_set_post_terms( $product->get_id(), $cat['term_id'], 'product_cat' );
		}
	} catch ( Exception $e ) {

	}
}
