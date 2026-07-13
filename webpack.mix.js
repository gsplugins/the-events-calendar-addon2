const mix = require('laravel-mix');

const wpPot = require('wp-pot');

mix.options({
    autoprefixer: {
        remove: false
    },
    processCssUrls: false
});

mix.webpackConfig({
	target: 'web',
	externals: {
		jquery: "window.jQuery",
		$: "window.jQuery",
		wp: 'window.wp',
		React: 'window.React',
		CodeMirror: 'window.CodeMirror',
		_gs_teca_data: 'window._gs_teca_data'
	},
});

// Disable notification on dev mode
if ( process.env.NODE_ENV.trim() !== 'production' ) mix.disableNotifications();

// Public CSS
mix.sass('./dev/public/gs-teca.scss', './assets/css/gs-teca.min.css');
mix.sass('./dev/public/gs-teca-divi.scss', './assets/css/gs-teca-divi.min.css');

// Public JS (slider helpers must load before main bundle)
mix.scripts([
	'./dev/public/partials/gs-teca-slider-init.js',
	'./dev/public/gs-teca.js',
], './assets/js/gs-teca.min.js');

// Admin CSS
mix.sass('./dev/admin/gs-plugins-free.scss', './assets/admin/css/gs-plugins-free.min.css');

// Shortcode
mix.sass('./dev/shortcode/app.scss', './assets/admin/css/gs-teca-shortcode.min.css');
mix.sass('./dev/shortcode/preview.scss', './assets/css/gs-teca-shortcode-preview.min.css');
mix.js('./dev/shortcode/app.js', './assets/admin/js/gs-teca-shortcode.min.js').vue({ version: 2});
mix.scripts('./dev/shortcode/preview.js', './assets/js/preview.min.js');
// Sort

mix.sass('./dev/admin/gs-teca-sort.scss', './assets/admin/css/gs-teca-sort.min.css');
mix.scripts('./dev/admin/gs-teca-sort.js', './assets/admin/js/gs-teca-sort.min.js');

// Divi Builder
// mix.js('./includes/integrations/assets/divi/divi-builder.js', './includes/integrations/assets/divi/divi-builder.min.js');

// Gutenberg block
mix.js('./includes/integration/gutenberg/src/index.js', './includes/integration/gutenberg/index.js');
mix.sass('./includes/integration/gutenberg/src/editor.scss', './includes/integration/gutenberg/editor.css');

// Divi Builder module
mix.js('./includes/integration/divi/assets/js/teca-divi-editor.js', './includes/integration/divi/assets/js/teca-divi-editor.min.js');

if ( process.env.NODE_ENV.trim() === 'production' ) {

	// Language pot file generator
	wpPot({
		destFile: 'languages/the-events-calendar-addon.pot',
		domain: 'the-events-calendar-addon',
		package: 'the-events-calendar-addon2',
		src: '**/*.php'
	});

}