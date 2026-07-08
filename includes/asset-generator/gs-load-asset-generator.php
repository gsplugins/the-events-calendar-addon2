<?php

namespace GSWPS;

if ( ! defined( 'ABSPATH' ) ) exit;

require_once __DIR__ . '/gs-asset-generator-base.php';
require_once __DIR__ . '/gs-teca-asset-generator.php';

// Needed for pro compatibility
do_action( 'gs_teca_assets_generator_loaded' );