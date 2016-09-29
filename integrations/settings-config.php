<?php

use \Aventura\Edd\Bookings\Settings\Option\Option;
use \Aventura\Edd\Bookings\Settings\Section\Section;

/**
 * A simple interal integration that loads a configuration file of sections and options for the Plugin's settings.
 */

// Settings instance
$settings = eddBookings()->getSettings();

// Load the XML file
$xml = eddBookings()->loadConfigFile('settings');
// Stop if failed to load config file
if (is_null($xml)) {
    return;
}

// Iterate sections
foreach($xml as $sectionNode) {
    // Create a section instance
    $sectionAttrs = $sectionNode->attributes();
    $section = new Section(
        (string) $sectionAttrs['id'],
        translate((string) $sectionAttrs['name'], 'eddbk')
    );
    // Register the section
    $settings->addSection($section);
    // Iterate options
    foreach ($sectionNode->children() as $optionNode) {
        // Create the option instance
        $optionAttrs = $optionNode->attributes();
        $option = new Option(
            (string) $optionAttrs['id'],
            translate((string) $optionAttrs['name'], 'eddbk'),
            translate((string) $optionNode, 'eddbk'),
            translate((string) $optionAttrs['default'], 'eddbk'),
            (string) $optionAttrs['view']
        );
        // Register the option
        $section->addOption($option);
    }
}
