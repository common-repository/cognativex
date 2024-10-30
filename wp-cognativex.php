<?php
/*
 * @package   cognativex
 * @author    CognativeX
 * @copyright 2022 CognativeX
 * @license   GPL-2.0-or-later
 *
 *  Plugin Name:       CognativeX – The Best AI tool for Audience Growth & Engagement| Personalise your content experience
 *  Plugin URI:        https://github.com/cognativex/cx-wp-plugin
 *  Version:           2.2.6
 *  Description:       CognativeX Integration Plugin to enable tracking, widgets, and AD placement
 *  Author:            CognativeX
 *  Text Domain:       cognativex
 *  Requires PHP:      5.6
 *  Tested up to:      6.4
 *  Requires at least: 5.0
 *  Author URI:        https://cognativex.com
 *  License:           GPLv2 or later
 *  Text Domain:       wp-cognativex
 */


/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

//If no action has been specified, die
if (!function_exists('add_action')) {
    die('you can\t access this file');
}

class CognativexPlugin
{

    public $plugin_name = "wp-cognativex";
    public $plugin_title = "CognativeX Plugin";
    public $plugin_version = "2.2.6";


    public $add_something_nonce;
    public $ajax_url;
    public $user_id;

    //this is the dev url
    // public $api_url = "https://cx-portal-api-dot-cognativex-dev.ew.r.appspot.com/";

    public $api_url = "https://api-platform.cognativex.com/";

    public function __construct()
    {
        add_action('init', array($this, 'create_cx_widget_block'));
        add_action('admin_menu', array($this, 'addPluginSettingsMenu'));
        add_action('admin_init', array($this, 'registerAndBuildFields'));
        add_action('wp_footer',  'append_popup_widget');

    }

    
    public function create_cx_widget_block()
    {
        register_block_type(__DIR__ . '/build');
    }

    function register()
    {
        add_action('wp_head', array($this, 'buildTrackerScript'));
        add_action('wp_head', array($this, 'buildMetaScript'));
        add_action('wp_head', array($this, 'buildWidgetScript'));
    }

    function activate()
    {
        //here we should
        // $this->custom_post_type();
        //generate a CPT (custom post type)
        //flush rewrite rules
//        flush_rewrite_rules();
        create_publisher();
    }

    function deactivate()
    {
        //this doesn't get activated since the plugin is already deactivated
        //flush rewrite rules

        echo 'The plugin was deactivated';
    }

    static function uninstall()
    {
        //delete CPT
        //delete all the plugin data from the DB
    }




    public function addPluginSettingsMenu()
    {
        add_options_page("cognativex-settings", "Cognativex", 'administrator', 'cognativex-settings', array($this, 'displayPluginAdminSettings'));
    }

    public function displayPluginAdminSettings()
    {
        // set this var to be used in the settings-display view
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
        if (isset($_GET['error_message'])) {
            add_action('admin_notices', array($this, 'pluginNameSettingsMessages'));
            do_action('admin_notices', sanitize_text_field($_GET['error_message']));
        }
        require_once 'partials/' . $this->plugin_name . '-settings.php';
    }

    public function pluginNameSettingsMessages($error_message)
    {
        switch ($error_message) {
            case '1':
                $message = __('There was an error adding this setting. Please try again.  If this persists, shoot us an email.', 'cognativex');
                $err_code = esc_attr('wp_cognativex_domain_setting');
                $setting_field = 'wp_cognativex_domain_setting';
                break;
        }
        $type = 'error';
        add_settings_error(
            $setting_field,
            $err_code,
            $message,
            $type
        );
    }

