<?php

class EHSSL_Settings_Menu extends EHSSL_Admin_Menu
{
    public $menu_page_slug = EHSSL_SETTINGS_MENU_SLUG;

    // Specify all the tabs of this menu in the following array.
    public $dashboard_menu_tabs = array('tab1' => 'General', 'tab2' => 'HTTPS Redirection', 'tab3' => 'Mixed Contents');

    public function __construct()
    {
        $this->render_menu_page();
    }

    public function get_current_tab()
    {
        $tab = isset($_GET['tab']) ? $_GET['tab'] : array_keys($this->dashboard_menu_tabs)[0];
        return $tab;
    }

    /**
     * Renders our tabs of this menu as nav items
     */
    public function render_page_tabs()
    {
        $current_tab = $this->get_current_tab();
        foreach ($this->dashboard_menu_tabs as $tab_key => $tab_caption) {
            $active = $current_tab == $tab_key ? 'nav-tab-active' : '';
            echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->menu_page_slug . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
        }
    }

    /**
     * The menu rendering goes here
     */
    public function render_menu_page()
    {
        $tab = $this->get_current_tab();

        ?>
        <div class="wrap">
            <h2><?php _e("Settings", EHSSL_TEXT_DOMAIN)?></h2>
            <h2 class="nav-tab-wrapper"><?php $this->render_page_tabs();?></h2>
            <div id="poststuff"><div id="post-body">
            <?php

        $tab_keys = array_keys($this->dashboard_menu_tabs);
        switch ($tab) {
            case $tab_keys[1]:
                //include_once('file-to-handle-this-tab-rendering.php');//If you want to include a file
                $this->render_https_redirection_tab();
                break;
            case $tab_keys[2]:
                //include_once('file-to-handle-this-tab-rendering.php');//If you want to include a file
                $this->render_mixed_content_tab();
                break;
            default:
                //include_once('file-to-handle-this-tab-rendering.php');//If you want to include a file
                $this->render_general_tab();
                break;
        }
        ?>
            </div>
        </div>
        <?php $this->documentation_link_box();?>
        </div><!-- end or wrap -->
        <?php
}

    public function render_general_tab()
    {
        global $httpsrdrctn_options;

        // Save data for settings page.
        if (isset($_POST['ehssl_debug_log_form_submit']) && check_admin_referer('ehssl_debug_settings_nonce')) {
            $httpsrdrctn_options['enable_debug_logging'] = isset($_POST['enable_debug_logging']) ? esc_attr($_POST['enable_debug_logging']) : 0;

            update_option('httpsrdrctn_options', $httpsrdrctn_options)

            ?>
            <div class="notice notice-success">
                <p><?php _e("Settings Saved.", EHSSL_TEXT_DOMAIN);?></p>
            </div>
            <?php
        }

        $is_debug_logging_enabled = isset($httpsrdrctn_options['enable_debug_logging']) ? esc_attr($httpsrdrctn_options['enable_debug_logging']) : 0;

        ?>
        <div class="postbox">
            <h3 class="hndle"><label for="title"><?php _e("Debug Logging", EHSSL_TEXT_DOMAIN);?></label></h3>
            <div class="inside">
            <p>
                <?php _e('Debug logging can be useful to troubleshoot transaction processing related issues on your site. keep it disabled unless you are troubleshooting.', EHSSL_TEXT_DOMAIN);?>
            </p>
            <form id="ehssl_debug_settings_form" method="post" action="">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">
                            <label for="ehssl-debug-enable-checkbox">
                                <?php _e('Enable Debug Logging', EHSSL_TEXT_DOMAIN);?>
                            </label>
                        </th>
                        <td>
                            <input type="checkbox" id="ehssl-debug-enable-checkbox" name="enable_debug_logging" value="1" <?php if ('1' == $is_debug_logging_enabled) {echo "checked=\"checked\" ";}?> />
                            <br />
                            <p class="description"><?php _e("Check this option to enable debug logging.", EHSSL_TEXT_DOMAIN);?></p>
                            <p class="description">
                                <a href="<?php echo wp_nonce_url(get_admin_url() . '?ehssl-debug-action=view_log', 'ehssl_view_log_nonce'); ?>" target="_blank">
                                    <?php _e('Click here', EHSSL_TEXT_DOMAIN)?>
                                </a>
                                <?php _e(' to view log file.', EHSSL_TEXT_DOMAIN);?>
                                <br>
                                <a id="ehssl-reset-log" href="#0" style="color: red">
                                    <?php _e('Click here', EHSSL_TEXT_DOMAIN);?>
                                </a>
                                <?php _e(' to reset log file.', EHSSL_TEXT_DOMAIN);?>
                            </p>
                        </td>
                    </tr>
                </table>

                <input type="submit" name="ehssl_debug_log_form_submit" class="button-primary" value="<?php _e('Save Changes')?>" />
                <?php wp_nonce_field('ehssl_debug_settings_nonce');?>
            </form>
            </div><!-- end of inside -->
        </div><!-- end of postbox -->
        <script>
            jQuery( document ).ready( function( $ ) {
                const ehssl_ajaxurl = "<?php echo get_admin_url() . 'admin-ajax.php' ?>";
				const ehssl_ajax_nonce = "<?php echo wp_create_nonce('ehssl_settings_ajax_nonce') ?>";
                $( '#ehssl-reset-log' ).on('click', function( e ) {
                    e.preventDefault();
                    $.post( ehssl_ajaxurl,
                            {
                                action: 'ehssl_reset_log',
                                nonce: ehssl_ajax_nonce
                            },
                            function( result ) {
                                if ( result === '1' ) {
                                    alert( wp.i18n.__("Log file has been reset.", "<?php echo EHSSL_TEXT_DOMAIN ?>") );
                                } else {
                                    alert(  wp.i18n.__('Error trying to reset log: ' + result , "<?php echo EHSSL_TEXT_DOMAIN ?>"));
                                }
                            } );
                } );
            } );
        </script>
        <?php
}

