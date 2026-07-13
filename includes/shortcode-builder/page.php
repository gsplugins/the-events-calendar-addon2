<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="app-container">
	<div class="main-container">
		<div id="gs-teca-shortcode-app">
			<header class="gs-teca-header">
				<div class="gs-containeer-f">
					<div class="gs-roow">
						<div class="teca-area gs-col-xs-6">
							<router-link to="/"><img src="<?php echo esc_url( trailingslashit( GS_TECA_PLUGIN_URI ) . 'assets/img/icon.svg' ); ?>" alt="The events calendar addon"></router-link>
						</div>
						<div class="menu-area gs-col-xs-6 text-right">
							<ul>
								<router-link to="/" tag="li"><a><?php esc_html_e('Shortcodes', 'the-events-calendar-addon'); ?></a></router-link>
								<router-link to="/shortcode" tag="li"><a><?php esc_html_e('Create New', 'the-events-calendar-addon'); ?></a></router-link>
								<router-link to="/preferences" tag="li"><a><?php esc_html_e('Preferences', 'the-events-calendar-addon'); ?></a></router-link>
								<router-link to="/layout" tag="li"><a><?php esc_html_e('Layout', 'the-events-calendar-addon'); ?></a></router-link>
								<router-link to="/demo-data" tag="li"><a><?php esc_html_e('Demo Data', 'the-events-calendar-addon'); ?></a></router-link>
							</ul>
						</div>
					</div>
				</div>
			</header>

			<div class="gs-teca-app-view-container">
				<router-view :key="$route.fullPath"></router-view>
			</div>

		</div>
	</div>
</div>