    public function registerAndBuildFields()
    {
        /**
         * First, we add_settings_section. This is necessary since all future settings must belong to one.
         * Second, add_settings_field
         * Third, register_setting
         */
        add_settings_section(
            // ID used to identify this section and with which to register options
            'wp_cognativex_general_section',
            // Title to be displayed on the administration page
            'Basic Configurations',
            // Callback used to render the description of the section
            array($this, 'wp_cognativex_display_general_account'),
            // Page on which to add this section of options
            'wp_cognativex_general_settings'
        );
        unset($args);
        $args = array(
            'type' => 'input',
            'subtype' => 'text',
            'id' => 'wp_cognativex_domain_setting',
            'name' => 'wp_cognativex_domain_setting',
            'required' => 'true',
            'get_options_list' => '',
            'value_type' => 'normal',
            'wp_data' => 'option'
        );
        add_settings_field(
            'wp_cognativex_domain_setting',
            __('Domain', 'cognativex'),
            array($this, 'wp_cognativex_render_settings_field'),
            'wp_cognativex_general_settings',
            'wp_cognativex_general_section',
            $args
        );

        register_setting(
            'wp_cognativex_general_settings',
            'wp_cognativex_domain_setting',
            array($this, 'validate_domain_setting')
        );
        add_settings_section(
            // ID used to identify this section and with which to register options
            'wp_cognativex_widget_section',
            // Title to be displayed on the administration page
            'Widget Settings',
            // Callback used to render the description of the section
            array($this, 'wp_cognativex_display_widget_account'),
            // Page on which to add this section of options
            'wp_cognativex_general_settings'
        );
        unset($args);
        $args = array(
            'type' => 'input',
            'subtype' => 'text',
            'id' => 'wp_cognativex_widget_ids_setting',
            'name' => 'wp_cognativex_widget_ids_setting',
            'required' => 'required',
            'help_text' => 'widget id sent by CognativeX',
            'get_options_list' => '',
            'value_type' => 'normal',
            'wp_data' => 'option'
        );
        add_settings_field(
            'wp_cognativex_widget_ids_setting',
            __('Widget IDs', 'cognativex'),
            array($this, 'wp_cognativex_render_settings_field'),
            'wp_cognativex_general_settings',
            'wp_cognativex_widget_section',
            $args
        );

        register_setting(
            'wp_cognativex_general_settings',
            'wp_cognativex_widget_ids_setting'
        );


    }

    //this function will check for publisher ID, if existing, then the publisher appdomain is updated. else, the publisher is created
    public function validate_domain_setting($domain)
    {

        $current_domain = get_option('wp_cognativex_domain_setting');
        if ($current_domain == $domain) {
            return $domain;
        } else {
            $publisher_id = get_option('wp_cognativex_publisher_id_setting');
            if ($publisher_id) {
                //this means that the update appdomain is to be used
                $request_url = $this->api_url . 'wordpress-update-domain';
                // $request_url = 'https://webhook.site/017f71b7-bb13-4484-9a94-54c05acd3726';

                $response = wp_remote_post(
                    $request_url,
                    array(
                        'method' => 'POST',
                        'timeout' => 45,
                        'redirection' => 0,
                        'headers' => array(
                            'accept-encoding' => 'deflate, gzip, br'
                        ),
                        'body' => "data={\"id\":\"$publisher_id\",\"newDomain\":\"$domain\"}"
                    )
                );

                if (isset(json_decode($response['body'])->publisherId)) {
                    $publisher_id = json_decode($response['body'])->publisherId;
                    update_option('wp_cognativex_publisher_id_active', __('success-domain has been succesfully updated'));
                    update_option('wp_cognativex_publisher_id_active', __('success-Plugin is Active, and your publisher ID is: ') . $publisher_id);
                    return $domain;
                } else {
                    update_option('wp_cognativex_publisher_id_active', __('error-An error has occured while trying to update the domain'));
                    return $current_domain;
                }
            } else {
                $admin_email = get_bloginfo("admin_email");
                $request_url = $this->api_url . 'wordpress-publisher-create';
                // $request_url = 'https://webhook.site/017f71b7-bb13-4484-9a94-54c05acd3726';

                $response = wp_remote_post(
                    $request_url,
                    array(
                        'method' => 'POST',
                        'timeout' => 45,
                        'redirection' => 0,
                        'headers' => array(
                            'accept-encoding' => 'deflate, gzip, br'
                        ),
                        'body' => "data={\"domain\":\"$domain\",\"portalEmail\":\"$admin_email\",\"portalPassword\":\"\"}"
                    )
                );

                if (isset(json_decode($response['body'])->publisherId)) {
                    $widget_ids = json_decode($response['body'])->bottomTemplateWidgetId . ',' . json_decode($response['body'])->popupWidgetId;
                    $publisher_id = json_decode($response['body'])->publisher_id;
                    update_option("wp_cognativex_publisher_id_setting", $publisher_id);
                    update_option("wp_cognativex_domain_setting", $domain);
                    update_option("wp_cognativex_widget_ids_setting", $widget_ids);
                    update_option('wp_cognativex_plugin_notice', __('success-A publisher has been successfully created for this instance'));
                    update_option('wp_cognativex_publisher_id_active', __('success-Plugin is Active, and your publisher ID is: ') . $publisher_id);
                    
                    return $domain;
                } else {
                    update_option('wp_cognativex_publisher_id_active', 'error-An error has occured while trying to create a publisher');
                    return $current_domain;
                }

            }
        }

    }
    public function wp_cognativex_display_general_account()
    {
        echo __('<p>Please fill the following settings for the plugin to function properly; ex : example.com</p>', 'cognativex');
    }


