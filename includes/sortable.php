<?php
namespace GS_TECA;

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists('GS_Teca_Sortable') ) {
	class GS_Teca_Sortable {

		var $location = ''; // path to css/js
		var $posttype = 'tribe_events';
		var $title = '';
		var $ppp = '-1'; // postsperpage

		public function __construct( $posttype, $title, $ppp = -1 ) {

			$this->location = get_stylesheet_directory_uri() . '/_inc/functions/'; // path to css/js
			$this->posttype = $posttype;
			$this->title = $title;
			$this->ppp = $ppp;

			add_filter( 'events_orderby', array( $this, 'gs_teca_order_events' ) );
			add_action( 'admin_menu' , array( $this, 'gs_teca_enable_sort' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'gs_teca_sort_scripts' ) );
			add_action( 'wp_ajax_update_event_order', array( $this, 'update_event_order' ) );
			add_action( 'wp_ajax_update_event_visibility_order', array( $this, 'update_event_visibility_order' ) );
			add_filter( 'plugins_loaded', array($this, 'alter_terms_table'), 100 );
			add_filter( 'get_terms_orderby', array($this, 'get_terms_orderby'), 1, 2 );
			add_filter( 'terms_clauses', array($this, 'terms_clauses'), 10, 3 );
			add_action( 'wp_ajax_update_gsteca_taxonomy_order', array($this, 'update_gsteca_taxonomy_order') );
		}

		/**
		 * Alter the query on front and backend to order posts as desired.
		 */
		public function gs_teca_order_events( $orderby ) {
			global $wpdb;
			global $wp_query;
			
			if ( ! isset($wp_query) || ! is_main_query() ) return $orderby;
		
			if ( is_post_type_archive( array($this->posttype)) ) {
				$orderby = "{$wpdb->posts}.menu_order, {$wpdb->posts}.post_date DESC";
			}

			return($orderby);
		}

		/**
		 * Add Sort menu
		 */
		public function 	gs_teca_enable_sort() {
			add_submenu_page('gs-the-events-calendar-addon', 'Sort Events', 'Sort Order', 'edit_posts', 'sort_' . $this->posttype, array( $this, 'dhf_sort'), 4);
		}

		public function dhf_sort() {

			$object_type = isset( $_GET['object_type'] ) ? $_GET['object_type'] : 'gs_teca';

			?>
			<div class="gs-plugins--sort-page">
				<div class="gs-plugins--sort-links">
					<a class="<?php echo $object_type === 'gs_teca' ? 'gs-sort-active' : ''; ?>" href="<?php echo esc_url( $this->get_url_with_object_type( 'gs_teca' ) ); ?>"><?php echo esc_html( 'Events', 'the-events-calendar-addon' ); ?></a>
					<a class="<?php echo $object_type === 'gs_teca_fields_visibility' ? 'gs-sort-active' : ''; ?>" href="<?php echo esc_url( $this->get_url_with_object_type( 'gs_teca_fields_visibility' ) ); ?>"><?php echo esc_html( 'Single Post Info', 'the-events-calendar-addon' ); ?></a>
					<a class="<?php echo $object_type === 'gs_teca_cat_order' ? 'gs-sort-active' : ''; ?>" href="<?php echo esc_url( $this->get_url_with_object_type( 'gs_teca_cat_order' ) ); ?>"><?php echo esc_html( 'Categories', 'the-events-calendar-addon' ); ?></a>
					<a class="<?php echo $object_type === 'gs_teca_tag_order' ? 'gs-sort-active' : ''; ?>" href="<?php echo esc_url( $this->get_url_with_object_type( 'gs_teca_tag_order' ) ); ?>"><?php echo esc_html( 'Tags', 'the-events-calendar-addon' ); ?></a>
				</div>

				<div class="gs-plugins--sort-content">				
					<?php if ( $object_type === 'gs_teca' ) : ?>
						<?php $this->sort_events(); ?>
					<?php elseif( $object_type === 'gs_teca_fields_visibility') : ?>
						<?php $this->sort_single_event_visibility() ; ?>
					<?php elseif( $object_type === 'gs_teca_cat_order') : ?>
						<?php $this->sort_teca_categories() ; ?>
					<?php else : ?>
						<?php $this->sort_teca_tags() ; ?>
					<?php endif; ?>
				</div>

			</div>

			<?php
		}

		public function print_pro_message() {
			if (! is_pro_active_and_valid()) : ?>

				<div class="gs-teca-disable--term-pages">
					<div class="gs-teca-disable--term-inner">
						<a class="gs-teca-disable--term-message" href="https://www.gsplugins.com/product/wordpress-the-events-calendar-addon/#pricing">Upgrade to PRO</a>
					</div>
				</div>

			<?php endif;
		}

		public function sort_events() {
				
			$sortable = new \WP_Query('post_type=' . $this->posttype . '&posts_per_page=' . $this->ppp . '&orderby=menu_order&order=ASC');

			$this->print_pro_message();			
				
			 ?>

				<div class="gs-teca--sort-wrap <?php echo is_pro_active_and_valid() ? 'sort--wrap-active' : ''; ?>">

					<div style="display: flex; width: 100%; max-width: 1280px; gap: 40px; flex-wrap: wrap;">

						<div class="gsteca-sort--left-area" style="flex: 1 0 auto;">

							<h3><?php esc_html_e('Step 1: Drag & Drop to rearrange events', 'the-events-calendar-addon'); ?><img src="<?php esc_url(GS_TECA_PLUGIN_URI . 'assets/img/loader.svg'); ?>" id="loading-animation" /></h3>

							<?php if ($sortable->have_posts()) : ?>

								<ul id="sortable-list">
									<?php while ($sortable->have_posts()) :

										$event_id = 0;

										if ( isset( $event['event_id'] ) && $event['event_id'] > 0 ) {
											$event_id = (int) $event['event_id'];
										}

										if ( ! $event_id ) {
											$post = get_post();
											if ( $post instanceof \WP_Post ) {
												$event_id = $post->ID;
											}
										}

										$sortable->the_post();
										$term_obj_list = get_the_terms($event_id, 'tribe_events_cat');
										$terms_string = '';

										if (is_array($term_obj_list) || is_object($term_obj_list)) {
											$terms_string = join('</span><span>', wp_list_pluck($term_obj_list, 'name'));
										}

										if (!empty($terms_string)) $terms_string = '<span>' . $terms_string . '</span>';

									?>

										<li id="<?php the_id(); ?>">
											<div class="sortable-content sortable-icon"><i class="fas fa-arrows-alt" aria-hidden="true"></i></div>
											<div class="sortable-content sortable-thumbnail"><span><?php the_post_thumbnail(); ?></span></div>
											<div class="sortable-content sortable-title"><?php the_title(); ?></div>
											<div class="sortable-content sortable-group"><?php echo wp_kses_post($terms_string); ?></div>
										</li>

									<?php endwhile; ?>
								</ul>

							<?php else : ?>

								<div class="notice notice-warning" style="margin-top: 30px;">
									<h3><?php echo esc_html(_e('No Events Found!', 'the-events-calendar-addon')); ?></h3>
									<p style="font-size: 14px;"><?php echo esc_html(_e('We didn\'t find any event.</br>Please add some events to sort them.', 'the-events-calendar-addon')); ?></p>
									<a href="<?php echo esc_url(admin_url('post-new.php?post_type=tribe_events')); ?>" style="margin-top: 10px; margin-bottom: 20px;" class="button button-primary button-large"><?php echo esc_html(_e('Add Event', 'the-events-calendar-addon')); ?></a>
								</div>

							<?php endif; ?>

						</div>

						<div class="gsteca-sort--right-area">
							
							<h3><?php esc_html_e('Step 2: Query Settings for The Events Calendar Addon', 'the-events-calendar-addon'); ?></h3>

							<div style="background: #fff; width:400px; border-radius: 6px; padding: 30px; box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.12); font-size: 1.3em; line-height: 1.6; margin-top: 30px">
								
								<ol style="list-style: numeric; padding-left: 20px; margin: 0">
									<li>Create or Edit a Shortcode From <strong>The Events Calendar Addon > Shortcode</strong>.</li>
									<li>Then proceed to the 3rd tab labeled <strong>Query Settings</strong>.</li>
									<li>Set <strong>Order by</strong> to <strong>Custom Order</strong>.</li>
									<li>Set <strong>Order</strong> to <strong>ASC</strong>.</li>
								</ol>
			
								<ul style="list-style: circle; padding-left: 20px; margin-top: 20px">
									<li>Follow <a href="https://docs.gsplugins.com/the-events-calendar-addon/manage-the-events-calendar-addon/sort-order/" target="_blank">Documentation</a> to learn more.</li>
									<li><a href="https://www.gsplugins.com/contact/" target="_blank">Contact us</a> for support.</li>
								</ul>

							</div>

						</div>

					</div>

				</div><!-- #wrap -->
		
			<?php
		}

		public function sort_single_event_visibility() {

			$fields_visibility = plugin()->builder->get_sorted_fields_visibility_settings();

			$this->print_pro_message();

			$strings = plugin()->builder->get_translation_strings();

			?>

				<div class="gs-teca--sort-wrap <?php echo is_pro_active_and_valid() ? 'sort--wrap-active' : ''; ?>">
					<div style="display: flex; width: 100%; max-width: 1280px; gap: 40px; flex-wrap: wrap;">
						<div class="gsteca-sort--left-area" style="flex: 1 0 auto; width: 570px;">

							<h3><?php esc_html_e( 'Single Events Sorting & Visibility', 'the-events-calendar-addon' ); ?><img src="<?php esc_url(GS_TECA_PLUGIN_URI . 'assets/img/loader.svg'); ?>" id="loading-animation" /></h3>

							<?php if ( ! empty( $fields_visibility ) ) : ?>
								<div class="table table-striped table-visibility">
									<div class="table-head">
										<div class="table-row">
											
											<div>Field</div>

											<div><div class="visibility-device">
												<span><?php echo esc_html( $strings['desktop'] ); ?></span>
												<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="4" width="18" height="12" stroke="currentColor" stroke-width="2" /><rect x="9" y="18" width="6" height="2" stroke="currentColor" stroke-width="2" /><line x1="9" y1="16" x2="15" y2="16" stroke="currentColor" stroke-width="2" /></svg>
											</div></div>

											<div><div class="visibility-device">
												<span><?php echo esc_html( $strings['tablet'] ); ?></span>
												<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="6" y="3" width="12" height="18" stroke="currentColor" stroke-width="2" /><circle cx="12" cy="19" r="1" fill="currentColor" /></svg>
											</div></div>

											<div><div class="visibility-device">
												<span><?php echo esc_html( $strings['mobile_landscape'] ); ?></span>
												<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="8" width="18" height="8" stroke="currentColor" stroke-width="2" /><circle cx="5" cy="12" r="1" fill="currentColor" /></svg>
											</div></div>

											<div><div class="visibility-device">
												<span><?php echo esc_html( $strings['mobile'] ); ?></span>
												<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="8" y="2" width="8" height="20" stroke="currentColor" stroke-width="2" /><circle cx="12" cy="18" r="1" fill="currentColor" /></svg>
											</div></div>

										</div>
									</div>
									<div class="ui-sortable--table">
										<?php foreach ( $fields_visibility as $field => $visibility ) : ?>
											<div class="table-row" id="<?php echo esc_attr( $field ); ?>" style="max-width: 600px;">
												<div>
													<div class="sortable-content sortable-icon-title">
														<i class="fas fa-arrows-alt" aria-hidden="true"></i>
														<span><?php echo esc_html( $strings[ $visibility['translation_key'] ] ); ?></span>
													</div>
												</div>
												<div>
													<?php printf( '<label class="gs-checkbox-ui" for="%1$s"><input type="checkbox" id="%1$s" name="%1$s" %2$s /></label>', esc_attr( $field . '_desktop' ), checked( wp_validate_boolean( $visibility['desktop'] ), true, false ) ); ?>
												</div>
												<div>
													<?php printf( '<label class="gs-checkbox-ui" for="%1$s"><input type="checkbox" id="%1$s" name="%1$s" %2$s /></label>', esc_attr( $field . '_tablet' ), checked( wp_validate_boolean( $visibility['tablet'] ), true, false ) ); ?>
												</div>
												<div>
													<?php printf( '<label class="gs-checkbox-ui" for="%1$s"><input type="checkbox" id="%1$s" name="%1$s" %2$s /></label>', esc_attr( $field . '_mobile_landscape' ), checked( wp_validate_boolean( $visibility['mobile_landscape'] ), true, false ) ); ?>
												</div>
												<div>
													<?php printf( '<label class="gs-checkbox-ui" for="%1$s"><input type="checkbox" id="%1$s" name="%1$s" %2$s /></label>', esc_attr( $field . '_mobile' ), checked( wp_validate_boolean( $visibility['mobile'] ), true, false ) ); ?>
												</div>
											</div>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			
			<?php
		}

	

		public function get_terms_orderby($orderby, $args) {

			// if (empty($args['taxonomy'])) return $orderby;

			// if (is_pro_active_and_valid() && in_array('tribe_events_cat', $args['taxonomy'])) {
			// 	if (isset($args['orderby']) && $args['orderby'] == "term_order" && $orderby != "term_order") return "t.term_order";
			// }

			return $orderby;
		}

		public function alter_terms_table() {

			if (!is_pro_active_and_valid()) return;

			if (get_site_option('gsteca_terms_table_altered', false) !== false) return;

			global $wpdb;

			//check if the menu_order column exists;
			$query = "SHOW COLUMNS FROM $wpdb->terms LIKE 'term_order'";
			$result = $wpdb->query($query);

			if ($result == 0) {
				$query = "ALTER TABLE $wpdb->terms ADD `term_order` INT( 4 ) NULL DEFAULT '0'";
				$result = $wpdb->query($query);

				update_site_option('gsteca_terms_table_altered', true);
			}
		}

		public function update_gsteca_taxonomy_order() {

			// if (!is_pro_active_and_valid()) wp_send_json_error();

			if (empty($_POST['_nonce']) || !wp_verify_nonce($_POST['_nonce'], '_gsteca_update_order_')) {
				wp_send_json_error(__('Unauthorised Request', 'the-events-calendar-addon'), 401);
			}

			global $wpdb;

			$order = explode(',', sanitize_text_field($_POST['order']));
			$counter = 0;
			$taxonomy = sanitize_text_field($_POST['taxonomy'] ?? '');
			
			foreach ($order as $term_id) {
				 $wpdb->update(
					$wpdb->term_taxonomy,
					[ 'order' => $counter ],
					[
						'term_id'  => (int) $term_id,
						'taxonomy' => $taxonomy
					],
					[ '%d' ],
					[ '%d', '%s' ]
				);
				$counter++;
			}

			return wp_send_json_success();
		}

		public function terms_clauses($clauses, $taxonomies, $args) {

			if (empty($args['taxonomy'])) return $clauses;

			$target_taxonomies = ['tribe_events_cat', 'post_tag'];

			if (!array_intersect($target_taxonomies, (array) $args['taxonomy'])) {
				return $clauses;
			}

			if (isset($args['orderby']) && $args['orderby'] === 'term_order') {
				$clauses['orderby'] = 'ORDER BY tt.order';
			}

			return $clauses;
		}

		public function get_url_with_object_type( $object = 'gs_teca' ) {
			return add_query_arg( 'object_type', $object, $this->get_full_url() );
		}

		public function get_full_url() {
			// Get the protocol
			$protocol = ( ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443 ) ? 'https://' : 'http://';

			// Get the host
			$host = $_SERVER['HTTP_HOST'];

			// Get the request URI
			$uri = $_SERVER['REQUEST_URI'];

			// Combine them to get the full URL
			$full_url = $protocol . $host . $uri;

			return $full_url;
		}

		/**
		 * Add JS and CSS to admin
		 */
		public function gs_teca_sort_scripts( $hook ) {
			if ( $hook != 'events_page_sort_tribe_events' ) return;

			if ( empty( $_GET['object_type'] ) || $_GET['object_type'] == 'gs_teca' ) {
				$action = 'update_event_order';
			}elseif ( empty( $_GET['object_type'] ) || $_GET['object_type'] == 'gs_teca_fields_visibility' ) {
				$action = 'update_event_visibility_order';
			}else {
				$action = 'update_gsteca_taxonomy_order';
			}
			
			plugin()->scripts->wp_enqueue_style( 'gs-teca-sort' );
			plugin()->scripts->wp_enqueue_script( 'gs-teca-sort' );

			
			$data = [
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'  => wp_create_nonce( '_gsteca_update_order_' ),
				'action' => $action,
				'is_pro_active' => is_pro_active()
			];

			wp_localize_script( 'gs-teca-sort', '_gsteca_sort_data', $data );
			

		}


		public function update_event_order() {

			check_ajax_referer( '_gsteca_update_order_', '_nonce' );

			global $wpdb;
			$order   = explode( ',', $_POST['order'] );
			$counter = 0;

			foreach ( $order as $post_id ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->update( $wpdb->posts, array( 'menu_order' => $counter ), array( 'ID' => $post_id ) );
				++$counter;
			}

			return true;
		}
		
		public function update_event_visibility_order() {

			check_ajax_referer( '_gsteca_update_order_' , '_wpnonce' );

			if ( isset( $_POST['data'] ) && is_array( $_POST['data'] ) ) {

				$sanitized_data = array();

				foreach ( $_POST['data'] as $item ) {

					if ( ! is_array( $item ) ) continue;

					$sanitized_item = array();

					// sanitize the field key (text)
					$field = sanitize_key( $item['field'] );

					// sanitize boolean-like values
					$flags = array( 'desktop', 'tablet', 'mobile_landscape', 'mobile' );
					foreach ( $flags as $flag ) {
						$sanitized_item[ $flag ] = wp_validate_boolean( isset( $item[ $flag ] ) ? $item[ $flag ] : false );
					}
					$sanitized_data[ $field ] = $sanitized_item;
				}

				update_option( 'gs_teca_visibility_order', $sanitized_data );
			}

			wp_send_json_success();

		}

		public function sort_teca_categories() {

			$terms = get_terms( array(
				'taxonomy' => 'tribe_events_cat',
				'hide_empty' => false
			) );
			$this->print_pro_message();
			?>

			<h2><?php esc_html_e('Custom order for Categories', 'the-events-calendar-addon'); ?><img src="<?php bloginfo('url'); ?>/wp-admin/images/loading.gif" id="loading-animation" /></h2>

			<div class="gs-teca--sort-wrap <?php echo is_pro_active_and_valid() ? 'sort--wrap-active' : ''; ?>">

				<div class="gs-teca--sort-area">

					<?php if ( ! empty($terms) ) : ?>
			
						<ul id="sortable-list" data-taxonomy="tribe_events_cat">
							<?php foreach ( $terms as $term ) :
								$name  = is_object($term) ? ($term->name ?? '') : ($term['name'] ?? '');
								$count = is_object($term) ? ($term->count ?? 0) : ($term['count'] ?? 0);
								$id = is_object($term) ? ($term->term_id ?? 0) : ($term['term_id'] ?? 0);
								?>
								
								<li id="<?php echo esc_attr( $id ); ?>">
									<div class="sortable-content sortable-icon"><svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="28" height="28" viewBox="0 0 28 28"><path d="M28 14c0 0.266-0.109 0.516-0.297 0.703l-4 4c-0.187 0.187-0.438 0.297-0.703 0.297-0.547 0-1-0.453-1-1v-2h-6v6h2c0.547 0 1 0.453 1 1 0 0.266-0.109 0.516-0.297 0.703l-4 4c-0.187 0.187-0.438 0.297-0.703 0.297s-0.516-0.109-0.703-0.297l-4-4c-0.187-0.187-0.297-0.438-0.297-0.703 0-0.547 0.453-1 1-1h2v-6h-6v2c0 0.547-0.453 1-1 1-0.266 0-0.516-0.109-0.703-0.297l-4-4c-0.187-0.187-0.297-0.438-0.297-0.703s0.109-0.516 0.297-0.703l4-4c0.187-0.187 0.438-0.297 0.703-0.297 0.547 0 1 0.453 1 1v2h6v-6h-2c-0.547 0-1-0.453-1-1 0-0.266 0.109-0.516 0.297-0.703l4-4c0.187-0.187 0.438-0.297 0.703-0.297s0.516 0.109 0.703 0.297l4 4c0.187 0.187 0.297 0.438 0.297 0.703 0 0.547-0.453 1-1 1h-2v6h6v-2c0-0.547 0.453-1 1-1 0.266 0 0.516 0.109 0.703 0.297l4 4c0.187 0.187 0.297 0.438 0.297 0.703z"/></svg></div>
									<div class="sortable-content sortable-title"><?php esc_html_e( $name ); ?></div>
									<div class="sortable-content sortable-group"><?php echo '<span>' . esc_html($count) . ' Events</span>'; ?></div>
								</li>
					
							<?php endforeach; ?>
						</ul>
					
					<?php else: ?>
						
						<div class="notice notice-warning">
							<h3><?php _e( 'No Category Found!', 'the-events-calendar-addon' ); ?></h3>
							<p><?php _e( 'We didn\'t find any Category.</br>Please add some categories to sort them.', 'the-events-calendar-addon' ); ?></p>
							<a href="<?php echo admin_url('edit-tags.php?taxonomy=tribe_events_cat&post_type=tribe_events'); ?>" class="button button-primary button-large"><?php _e( 'Add Category', 'the-events-calendar-addon' ); ?></a>
						</div>
		
					<?php endif; ?>
		
				</div>

				<div class="gs-teca--docs-area">
					<h3><?php esc_html_e('Query Settings for Categories', 'the-events-calendar-addon'); ?></h3>

					<div class="gs-teca--docs-area-content">
						
						<ol>
							<li>Create or Edit a Shortcode From <strong>The Events Calendar Addon > Shortcode</strong>.</li>
							<li>Then proceed to the 3rd tab labeled <strong>Query Settings</strong>.</li>
							<li>Set <strong>Category Order by</strong> to <strong>Custom Order</strong>.</li>
							<li>Set <strong>Category Order</strong> to <strong>ASC</strong>.</li>
						</ol>

						<ul>
							<li>Follow <a href="https://docs.gsplugins.com/gs-portfolio/manage-the-portfolios/sort-order/#reordering-groups-categories" target="_blank">Documentation</a> to learn more.</li>
							<li><a href="https://www.gsplugins.com/contact/" target="_blank">Contact us</a> for support.</li>
						</ul>

					</div>
				</div>

			</div><!-- #wrap -->

			<?php
		}

		public function sort_teca_tags() {

			$terms = get_terms( array(
				'taxonomy' => 'post_tag',
				'hide_empty' => false
			) );
			$this->print_pro_message();
			?>

			<h2><?php esc_html_e('Custom order for Tags', 'the-events-calendar-addon'); ?><img src="<?php bloginfo('url'); ?>/wp-admin/images/loading.gif" id="loading-animation" /></h2>

			<div class="gs-teca--sort-wrap <?php echo is_pro_active_and_valid() ? 'sort--wrap-active' : ''; ?>">

				<div class="gs-teca--sort-area">

					<?php if ( ! empty($terms) ) : ?>
			
						<ul id="sortable-list" data-taxonomy="post_tag">
							<?php foreach ( $terms as $term ) :
								$name  = is_object($term) ? ($term->name ?? '') : ($term['name'] ?? '');
								$count = is_object($term) ? ($term->count ?? 0) : ($term['count'] ?? 0);
								$id = is_object($term) ? ($term->term_id ?? 0) : ($term['term_id'] ?? 0);
								?>
								
								<li id="<?php echo esc_attr( $id ); ?>">
									<div class="sortable-content sortable-icon"><svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="28" height="28" viewBox="0 0 28 28"><path d="M28 14c0 0.266-0.109 0.516-0.297 0.703l-4 4c-0.187 0.187-0.438 0.297-0.703 0.297-0.547 0-1-0.453-1-1v-2h-6v6h2c0.547 0 1 0.453 1 1 0 0.266-0.109 0.516-0.297 0.703l-4 4c-0.187 0.187-0.438 0.297-0.703 0.297s-0.516-0.109-0.703-0.297l-4-4c-0.187-0.187-0.297-0.438-0.297-0.703 0-0.547 0.453-1 1-1h2v-6h-6v2c0 0.547-0.453 1-1 1-0.266 0-0.516-0.109-0.703-0.297l-4-4c-0.187-0.187-0.297-0.438-0.297-0.703s0.109-0.516 0.297-0.703l4-4c0.187-0.187 0.438-0.297 0.703-0.297 0.547 0 1 0.453 1 1v2h6v-6h-2c-0.547 0-1-0.453-1-1 0-0.266 0.109-0.516 0.297-0.703l4-4c0.187-0.187 0.438-0.297 0.703-0.297s0.516 0.109 0.703 0.297l4 4c0.187 0.187 0.297 0.438 0.297 0.703 0 0.547-0.453 1-1 1h-2v6h6v-2c0-0.547 0.453-1 1-1 0.266 0 0.516 0.109 0.703 0.297l4 4c0.187 0.187 0.297 0.438 0.297 0.703z"/></svg></div>
									<div class="sortable-content sortable-title"><?php esc_html_e( $name ); ?></div>
									<div class="sortable-content sortable-group"><?php echo '<span>' . esc_html($count) . ' Events</span>'; ?></div>
								</li>
					
							<?php endforeach; ?>
						</ul>
					
					<?php else: ?>
						
						<div class="notice notice-warning">
							<h3><?php _e( 'No Tag Found!', 'the-events-calendar-addon' ); ?></h3>
							<p><?php _e( 'We didn\'t find any Tag.</br>Please add some Tags to sort them.', 'the-events-calendar-addon' ); ?></p>
							<a href="<?php echo admin_url('edit-tags.php?taxonomy=post_tag&post_type=tribe_events'); ?>" class="button button-primary button-large"><?php _e( 'Add Category', 'the-events-calendar-addon' ); ?></a>
						</div>
		
					<?php endif; ?>
		
				</div>

				<div class="gs-teca--docs-area">
					<h3><?php esc_html_e('Query Settings for Tags', 'the-events-calendar-addon'); ?></h3>

					<div class="gs-teca--docs-area-content">
						
						<ol>
							<li>Create or Edit a Shortcode From <strong>The Events Calendar Addon > Shortcode</strong>.</li>
							<li>Then proceed to the 3rd tab labeled <strong>Query Settings</strong>.</li>
							<li>Set <strong>Tag Order by</strong> to <strong>Custom Order</strong>.</li>
							<li>Set <strong>Tag Order</strong> to <strong>ASC</strong>.</li>
						</ol>

						<ul>
							<li>Follow <a href="https://docs.gsplugins.com/gs-portfolio/manage-the-portfolios/sort-order/#reordering-groups-categories" target="_blank">Documentation</a> to learn more.</li>
							<li><a href="https://www.gsplugins.com/contact/" target="_blank">Contact us</a> for support.</li>
						</ul>

					</div>
				</div>

			</div><!-- #wrap -->

			<?php
		}

	}
}

$gs_teca_custom_order = new GS_Teca_Sortable( 'tribe_events', 'The Events Calendar Addon' );