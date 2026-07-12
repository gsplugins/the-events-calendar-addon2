<?php

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Shared asset-generator namespace is intentional and kept for backward compatibility.
namespace GSWPS;

if ( ! defined( 'ABSPATH' ) ) exit;

require_once __DIR__ . '/gs-asset-generator-base.php';
require_once __DIR__ . '/gs-teca-asset-generator.php';

// Needed for pro compatibility
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook name is kept for backward compatibility.
do_action( 'gs_teca_assets_generator_loaded' );