    public function wp_cognativex_display_widget_account()
    {
        echo __('<p>The Widget IDs are supplied to you by CognativeX. In case of more than one widget, add the widget numbers separated by commas; ex: ID-1,ID-2 </p>', 'cognativex');
    }

    public function wp_cognativex_render_settings_field($args)
    {
        /* EXAMPLE INPUT
        'type'      => 'input',
        'subtype'   => '',
        'id'    => $this->wp_cognativex.'_example_setting',
        'name'      => $this->wp_cognativex.'_example_setting',
        'required' => 'required="required"',
        'get_option_list' => "",
        'value_type' = serialized OR normal,
        'wp_data'=>(option or post_meta),
        'post_id' =>
        */
        if ($args['wp_data'] == 'option') {
            $wp_data_value = get_option($args['name']);
        } elseif ($args['wp_data'] == 'post_meta') {
            $wp_data_value = get_post_meta($args['post_id'], $args['name'], true);
        }

        switch ($args['type']) {

            case 'input':
                $value = ($args['value_type'] == 'serialized') ? serialize($wp_data_value) : $wp_data_value;
                if ($args['subtype'] != 'checkbox') {
                    $step = (isset($args['step'])) ? 'step="' . $args['step'] . '"' : '';
                    $min = (isset($args['min'])) ? 'min="' . $args['min'] . '"' : '';
                    $max = (isset($args['max'])) ? 'max="' . $args['max'] . '"' : '';
                    if (isset($args['disabled'])) {
                        // hide the actual input bc if it was just a disabled input, the information saved in the database would be wrong - bc it would pass empty values and wipe the actual information
                        echo '<input type="' . esc_attr($args['subtype']) . '" id="' . esc_attr($args['id']) . '_disabled" ' . esc_attr($step) . ' ' . esc_attr($max) . ' ' . esc_attr($min) . ' name="' . esc_attr($args['name']) . '_disabled" size="40" disabled value="' . esc_attr($value) . '" /><input type="hidden" id="' . esc_attr($args['id']) . '" ' . esc_attr($step) . ' ' . esc_attr($max) . ' ' . esc_attr($min) . ' name="' . esc_attr($args['name']) . '" size="40" value="' . esc_attr($value) . '" />';
                    } else {
                        echo '<input type="' . esc_attr($args['subtype']) . '" id="' . esc_attr($args['id']) . '" "' . esc_attr($args['required']) . '" ' . esc_attr($step) . ' ' . esc_attr($max) . ' ' . esc_attr($min) . ' name="' . esc_attr($args['name']) . '" size="40" value="' . esc_attr($value) . '" />';
                    }
                } else {
                    $checked = ($value) ? 'checked' : '';
                    echo '<input  type="' . esc_attr($args['subtype']) . '" id="' . esc_attr($args['id']) . '" "' . esc_attr($args['required']) . '" name="' . esc_attr($args['name']) . '" size="40" value="1" ' . esc_attr($checked) . ' />';
                }
                break;
            default:
                # code...
                break;
        }
    }

