<?php
add_action( 'wp_dashboard_setup', 'gg_auto_move_wp_dashboard_setup' );

function gg_auto_move_wp_dashboard_setup() {
	if ( current_user_can( 'view_woocommerce_reports' ) ) {
		wp_add_dashboard_widget( 'woocommerce_count_payment_gateways', __( 'WooCommerce Payment Gateways', 'gg-auto-move' ), 'gg_auto_move_count_payment_gateways_widget' );
	}
}

function gg_auto_move_count_payment_gateways_widget() {
	?>
    <div class="gg_auto_move-tabs--horizontal">
        <div class="gg_auto_move-tabs__wrap">
            <div class="gg_auto_move-tabs__nav">
                <ul class="gg_auto_move-ul gg_auto_move-tabs__nav-list">
                    <li class="gg_auto_move-tabs__item gg_auto_move-active">
                        <div class="gg_auto_move-tabs__title">
                            <span class="gg_auto_move-tabs__title-text"><?php esc_html_e( '7 days', 'gg-auto-move' ); ?></span>
                        </div>
                    </li>
                    <li class="gg_auto_move-tabs__item ">
                        <div class="gg_auto_move-tabs__title">
                            <span class="gg_auto_move-tabs__title-text"><?php esc_html_e( 'All time', 'gg-auto-move' ); ?></span>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="gg_auto_move-tabs__body">
                <div class="gg_auto_move-tabs__content gg_auto_move-active">
					<?php
					$days_7 = gg_auto_move_get_static_payment_gateways( 7 );
					?>
                    <table class="form-table" role="presentation">
                        <tbody>
						<?php foreach ( $days_7 as $day_7_payment => $day_7_count ) : ?>
						<?php
							$payment_gateways = WC()->payment_gateways;
							if ( ! isset($payment_gateways->payment_gateways()[$day_7_payment] ) ) {
								continue;
							}

							$payment = $payment_gateways->payment_gateways()[$day_7_payment];
							?>
                            <tr>
                                <th scope="row"><label for="schedule"><?php echo esc_html( $payment->title ); ?></label></th>
                                <td>
                                    <?php echo $day_7_count; ?>
                                </td>
                            </tr>
						<?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="gg_auto_move-tabs__content">
	                <?php
	                $all_time = gg_auto_move_get_static_payment_gateways();
	                ?>
                    <table class="form-table" role="presentation">
                        <tbody>
		                <?php foreach ( $all_time as $all_time_payment => $all_time_count ) : ?>
			                <?php
			                $payment_gateways = WC()->payment_gateways;
			                if ( ! isset($payment_gateways->payment_gateways()[$all_time_payment] ) ) {
				                continue;
			                }

			                $payment = $payment_gateways->payment_gateways()[$all_time_payment];
			                ?>
                            <tr>
                                <th scope="row"><label for="schedule"><?php echo esc_html( $payment->title ); ?></label></th>
                                <td>
					                <?php echo $all_time_count; ?>
                                </td>
                            </tr>
		                <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
        jQuery( '.gg_auto_move-tabs__title' ).on( 'click', function ( event ) {
            var $gg_auto_moveTab = jQuery( this ).parent();
            var gg_auto_moveIndex = $gg_auto_moveTab.index();

            if ( $gg_auto_moveTab.hasClass( 'gg_auto_move-active' ) ) {
                return;
            }

            $gg_auto_moveTab.closest( '.gg_auto_move-tabs__nav' ).find( '.gg_auto_move-active' ).removeClass( 'gg_auto_move-active' );
            $gg_auto_moveTab.addClass( 'gg_auto_move-active' );
            $gg_auto_moveTab.closest( '.gg_auto_move-tabs__wrap' ).find( '.gg_auto_move-tabs__content.gg_auto_move-active' ).hide().removeClass( 'gg_auto_move-active' );
            $gg_auto_moveTab.closest( '.gg_auto_move-tabs__wrap' ).find( '.gg_auto_move-tabs__content' ).eq( gg_auto_moveIndex ).fadeIn( 200, function () {
                jQuery( this ).addClass( 'gg_auto_move-active' );
            } );
        } );
    </script>
    <style>
        .gg_auto_move-tabs__wrap {
            display: inline-flex;
            width: 100%;
        }

        .gg_auto_move-tabs__wrap .gg_auto_move-tabs__nav .gg_auto_move-tabs__item {
            display: block;
            text-align: center;
            position: relative;
        }

        .gg_auto_move-tabs__wrap .gg_auto_move-tabs__nav .gg_auto_move-tabs__item.gg_auto_move-active .gg_auto_move-tabs__title {
            background-color: #b05d93;
            color: #fff;
        }

        .gg_auto_move-tabs__wrap .gg_auto_move-tabs__nav .gg_auto_move-tabs__item .gg_auto_move-tabs__title {
            cursor: pointer;
            color: #616f80;
            padding: 12px 15px;
            border-radius: 6%;
            text-decoration: none;
            font-weight: bold;
            background-color: #e8e9eb;
        }

        .gg_auto_move-tabs__wrap .gg_auto_move-tabs__nav .gg_auto_move-tabs__item:hover .gg_auto_move-action-tab-remove {
            display: block;
        }

        .gg_auto_move-tabs__wrap .gg_auto_move-tabs__nav .gg_auto_move-tabs__item:first-child {
            margin-left: 0 !important;
        }

        .gg_auto_move-tabs__wrap .gg_auto_move-tabs__nav .gg_auto_move-tabs__item:last-child {
            margin-right: 0 !important;
        }

        .gg_auto_move-tabs__content {
            visibility: hidden;
            opacity: 0;
            position: absolute;
        }

        .gg_auto_move-tabs__content.gg_auto_move-active {
            position: relative;
            opacity: 1;
            visibility: visible;
            transition: opacity 400ms;
        }

        .gg_auto_move-tabs__nav-list {
            padding: 0;
            margin: 0;
            list-style: none;
            display: inline-flex;
        }

        .gg_auto_move-tabs--horizontal .gg_auto_move-tabs__wrap {
            flex-wrap: wrap;
        }

        .gg_auto_move-tabs--horizontal .gg_auto_move-tabs__item {
            margin-left: 5px;
            margin-right: 5px;
        }

        .gg_auto_move-tabs--horizontal .gg_auto_move-tabs__item:first-child {
            margin-left: 0 !important;
        }

        .gg_auto_move-tabs--horizontal .gg_auto_move-tabs__item:last-child {
            margin-right: 0 !important;
        }

        .gg_auto_move-tabs--horizontal .gg_auto_move-tabs__title {
            justify-content: center;
        }

        .gg_auto_move-tabs--horizontal .gg_auto_move-add-new-tab {
            margin-left: 5px;
        }

        .gg_auto_move-tabs--horizontal .gg_auto_move-tabs__nav {
            width: 100%;
        }

        .gg_auto_move-tabs--horizontal .gg_auto_move-tabs__body {
            width: 100%;
        }
    </style>
	<?php
}

function gg_auto_move_query_payment_gateways( $gateway, $previous_days = '' ) {
	global $wpdb;
	if ( $previous_days ) {
		$previous_days = absint( $previous_days );
		$time          = " AND p.post_date >= (DATE(NOW()) - INTERVAL {$previous_days} DAY)";
	}

	$count = $wpdb->get_var( "SELECT count(m.meta_id) FROM {$wpdb->prefix}postmeta as m JOIN {$wpdb->prefix}posts as p WHERE p.ID = m.post_id AND p.post_type = 'shop_order' AND meta_key = '_payment_method' AND meta_value = '{$gateway}'{$time}" );

	return $count;
}

function gg_auto_move_get_static_payment_gateways( $previous_days = '' ) {
	$gateways = WC()->payment_gateways->get_payment_gateway_ids();
	$static   = [];
	if ( $gateways ) {
		foreach ( $gateways as $gateway ) {
			$static[ $gateway ] = gg_auto_move_query_payment_gateways( $gateway, $previous_days );
		}
	}

	return $static;
}
