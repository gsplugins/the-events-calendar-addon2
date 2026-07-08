<?php
// if direct access than exit the file.
defined('ABSPATH') || exit;

return array(
    'Helpers'         => 'includes/helpers.php',
    'Hooks'           => 'includes/hooks.php',
    'Scripts'         => 'includes/scripts.php',
    'Shortcode'       => 'includes/shortcode.php',
    'Integrations'    => 'includes/integration/class-teca-integration.php',
    'Builder'         => 'includes/shortcode-builder/builder.php',
    'Query'           => 'includes/query.php',
    'Template_Loader' => 'includes/template-loader.php',
    'Single_Page_Filter' => 'includes/single-page-filter.php',
    'Calendar_Renderer'  => 'includes/calendar-renderer.php',
    'Events_Section_Renderer' => 'includes/events-section-renderer.php',
    'Venue_Template_Renderer' => 'includes/venue-template-renderer.php',
    'Organizer_Template_Renderer' => 'includes/organizer-template-renderer.php',
    'Timeline_Renderer'       => 'includes/timeline-renderer.php',
    'Accordion_Renderer'      => 'includes/accordion-renderer.php',
);