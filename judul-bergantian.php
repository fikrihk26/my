<?php
/*
Plugin Name: Judul Halaman Bergantian
Description: Menampilkan judul semua halaman dengan tautan secara bergantian dengan efek fade melalui shortcode.
Version: 1.3
Author: Fikri
Author URI: 
*/

// Mencegah akses langsung ke file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =================================================================
// 1. PENGATURAN DAN HALAMAN ADMIN
// =================================================================

// (Fungsi-fungsi untuk halaman admin tidak berubah, hanya isinya)
add_action('admin_menu', 'jhb_register_settings_page');
function jhb_register_settings_page() {
    add_options_page('Pengaturan Judul Bergantian', 'Judul Bergantian', 'manage_options', 'judul-bergantian-settings', 'jhb_settings_page_html');
}

// Memperbarui halaman admin dengan info tentang file CSS
function jhb_settings_page_html() {
    if (!current_user_can('manage_options')) return;
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <p>Atur nilai default untuk shortcode Judul Halaman Bergantian.</p>
        
        <form action="options.php" method="post">
            <?php
            settings_fields('jhb_settings_group');
            do_settings_sections('judul-bergantian-settings');
            submit_button('Simpan Pengaturan');
            ?>
        </form>

        <hr>

        <h2><span class="dashicons dashicons-book-alt" style="vertical-align: middle;"></span> Petunjuk Penggunaan</h2>
        
        <h3>1. Shortcode</h3>
        <p>Gunakan shortcode berikut di post, halaman, atau widget untuk menampilkan rotator.
        <br><code>[judul_halaman_bergantian tag="h3" prefix="Produk"]</code></p>
        <ul>
            <li>• <code>tag=""</code>: Mengubah tag HTML (default: dari pengaturan di atas).</li>
            <li>• <code>prefix=""</code>: Memfilter judul halaman (default: dari pengaturan di atas).</li>
        </ul>

        <h3>2. Kustomisasi Tampilan (Styling)</h3>
        <p>Tampilan rotator diatur melalui file CSS. Jika Anda ingin mengubah warna, ukuran font, atau gaya lainnya, Anda dapat mengedit file:</p>
        <p><code style="background: #eee; padding: 2px 6px; border-radius: 4px;"><?php echo esc_html(plugin_dir_path(__FILE__)); ?>css/style.css</code></p>
        <p>Dengan memisahkan file CSS, pembaruan plugin di masa depan tidak akan menghilangkan kustomisasi gaya yang telah Anda buat (selama Anda mem-backup file `style.css` Anda).</p>
    </div>
    <?php
}

add_action('admin_init', 'jhb_register_settings');
function jhb_register_settings() {
    register_setting('jhb_settings_group', 'jhb_options', 'jhb_sanitize_options');
    add_settings_section('jhb_main_section', 'Pengaturan Default', null, 'judul-bergantian-settings');
    add_settings_field('jhb_html_tag_field', 'Default HTML Tag', 'jhb_html_tag_field_callback', 'judul-bergantian-settings', 'jhb_main_section');
    add_settings_field('jhb_prefix_field', 'Default Prefix Judul', 'jhb_prefix_field_callback', 'judul-bergantian-settings', 'jhb_main_section');
}

function jhb_html_tag_field_callback() {
    $options = get_option('jhb_options');
    $tag = isset($options['default_tag']) ? $options['default_tag'] : 'h2';
    echo '<input type="text" name="jhb_options[default_tag]" value="' . esc_attr($tag) . '" class="regular-text"><p class="description">Contoh: h1, h2, h3, p, div.</p>';
}

function jhb_prefix_field_callback() {
    $options = get_option('jhb_options');
    $prefix = isset($options['default_prefix']) ? $options['default_prefix'] : '';
    echo '<input type="text" name="jhb_options[default_prefix]" value="' . esc_attr($prefix) . '" class="regular-text"><p class="description">Kosongkan untuk menampilkan semua judul. Isi untuk memfilter (misal: "Layanan").</p>';
}

function jhb_sanitize_options($input) {
    $sanitized_input = [];
    if (isset($input['default_tag'])) $sanitized_input['default_tag'] = preg_replace('/[^a-zA-Z0-9]/', '', $input['default_tag']);
    if (isset($input['default_prefix'])) $sanitized_input['default_prefix'] = sanitize_text_field($input['default_prefix']);
    return $sanitized_input;
}

// =================================================================
// 2. FUNGSI SHORTCODE UTAMA
// =================================================================

function jhb_shortcode_handler( $atts ) {
    $options = get_option('jhb_options');
    $default_tag = isset($options['default_tag']) ? $options['default_tag'] : 'h2';
    $default_prefix = isset($options['default_prefix']) ? $options['default_prefix'] : '';

    $atts = shortcode_atts(['tag' => $default_tag, 'prefix' => $default_prefix], $atts, 'judul_halaman_bergantian');

    $all_pages_query = get_pages(['post_status' => 'publish']);
    $filtered_pages = [];
    
    foreach ($all_pages_query as $page) {
        if ( empty($atts['prefix']) || stripos($page->post_title, $atts['prefix']) === 0 ) {
            $filtered_pages[] = ['title' => esc_html($page->post_title), 'link' => esc_url(get_permalink($page->ID))];
        }
    }

    if (empty($filtered_pages)) return '';

    // Daftarkan dan muat file CSS
    wp_enqueue_style(
        'jhb-rotator-style',
        plugin_dir_url(__FILE__) . 'css/style.css',
        [], // Tidak ada dependensi
        '1.3' // Versi
    );

    // Daftarkan dan muat file JavaScript
    wp_enqueue_script('jhb-rotator-script', plugin_dir_url(__FILE__) . 'js/rotator.js', ['jquery'], '1.3', true);

    // Kirim data ke JavaScript
    wp_localize_script('jhb-rotator-script', 'jhbData', ['pages' => $filtered_pages, 'tag' => esc_html($atts['tag'])]);

    // Kembalikan div container KOSONG. Semua style dan atribut kini diatur oleh CSS.
    return '<div id="jhb-title-rotator-container"></div>';
}

if (shortcode_exists('judul_halaman_bergantian')) {
    remove_shortcode('judul_halaman_bergantian');
}
add_shortcode('judul_halaman_bergantian', 'jhb_shortcode_handler');