    public function render_mixed_content_tab()
    {
        global $httpsrdrctn_options;

        $is_https_redirection_enabled = isset($httpsrdrctn_options['https']) && esc_attr($httpsrdrctn_options['https']) == '1' ? true : false;

        if (isset($_POST['ehssl_mixed_content_form_submit']) && check_admin_referer('ehssl_mixed_content_settings_nonce')) {
            $httpsrdrctn_options['force_resources'] = isset($_POST['httpsrdrctn_force_resources']) ? esc_attr($_POST['httpsrdrctn_force_resources']) : 0;
            update_option('httpsrdrctn_options', $httpsrdrctn_options)

            ?>
            <div class="notice notice-success">
                <p><?php _e("Settings Saved.", EHSSL_TEXT_DOMAIN);?></p>
            </div>
            <?php
}
        ?>
        <div class="postbox">
            <h3 class="hndle"><label for="title"><?php _e("Mixed Contents", EHSSL_TEXT_DOMAIN);?></label></h3>
            <div class="inside">
                <?php if(!$is_https_redirection_enabled){ ?>
                    <div style="background: #fff6d5; border: 1px solid #d1b655; color: #3f2502; margin: 10px 0; padding: 0px 5px 0px 10px; text-shadow: 1px 1px #ffffff;">
                        <p>
                            <?php _e("HTTPS redirection is turned off. Turn it on first to change these settings below!", EHSSL_TEXT_DOMAIN);?>
                        </p>
                    </div>
                <?php } ?>
                <form action="" method="POST">
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><?php _e('Force resources to use HTTPS URL', 'https_redirection');?></th>
                            <td>
                                <label>
                                    <input type="checkbox" <?php if (!$is_https_redirection_enabled) {echo "disabled";}?> name="httpsrdrctn_force_resources" value="1" <?php if (isset($httpsrdrctn_options['force_resources']) && $httpsrdrctn_options['force_resources'] == '1') {
            echo "checked=\"checked\" ";
        }
        ?> />
                                </label><br />
                                <p class="description"><?php _e('When checked, the plugin will force load HTTPS URL for any static resources in your content. Example: if you have have an image embedded in a post with a NON-HTTPS URL, this option will change that to a HTTPS URL.', 'https_redirection');?></p>
                            </td>
                        </tr>
                    </table>
                    <input type="submit" name="ehssl_mixed_content_form_submit" class="button-primary" value="<?php _e('Save Changes')?>" <?php if (!$is_https_redirection_enabled) {echo "disabled";}?>/>
                    <?php wp_nonce_field('ehssl_mixed_content_settings_nonce');?>
                </form>
            </div><!-- end of inside -->
        </div><!-- end of postbox -->
        <?php
}