    public function buildTrackerScript()
    {
        $domain_option_value = get_option('wp_cognativex_domain_setting');
        $domain_array = explode('.', $domain_option_value, -1);
        $domain = implode('.', $domain_array);
        $tracker_script = "
<script>      
(function (s, l, d, a) {
    var h = d.location.protocol,
        i = l + \"-\" + s,
        td = new Date(),
        dt = td.getFullYear() + '-' + (td.getMonth() + 1) + '-' + td.getDate();
    f = d.getElementsByTagName(s)[0],
        e = d.getElementById(i), u = \"static.cognativex.com\";
    if (e) return;
    e = d.createElement(s);
    e.id = i;
    e.async = true;
    e.src = h + \"//\" + u + \"/\" + l + \"/cn.js?v=\" + dt;
    e.setAttribute('data-domain', a);
    f.parentNode.insertBefore(e, f);
})(\"script\", \"cognativex\", document, \"" . esc_js($domain_option_value) . "\");
</script>
";
        echo $tracker_script;
    }

    public function buildWidgetScript()
    {
        $domain_option_value = get_option('wp_cognativex_widget_ids_setting');
        $widget_script = "
<script>        
 window.COGNATIVEX = window.COGNATIVEX || {};
 window.COGNATIVEX.widgets = window.COGNATIVEX.widgets || [];
 window.COGNATIVEX.widgets.push(function () {
 window.COGNATIVEX.renderWidgets([" . esc_js($domain_option_value) . "]);
 });
</script>
";
        echo $widget_script;
    }

    public function buildMetaScript()
    {
        $domain_option_value = get_option('wp_cognativex_domain_setting');
        $postId = get_queried_object_id();
        $post_data = get_post($postId);
        $post_content = wp_strip_all_tags(get_the_content());
        $post_tags = get_the_tags($post_data->ID);
        $post_tags_html = [];
        if ($post_tags != false) {
            if (sizeof($post_tags) > 0) {
                foreach (array_column($post_tags, 'name') as $post_tag) {
                    array_push($post_tags_html, html_entity_decode($post_tag));
                    $tag_slugs = $post_tags == false ? '' : implode(',', $post_tags_html);
                }
            } else {
                $tag_slugs = "";
            }
        } else {
            $tag_slugs = "";
        }
        $tag_slugs_json = $tag_slugs == "" ? "" : $tag_slugs;
        $post_url = get_the_post_thumbnail_url();
        $post_language = get_locale();
        $post_language_2_chars = substr($post_language, 0, 2);
        $post_published_time = (new DateTime($post_data->post_date))->format(DateTime::ATOM);
        $post_last_updated = (new DateTime($post_data->post_modified))->format(DateTime::ATOM);
        $user_data = get_user_meta($post_data->post_author);
        $user_full_name = $user_data['first_name'][0] . ' ' . $user_data['last_name'][0];
        $post_categories = implode(',', wp_get_post_categories($post_data->ID, ['fields' => 'names']));
        $classes = [];
        //if we want to extend the classes to the taxonomy list in WordPress
        $classes[0] = ['key' => 'class1', 'mapping' => 'category', 'value' => "$post_categories"];
        $classes_json_encoded = json_encode($classes, JSON_UNESCAPED_UNICODE);
        //        $category_slugs = implode(',',array_column($post_categories, 'name'));
        $meta_script_array = [
            "type" => "article",
            "postid" => $post_data->ID,
            "title" => $post_data->post_title,
            "url" => $post_data->guid,
            "keywords" => $tag_slugs_json,
            "thumbnail" => $post_url,
            "lang" => $post_language_2_chars,
            "published_time" => $post_published_time,
            "last_updated" => $post_last_updated,
            "description" => $post_content,
            "author" => $user_full_name,
            "classes" => $classes
        ];
        $meta_script = '<script id="cognativex-metadata" type="text/cognativex">' . json_encode($meta_script_array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
        echo $meta_script;
    }


}

if (class_exists("CognativexPlugin")) {
    $cognativexPlugin = new CognativexPlugin();
    $cognativexPlugin->register();
} else {
    die("Error loading Class");
}

function append_popup_widget(){
    $popup_widget_id = get_option('wp_cognativex_popup_widget_id');
    if (isset($popup_widget_id)){
    echo '<div id="cognativex-widget-'.$popup_widget_id.'"></div>';
    }
}

function create_publisher()
{
    
    //this is the dev url
    // $api_url = "https://cx-portal-api-dot-cognativex-dev.ew.r.appspot.com/";

    $api_url = "https://api-platform.cognativex.com/";

    // if ($test = 1){
    //     return '';
    // }
    $site_settings = [];
    $site_url = parse_url(get_bloginfo("url"))['host'];
    $admin_email = get_bloginfo("admin_email");
    $request_url = $api_url . 'wordpress-publisher-create';
    // $request_url = 'https://webhook.site/cf395f0d-3b21-422f-ba72-d8abd32b2490';

    $response = wp_remote_post(
        $request_url,
        array(
            'method' => 'POST',
            'timeout' => 45,
            'redirection' => 0,
            'headers' => array(
                'accept-encoding' => 'deflate, gzip, br'
            ),
            'body' => "data={\"domain\":\"$site_url\",\"portalEmail\":\"$admin_email\",\"portalPassword\":\"\"}"
        )
    );
    delete_option('wp_cognativex_publisher_id_active');

    if (isset(json_decode($response['body'])->data->publisherId)) {
        $popup_widget_id = json_decode($response['body'])->data->popupWidgetId;
        $bottom_widget_id = json_decode($response['body'])->data->bottomTemplateWidgetId;
        $widget_ids =  $bottom_widget_id . ',' . $popup_widget_id;
        $publisher_id = json_decode($response['body'])->data->publisherId;
        update_option("wp_cognativex_publisher_id_setting", $publisher_id);
        update_option("wp_cognativex_domain_setting", $site_url);
        update_option("wp_cognativex_widget_ids_setting", $widget_ids);
        update_option("wp_cognativex_popup_widget_id", $popup_widget_id);
        update_option('wp_cognativex_plugin_notice', __('success-A publisher has been successfully created for this instance'));
        update_option('wp_cognativex_publisher_id_active', __('success-Plugin is Active, and your publisher ID is: ') . $publisher_id);

    } elseif (isset(json_decode($response['body'])->exception)) {
        $exception_details = explode('-', (json_decode($response['body'])->exception));
        if ($exception_details[0] == 'duplicate') {
            $publisher_id = $exception_details[2];
            delete_option('wp_cognativex_plugin_notice');
            update_option('wp_cognativex_publisher_id_setting', $publisher_id);
            update_option('wp_cognativex_publisher_id_active', __('success-Plugin is Active, and your publisher ID is: ') . $publisher_id);
        } else {
            update_option('wp_cognativex_plugin_notice', __('error- an error has occured'));
        }
    }
    // {
    // "exception": "duplicate-appdomain-5750852904026112",
    // "api": "/wordpress-publisher-create"
// }
    return $response;
}
//these events fire functions in the plugin
// activation
register_activation_hook(__FILE__, array($cognativexPlugin, 'activate'));
// deactivation
register_deactivation_hook(__FILE__, array($cognativexPlugin, 'activate'));
//uninstall
//register_uninstall_hook(__FILE__, array($cognativexPlugin, 'uninstall'));
