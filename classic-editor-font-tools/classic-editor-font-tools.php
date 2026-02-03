<?php
/*
Plugin Name: Classic Editor Zusatztool
Description: Farben & Styles + Emoji Picker im Classic Editor
Version: 1.0.3
Author: Stefan Schneebauer
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: classic-editor-font-tools
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* -------------------------------------------------
 * SETTINGS
 * ------------------------------------------------- */

add_action( 'admin_init', function () {

    register_setting(
        'ceft_settings',
        'ceft_primary_color',
        [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_hex_color',
            'default'           => '#1e73be',
        ]
    );

    register_setting(
        'ceft_settings',
        'ceft_secondary_color',
        [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_hex_color',
            'default'           => '#27ae60',
        ]
    );
});

/* -------------------------------------------------
 * ADMIN MENU
 * ------------------------------------------------- */

add_action( 'admin_menu', function () {

    add_options_page(
        'Classic Editor Font Tools',
        'Editor Font Tools',
        'manage_options',
        'ceft-settings',
        'ceft_settings_page'
    );
});

function ceft_settings_page() {
?>
    <div class="wrap">
        <h1>Classic Editor Font Tools</h1>

        <form method="post" action="options.php">
            <?php settings_fields( 'ceft_settings' ); ?>

            <table class="form-table">
                <tr>
                    <th>PrimÃƒÂ¤rfarbe</th>
                    <td>
                        <input type="color" name="ceft_primary_color"
                            value="<?php echo esc_attr( get_option( 'ceft_primary_color', '#1e73be' ) ); ?>">
                    </td>
                </tr>

                <tr>
                    <th>SekundÃƒÂ¤rfarbe</th>
                    <td>
                        <input type="color" name="ceft_secondary_color"
                            value="<?php echo esc_attr( get_option( 'ceft_secondary_color', '#27ae60' ) ); ?>">
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>

        <hr>

        <h2>Buttons im Editor</h2>
        <p>
            Beispiel:<br>
            <code>&lt;a href="#" class="ceft-btn ceft-btn-primary"&gt;Download Ã°Å¸Å¡â‚¬&lt;/a&gt;</code>
        </p>
    </div>
<?php
}

/* -------------------------------------------------
 * INLINE CSS (EDITOR + FRONTEND)
 * ------------------------------------------------- */

function ceft_inline_css() {

    $primary   = get_option( 'ceft_primary_color', '#1e73be' );
    $secondary = get_option( 'ceft_secondary_color', '#27ae60' );

    $css = "
    :root {
        --ceft-primary: {$primary};
        --ceft-secondary: {$secondary};
    }

    .ceft-btn {
        padding: 10px 18px;
        border-radius: 4px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
    }

    .ceft-btn-primary {
        background: var(--ceft-primary);
        color: #fff !important;
    }

    .ceft-btn-secondary {
        background: var(--ceft-secondary);
        color: #fff !important;
    }

    .ceft-box-hint {
        background: #f0f8ff;
        border-left: 5px solid var(--ceft-primary);
        padding: 15px;
        margin: 15px 0;
    }

    .ceft-box-warning {
        background: #fff3f3;
        border-left: 5px solid #e74c3c;
        padding: 15px;
        margin: 15px 0;
    }

    .emoji {
        font-size: 1.2em;
        line-height: 1;
    }
    ";

    wp_register_style( 'ceft-inline-style', false, [], '1.0.1' );
    wp_enqueue_style( 'ceft-inline-style' );
    wp_add_inline_style( 'ceft-inline-style', $css );
}

add_action( 'wp_enqueue_scripts', 'ceft_inline_css' );
add_action( 'admin_enqueue_scripts', 'ceft_inline_css' );

/* -------------------------------------------------
 * CLASSIC EDITOR (TinyMCE)
 * ------------------------------------------------- */

add_filter( 'mce_buttons', function ( $buttons ) {
    array_unshift( $buttons, 'fontselect', 'fontsizeselect' );
    return $buttons;
});

add_filter( 'mce_buttons', function ( $buttons ) {
    array_push( $buttons, 'styleselect', 'forecolor' );
    return $buttons;
});

add_filter( 'tiny_mce_before_init', function ( $init ) {

    $primary   = get_option( 'ceft_primary_color', '#1e73be' );
    $secondary = get_option( 'ceft_secondary_color', '#27ae60' );

    $init['fontsize_formats'] =
        '10pt 12pt 14pt 16pt 18pt 20pt 24pt 28pt 32pt 36pt 40pt 48pt 56pt 64pt';

    $init['textcolor_map'] = json_encode( [
        ltrim( $primary, '#' ),   'Primary color',
        ltrim( $secondary, '#' ), 'Secondary color',
        '000000',                 'Black',
        'ffffff',                 'White',
    ] );

    $init['style_formats'] = json_encode( [
        [
            'title'    => 'Button Primary',
            'selector' => 'a',
            'classes'  => 'ceft-btn ceft-btn-primary',
        ],
        [
            'title'    => 'Button Secondary',
            'selector' => 'a',
            'classes'  => 'ceft-btn ceft-btn-secondary',
        ],
        [
            'title'   => 'Hint Box',
            'block'   => 'div',
            'classes' => 'ceft-box-hint',
            'wrapper' => true,
        ],
        [
            'title'   => 'Warning Box',
            'block'   => 'div',
            'classes' => 'ceft-box-warning',
            'wrapper' => true,
        ],
    ] );

    return $init;
});

/* -------------------------------------------------
 * EDITOR STYLE SUPPORT
 * ------------------------------------------------- */

add_action( 'admin_init', function () {
    add_editor_style();
});

/* -------------------------------------------------
 * EMOJI PICKER (TinyMCE BUTTON)
 * ------------------------------------------------- */

add_filter( 'mce_external_plugins', function ( $plugins ) {
    $plugins['ceft_emoji'] = plugin_dir_url( __FILE__ ) . 'ceft-emoji.js';
    return $plugins;
});

add_filter( 'mce_buttons', function ( $buttons ) {
    $buttons[] = 'ceft_emoji';
    return $buttons;
});