    /**
     * The menu rendering goes here
     */
    public function render_https_redirection_tab()
    {
        global $httpsrdrctn_admin_fields_enable, $httpsrdrctn_options;
        // global $wp_rewrite; echo "<pre>"; var_dump($wp_rewrite);

        $error = "";

        // Save data for settings page.
        if (isset($_REQUEST['httpsrdrctn_form_submit']) && check_admin_referer(plugin_basename(__FILE__), 'httpsrdrctn_nonce_name')) {
            $httpsrdrctn_options['https'] = isset($_REQUEST['httpsrdrctn_https']) ? $_REQUEST['httpsrdrctn_https'] : 0;
            $httpsrdrctn_options['https_domain'] = isset($_REQUEST['httpsrdrctn_https_domain']) ? $_REQUEST['httpsrdrctn_https_domain'] : 0;

            if (isset($_REQUEST['httpsrdrctn_https_pages_array'])) {
                $httpsrdrctn_options['https_pages_array'] = array();
                // var_dump($httpsrdrctn_options['https_pages_array']);
                foreach ($_REQUEST['httpsrdrctn_https_pages_array'] as $httpsrdrctn_https_page) {
                    if (!empty($httpsrdrctn_https_page) && $httpsrdrctn_https_page != '') {
                        $httpsrdrctn_https_page = str_replace('https', 'http', $httpsrdrctn_https_page);
                        $httpsrdrctn_options['https_pages_array'][] = trim(str_replace(home_url(), '', $httpsrdrctn_https_page), '/');
                    }
                }
            }

            if ("" == $error) {
                // Update options in the database.
                update_option('httpsrdrctn_options', $httpsrdrctn_options, '', 'yes');
                $message = __("Settings saved.", 'https_redirection');
                $httpsrdrctn_obj = new EHSSL_Htaccess();
                $httpsrdrctn_obj->write_to_htaccess();

                // clear caching plugins cache if needed
                // WP Fastest Cache
                if (isset($GLOBALS["wp_fastest_cache"])) {
                    $wpfc = $GLOBALS["wp_fastest_cache"];
                    if (method_exists($wpfc, 'deleteCache') && is_callable(array($wpfc, 'deleteCache'))) {
                        $wpfc->deleteCache(true);
                    }
                }
                // httpsrdrctn_generate_htaccess();
            }
        }
        $siteSSLurl = get_home_url(null, '', 'https');
        ?>
        <div style="background: #fff6d5; border: 1px solid #d1b655; color: #3f2502; margin: 10px 0; padding: 5px 5px 5px 10px; text-shadow: 1px 1px #ffffff;">
            <p>
                <?php echo sprintf(__("When you enable the HTTPS redirection, the plugin will force redirect the URL to the HTTPS version of the URL. So before enabling this plugin's feature, visit your site's HTTPS URL %s to make sure the page loads correctly. Otherwise you may get locked out if your SSL certificate is not installed correctly on your site or the HTTPS URL is not working and this plugin is auto redirecting to the HTTPS URL.", 'https_redirection'), '<a href="' . $siteSSLurl . '" target="_blank">' . $siteSSLurl . '</a>'); ?>
            </p>
            <p>
                <span style='font-weight:bold;color:red;'><?php _e('Important!', 'https_redirection');?></span>
                <?php _e("If you're using caching plugins similar to W3 Total Cache or WP Super Cache, you need to clear their cache after you enable or disable automatic redirection option. Failing to do so may result in mixed content warning from browser.", 'https_redirection');?>
            </p>
        </div>
        <div class="postbox">
            <div class="inside">
                <?php
// Display form on the setting page.
        if (get_option('permalink_structure')) {
            // Pretty permalink is enabled. So allow HTTPS redirection feature.
            ?>
                <div id="httpsrdrctn_settings_notice" class="updated fade" style="display:none">
                    <p>
                        <strong><?php _e("Notice:", 'https_redirection');?></strong><?php _e("The plugin's settings have been changed. In order to save them please don't forget to click the 'Save Changes' button.", 'https_redirection');?>
                    </p>
                </div>
                <div class="updated fade" <?php if (!isset($_REQUEST['httpsrdrctn_form_submit']) || $error != "") {echo "style=\"display:none\"";}?>>
                    <p><strong><?php echo $message; ?></strong></p>
                </div>
                <div class="error" <?php if ("" == $error) {echo "style=\"display:none\"";}?>>
                    <p><?php echo $error; ?></p>
                </div>
                <form id="httpsrdrctn_settings_form" method="post" action="">
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><?php _e('Enable automatic redirection to the "HTTPS"', 'https_redirection');?></th>
                            <td>
                                <label>
                                    <input type="checkbox" id="httpsrdrctn-checkbox" name="httpsrdrctn_https" value="1" <?php if ('1' == $httpsrdrctn_options['https']) {echo "checked=\"checked\" ";}?> />
                                </label>
                                <br />
                                <p class="description"><?php _e("Use this option to make your webpage(s) load in HTTPS version only. If someone enters a non-https URL in the browser's address bar then the plugin will automatically redirect to the HTTPS version of that URL.", 'https_redirection');?></p>
                            </td>
                        </tr>
                    </table>

                    <div style="position: relative">
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Apply HTTPS redirection on:', 'https_redirection');?></th>
                                <td>
                                    <label><input type="radio" name="httpsrdrctn_https_domain" value="1" <?php if ('1' == $httpsrdrctn_options['https_domain']) {echo "checked=\"checked\" ";}?> /> <?php _e('The whole domain', 'https_redirection');?></label>
                                    <br />
                                    <label><input type="radio" name="httpsrdrctn_https_domain" value="0" <?php if ('0' == $httpsrdrctn_options['https_domain']) {echo "checked=\"checked\" ";}?> /> <?php _e('A few pages', 'https_redirection');?></label>
                                    <br />
                                    <?php foreach ($httpsrdrctn_options['https_pages_array'] as $https_page) {?>
                                        <span>
                                            <?php echo str_replace("http://", "https://", home_url()); ?>/<input type="text" name="httpsrdrctn_https_pages_array[]" value="<?php echo $https_page; ?>" /> <span class="rewrite_delete_item">&nbsp;</span> <span class="rewrite_item_blank_error"><?php _e('Please, fill field', 'list');?></span><br />
                                        </span>
                                    <?php }?>
                                    <span class="rewrite_new_item">
                                        <?php echo str_replace("http://", "https://", home_url()); ?>/<input type="text" name="httpsrdrctn_https_pages_array[]" value="" /> <span class="rewrite_add_item">&nbsp;</span> <span class="rewrite_item_blank_error"><?php _e('Please, fill field', 'list');?></span><br />
                                    </span>
                                </td>
                            </tr>

                        </table>
                        <style>
                            #httpsrdrctn-overlay {
                                position: absolute;
                                top: 10px;
                                background-color: white;
                                width: 100%;
                                height: 100%;
                                opacity: 0.5;
                                text-align: center;
                            }
                        </style>
                        <div id="httpsrdrctn-overlay" <?php echo ($httpsrdrctn_options['https'] == 1 ? ' class="hidden"' : ''); ?>></div>
                    </div>

                    <input type="hidden" name="httpsrdrctn_form_submit" value="submit" />

                    <p class="submit">
                        <input type="submit" class="button-primary" value="<?php _e('Save Changes')?>" />
                    </p>
                    <?php wp_nonce_field(plugin_basename(__FILE__), 'httpsrdrctn_nonce_name');?>
                </form>

                <script>
                    jQuery('input#httpsrdrctn-checkbox').change(function() {
                        if (jQuery(this).is(':checked')) {
                            jQuery('div#httpsrdrctn-overlay').fadeOut('fast');
                        } else {
                            jQuery('div#httpsrdrctn-overlay').fadeIn('fast');
                        }
                    });
                </script>

                <div style="background: #FFEBE8; border: 1px solid #CC0000; color: #333333; margin: 10px 0; padding: 5px 5px 5px 10px;">
                    <p><strong><?php _e("Notice:", 'https_redirection');?></strong> <?php _e("It is very important to be extremely attentive when making changes to .htaccess file.", 'https_redirection');?></p>
                    <p><?php _e('If after making changes your site stops functioning, do the following:', 'https_redirection');?></p>
                    <p><?php _e('Step #1: Open .htaccess file in the root directory of the WordPress install and delete everything between the following two lines', 'https_redirection');?></p>
                    <p style="border: 1px solid #ccc; padding: 10px;">
                        # BEGIN HTTPS Redirection Plugin<br />
                        # END HTTPS Redirection Plugin
                    </p>
                    <p><?php _e('Step #2: Save the htaccess file (this will erase any change this plugin made to that file).', 'https_redirection');?></p>
                    <p><?php _e("Step #3: Deactivate the plugin or rename this plugin's folder (which will deactivate the plugin).", 'https_redirection');?></p>

                    <p><?php _e('The changes will be applied immediately after saving the changes, if you are not sure - do not click the "Save changes" button.', 'https_redirection');?></p>
                </div>

                <?php } else {?>
                    <!-- pretty permalink is NOT enabled. This plugin can't work. -->
                    <div class="error">
                        <p><?php _e('HTTPS redirection only works if you have pretty permalinks enabled.', 'https_redirection');?></p>
                        <p><?php _e('To enable pretty permalinks go to <em>Settings > Permalinks</em> and select any option other than "default".', 'https_redirection');?></p>
                        <p><a href="options-permalink.php"><?php _e('Enable Permalinks', 'https_redirection');?></a></p>
                    </div>
                <?php }?>
            </div>
        </div>
    <?php
}
} // End class