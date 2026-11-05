<?php
/**
 * Plugin Name: GlitchEpoch
 * Plugin URI: https://github.com/lookee/glitchepoch
 * Description: A refined, lightweight elapsed-time counter for WordPress. No dependencies, no tracking.
 * Version: 1.0.0
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Author: Luca Amore
 * Author URI: https://www.lucaamore.com/
 * Project GitHub: https://github.com/lookee/glitchepoch
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: glitchepoch
 *
 * Copyright (C) 2026 Luca Amore
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'GLITCHEPOCH_VERSION', '1.0.0' );

/**
 * Enqueue the small, dependency-free frontend assets.
 *
 * The files are deliberately loaded on frontend pages so shortcodes in
 * widgets, custom fields, or page builders work without extra setup.
 */
function glitchepoch_enqueue_assets() {
    wp_enqueue_style(
        'glitchepoch',
        plugins_url( 'assets/css/glitchepoch.css', __FILE__ ),
        array(),
        GLITCHEPOCH_VERSION
    );

    wp_enqueue_script(
        'glitchepoch',
        plugins_url( 'assets/js/glitchepoch.js', __FILE__ ),
        array(),
        GLITCHEPOCH_VERSION,
        true
    );
}
add_action( 'wp_enqueue_scripts', 'glitchepoch_enqueue_assets' );

/**
 * Parse a local date and convert it to UTC for a DST-safe browser counter.
 *
 * @param string $start    Date in YYYY-MM-DD HH:MM[:SS] format.
 * @param string $timezone PHP/IANA timezone identifier.
 * @return DateTimeImmutable|null Parsed date or null for invalid input.
 */
function glitchepoch_parse_start( $start, $timezone ) {
    if ( ! is_string( $start ) || ! is_string( $timezone ) ) {
        return null;
    }

    $start = trim( $start );
    $timezone = trim( $timezone );

    if ( ! preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}(?::\d{2})?$/D', $start ) ) {
        return null;
    }

    if ( strlen( $start ) === 16 ) {
        $start .= ':00';
    }

    try {
        $zone = new DateTimeZone( $timezone );
        $date = DateTimeImmutable::createFromFormat( '!Y-m-d H:i:s', $start, $zone );

        // Reject invalid dates and nonexistent local times during DST transitions.
        if ( ! $date || $date->format( 'Y-m-d H:i:s' ) !== $start ) {
            return null;
        }

        return $date;
    } catch ( Exception $error ) {
        return null;
    }
}

/**
 * Render a single elapsed-time counter.
 *
 * Usage: [glitchepoch start="2010-12-05 02:00:00" timezone="Europe/Rome"]
 * Optional: accent="#007AFF"
 *
 * @param array|string $attributes Shortcode attributes.
 * @return string Accessible markup or a helpful configuration message.
 */
