<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;
if ( ! defined( 'ABSPATH' ) ) exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.

?>


<div class="search-filter <?php echo ( $gs_teca_filter_type === 'ajax-filter' ) ? 'search-filter-ajax' : '' ?>"></div>
<div class="gs-teca-filter-loader-spinner" style="display: none;"><img src="<?php echo esc_url(GS_TECA_PLUGIN_URI . 'assets/img/loader.svg'); ?>" alt="Loader Image"></div>

<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