function glitchepoch_shortcode( $attributes = array() ) {
    if ( ! is_array( $attributes ) ) {
        $attributes = array();
    }

    $attributes = shortcode_atts(
        array(
            'start'    => '',
            'timezone' => '',
            'accent'   => '#007AFF',
        ),
        $attributes,
        'glitchepoch'
    );

    $timezone = trim( (string) $attributes['timezone'] );
    if ( '' === $timezone ) {
        $timezone = wp_timezone_string();
        if ( '' === $timezone ) {
            $timezone = 'UTC';
        }
    }

    $start = glitchepoch_parse_start( (string) $attributes['start'], $timezone );
    if ( null === $start ) {
        return '<p class="glitchepoch-error">' . esc_html__( 'GlitchEpoch: provide a valid start date and timezone.', 'glitchepoch' ) . '</p>';
    }

    $accent = sanitize_hex_color( (string) $attributes['accent'] );
    if ( ! $accent ) {
        $accent = '#007AFF';
    }

    $utc = $start->setTimezone( new DateTimeZone( 'UTC' ) )->format( 'Y-m-d\TH:i:s\Z' );
    $total = max( 0, time() - $start->getTimestamp() );
    $values = array(
        'days'    => number_format( intdiv( $total, 86400 ), 0, '.', ',' ),
        'hours'   => sprintf( '%02d', intdiv( $total % 86400, 3600 ) ),
        'minutes' => sprintf( '%02d', intdiv( $total % 3600, 60 ) ),
        'seconds' => sprintf( '%02d', $total % 60 ),
    );
    $labels = array(
        'days'    => __( 'Days', 'glitchepoch' ),
        'hours'   => __( 'Hours', 'glitchepoch' ),
        'minutes' => __( 'Minutes', 'glitchepoch' ),
        'seconds' => __( 'Seconds', 'glitchepoch' ),
    );

    $description = sprintf(
        /* translators: 1: local start date, 2: IANA timezone. */
        __( 'Elapsed time since %1$s (%2$s)', 'glitchepoch' ),
        $start->format( 'Y-m-d H:i:s' ),
        $timezone
    );

    $html = '<div class="glitchepoch" role="timer" aria-live="off" aria-label="' . esc_attr( $description ) . '" data-start="' . esc_attr( $utc ) . '" style="--ge-accent:' . esc_attr( $accent ) . '">';
    $html .= '<div class="glitchepoch__grid">';

    foreach ( $values as $key => $value ) {
        $html .= '<div class="glitchepoch__unit">';
        $html .= '<span class="glitchepoch__number" data-unit="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</span>';
        $html .= '<span class="glitchepoch__label">' . esc_html( $labels[ $key ] ) . '</span>';
        $html .= '</div>';
    }

    $html .= '</div></div>';
    return $html;
}
add_shortcode( 'glitchepoch', 'glitchepoch_shortcode' );

/**
 * Provide a discoverable usage guide without storing any options.
 */
function glitchepoch_admin_menu() {
    add_options_page(
        __( 'GlitchEpoch', 'glitchepoch' ),
        __( 'GlitchEpoch', 'glitchepoch' ),
        'manage_options',
        'glitchepoch',
        'glitchepoch_admin_page'
    );
}
add_action( 'admin_menu', 'glitchepoch_admin_menu' );

function glitchepoch_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'GlitchEpoch', 'glitchepoch' ); ?></h1>
        <p><?php esc_html_e( 'A minimal elapsed-time counter. No accounts, dependencies, or tracking.', 'glitchepoch' ); ?></p>
        <h2><?php esc_html_e( 'Quick start', 'glitchepoch' ); ?></h2>
        <p><?php esc_html_e( 'Paste this shortcode into any post, page, or Shortcode block:', 'glitchepoch' ); ?></p>
        <p><code>[glitchepoch start="2010-12-05 02:00:00" timezone="Europe/Rome"]</code></p>
        <p><?php esc_html_e( 'Change the start date, timezone, and optional accent color to match your content.', 'glitchepoch' ); ?></p>
        <p><code>[glitchepoch start="2020-01-01 00:00" timezone="UTC" accent="#2563EB"]</code></p>
        <p>
            <a href="<?php echo esc_url( 'https://github.com/lookee/glitchepoch' ); ?>" target="_blank" rel="noopener noreferrer">
                <?php esc_html_e( 'Documentation and source code', 'glitchepoch' ); ?>
            </a>
        </p>
        <hr>
        <h2><?php esc_html_e( 'About the author', 'glitchepoch' ); ?></h2>
        <p><?php esc_html_e( 'GlitchEpoch is an open-source project created and maintained by Luca Amore.', 'glitchepoch' ); ?></p>
        <p>
            <a href="<?php echo esc_url( 'https://www.lucaamore.com/' ); ?>" target="_blank" rel="noopener noreferrer">lucaamore.com</a>
            &nbsp;·&nbsp;
            <a href="<?php echo esc_url( 'https://github.com/lookee' ); ?>" target="_blank" rel="noopener noreferrer">GitHub: @lookee</a>
        </p>
    </div>
    <?php
}
