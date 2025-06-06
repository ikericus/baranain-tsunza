<?php
namespace Nimble;
if ( !defined( 'ABSPATH' ) ) exit;


/* ------------------------------------------------------------------------- *
*  OPTION PAGE
/* ------------------------------------------------------------------------- */
require_once( NIMBLE_BASE_PATH . '/inc/admin/nb-options.php' );



/* ------------------------------------------------------------------------- *
*  SYSTEM INFO ( May 2020 => moved in a tab in the new options page )
/* ------------------------------------------------------------------------- */
add_action('admin_menu', '\Nimble\sek_plugin_menu');
function sek_plugin_menu() {
    if ( !current_user_can( 'update_plugins' ) || !sek_current_user_can_access_nb_ui() )
      return;
    add_plugins_page(__( 'System info', 'nimble-builder' ), __( 'System info', 'nimble-builder' ), 'read', 'nimble-builder', '\Nimble\sek_plugin_page');
}
add_action( 'admin_init' , '\Nimble\sek_redirect_system_info' );
function sek_redirect_system_info() {
    if ( isset( $_GET['page'] ) && 'nimble-builder' === sanitize_text_field($_GET['page']) ) {
        wp_safe_redirect( urldecode( admin_url( NIMBLE_OPTIONS_PAGE_URL . '&tab=system-info' ) ) );
        exit;
    }
}

/* ------------------------------------------------------------------------- *
*  VERSIONNING
/* ------------------------------------------------------------------------- */
add_action( 'plugins_loaded', '\Nimble\sek_versionning');
function sek_versionning() {
    $current_version = get_option( 'nimble_version' );
    if ( $current_version != NIMBLE_VERSION ) {
        update_option( 'nimble_version_upgraded_from', $current_version, 'no' );
        update_option( 'nimble_version', NIMBLE_VERSION );
    }
    $started_with = get_option( 'nimble_started_with_version' );
    if ( empty( $started_with ) ) {
        update_option( 'nimble_started_with_version', $current_version );
    }
    $start_date = get_option( 'nimble_start_date' );
    if ( empty( $start_date ) ) {
        update_option( 'nimble_start_date', date("Y-m-d H:i:s") );
    }
}
add_action( 'admin_init' , '\Nimble\sek_admin_style' );
function sek_admin_style() {
    if ( skp_is_customizing() || !sek_current_user_can_access_nb_ui() )
      return;
    wp_enqueue_style(
        'nimble-admin-css',
        sprintf(
            '%1$s/assets/admin/css/%2$s' ,
            NIMBLE_BASE_URL,
            'nimble-admin.css'
        ),
        array(),
        NIMBLE_ASSETS_VERSION,
        'all'
    );
}
/* beautify admin notice text using some defaults the_content filter callbacks */
foreach ( array( 'wptexturize', 'convert_smilies' ) as $callback ) {
    add_filter( 'nimble_parse_admin_text', $callback );
}
function sek_is_forbidden_post_type_for_nimble_edit_button( $post_type = '' ) {
    $post_type_obj = get_post_type_object( $post_type );
    $authorized_post_types = apply_filters( 'nimble-authorized-post-types' , array( 'post', 'page', 'product' ) );
    if ( is_string($post_type) && !in_array($post_type, $authorized_post_types) )
      return true;

    return is_object($post_type_obj) && true !== $post_type_obj->public;
}
add_action( 'edit_form_after_title', '\Nimble\sek_print_edit_with_nimble_btn_for_classic_editor' );
function sek_print_edit_with_nimble_btn_for_classic_editor( $post ) {
  if ( !sek_current_user_can_access_nb_ui() || !apply_filters('nb_post_edit_btn_enabled', true ) )
    return;
  if ( is_object($post) && sek_is_forbidden_post_type_for_nimble_edit_button( $post->post_type ) )
    return;
  $current_screen = get_current_screen();
  if ( 'post' !== $current_screen->base )
    return;
  if ( did_action( 'enqueue_block_editor_assets' ) ) {
    return;
  }
  if ( !sek_current_user_can_edit( $post->ID ) || !current_user_can( 'customize' ) ) {
    return;
  }
  sek_print_nb_btn_edit_with_nimble( 'classic' );
}
add_action( 'enqueue_block_editor_assets', '\Nimble\sek_enqueue_js_asset_for_gutenberg_edit_button');
function sek_enqueue_js_asset_for_gutenberg_edit_button() {
    if ( !sek_current_user_can_access_nb_ui() || !apply_filters('nb_post_edit_btn_enabled', true ) )
      return;
    $current_screen = get_current_screen();
    if ( 'post' !== $current_screen->base )
      return;
    $post = get_post();
    if ( !sek_current_user_can_edit( $post->ID ) || !current_user_can( 'customize' ) ) {
      return;
    }

    wp_enqueue_script(
      'nb-gutenberg',
      sprintf(
            '%1$s/assets/admin/js/%2$s' ,
            NIMBLE_BASE_URL,
            'nimble-gutenberg.js'
      ),
      array('jquery'),
      NIMBLE_ASSETS_VERSION,
      true
    );
}
add_action( 'admin_head', '\Nimble\sek_print_js_for_nimble_edit_btn', PHP_INT_MAX );
function sek_print_js_for_nimble_edit_btn() {
  if ( !sek_current_user_can_access_nb_ui() || !apply_filters('nb_post_edit_btn_enabled', true ) )
    return;
  $current_screen = get_current_screen();
  if ( 'post' !== $current_screen->base )
    return;

  $post = get_post();
  if ( is_object($post) && sek_is_forbidden_post_type_for_nimble_edit_button( $post->post_type ) )
    return;
  if ( !sek_current_user_can_edit( $post->ID ) || !current_user_can( 'customize' ) ) {
    return;
  }
  ?>
  <?php if ( did_action( 'enqueue_block_editor_assets' ) ) : ?>
    <?php // Only printed when Gutenberg editor is enabled 
    ?>
    <script id="sek-edit-with-nb" type="text/html">
      <?php sek_print_nb_btn_edit_with_nimble( 'gutenberg' ); ?>
    </script>
  <?php else : ?>
    <?php // Only printed when Gutenberg editor is NOT enabled 
    ob_start();
    ?>
      (function ($) {
          var _doRedirectToCustomizer = function( post_id, $clickedEl ) {
              wp.ajax.post( 'sek_get_customize_url_for_nimble_edit_button', {
                  nimble_edit_post_id : post_id
              }).done( function( resp ) {
                  window.location.href = resp;
              }).fail( function( resp ) {
                  $clickedEl.removeClass('sek-loading-customizer').addClass('button-primary');
                  _.delay(function () {
                      $( window ).off( 'beforeunload' );
                      location.href = location.href; //wp-admin/post.php?post=70&action=edit
                  }, 300 );
              });
          };
          $('body').on( 'click', '#sek-edit-with-nimble', function(evt) {
              evt.preventDefault();
              var $clickedEl = $(this),
                  _url = $clickedEl.data('cust-url');
              if ( _.isEmpty( _url ) ) {
                  $clickedEl.addClass('sek-loading-customizer').removeClass('button-primary');
                  var post_id = $('#post_ID').val();
                  _doRedirectToCustomizer( post_id, $clickedEl );
              } else {
                  window.location.href = _url;
              }
          });
      })(jQuery);
      <?php
      $script = ob_get_clean();
      wp_register_script( 'nb_print_js_for_nimble_edit_btn', '');
      wp_enqueue_script( 'nb_print_js_for_nimble_edit_btn' );
      wp_add_inline_script( 'nb_print_js_for_nimble_edit_btn', $script );
      ?>
  <?php endif; ?>
  <?php
}
function sek_print_nb_btn_edit_with_nimble( $editor_type ) {
    if ( !sek_current_user_can_access_nb_ui() || !apply_filters('nb_post_edit_btn_enabled', true ) )
      return;
    $post = get_post();
    $manually_built_skope_id = strtolower( NIMBLE_SKOPE_ID_PREFIX . 'post_' . $post->post_type . '_' . $post->ID );
    $customize_url = sek_get_customize_url_when_is_admin( $post );
    if ( !empty( $customize_url ) ) {
        $customize_url = add_query_arg(
            array( 'autofocus' => array( 'section' => '__content_picker__' ) ),
            $customize_url
        );
    }
    $btn_css_classes = 'classic' === $editor_type ? 'button button-primary button-hero classic-ed' : 'button button-primary button-large guten-ed';
    ?>
    <button id="sek-edit-with-nimble" type="button" class="<?php echo esc_attr($btn_css_classes); ?>" data-cust-url="<?php echo esc_url( $customize_url ); ?>">
      <?php //_e( 'Edit with Nimble Builder', 'text_doma' ); ?>
      <?php printf( '<span class="sek-spinner"></span><span class="sek-nimble-icon" title="%3$s"><img src="%1$s" alt="%2$s"/><span class="sek-nimble-admin-bar-title">%2$s</span><span class="sek-nimble-mobile-admin-bar-title">%3$s</span></span>',
          esc_url( NIMBLE_BASE_URL.'/assets/img/nimble/nimble_icon.svg?ver='.NIMBLE_VERSION ),
          apply_filters( 'nb_admin_nb_button_edit_title', sek_local_skope_has_been_customized( $manually_built_skope_id ) ? __('Continue building with Nimble','nimble-builder') : __('Build with Nimble Builder','nimble-builder'), $manually_built_skope_id ),
          __('Build','nimble-builder'),
          __('Build sections in live preview with Nimble Builder', 'nimble-builder')
      ); ?>
    </button>
    <?php
}
function sek_current_user_can_edit( $post_id = 0 ) {
    $post = get_post( $post_id );

    if ( !$post ) {
      return false;
    }
    if ( 'trash' === get_post_status( $post_id ) ) {
      return false;
    }
    $post_type_object = get_post_type_object( $post->post_type );

    if ( !isset( $post_type_object->cap->edit_post ) ) {
      return false;
    }
    $edit_cap = $post_type_object->cap->edit_post;
    if ( !current_user_can( $edit_cap, $post_id ) ) {
      return false;
    }
    if ( get_option( 'page_for_posts' ) === $post_id ) {
      return false;
    }
    return true;
}
add_filter( 'display_post_states', '\Nimble\sek_add_nimble_post_state', 10, 2 );
function sek_add_nimble_post_state( $post_states, $post ) {
    if ( skp_is_customizing() )
      return $post_states;
    if ( !sek_current_user_can_access_nb_ui() )
      return $post_states;
    $manually_built_skope_id = strtolower( NIMBLE_SKOPE_ID_PREFIX . 'post_' . $post->post_type . '_' . $post->ID );
    if ( $post && current_user_can( 'edit_post', $post->ID ) && sek_local_skope_has_been_customized( $manually_built_skope_id ) ) {
        $post_states['nimble'] = __( 'Nimble Builder', 'nimble-builder' );
    }
    return $post_states;
}
add_filter( 'post_row_actions', '\Nimble\sek_filter_post_row_actions', 11, 2 );
add_filter( 'page_row_actions', '\Nimble\sek_filter_post_row_actions', 11, 2 );
function sek_filter_post_row_actions( $actions, $post ) {
    if ( !sek_current_user_can_access_nb_ui() )
      return $actions;
    $manually_built_skope_id = strtolower( NIMBLE_SKOPE_ID_PREFIX . 'post_' . $post->post_type . '_' . $post->ID );
    if ( $post && current_user_can( 'edit_post', $post->ID ) && sek_local_skope_has_been_customized( $manually_built_skope_id ) ) {
        $actions['edit_with_nimble_builder'] = sprintf( '<a href="%1$s" title="%2$s">%2$s</a>',
            esc_url(sek_get_customize_url_for_post_id( $post->ID )),
            __( 'Edit with Nimble Builder', 'nimble-builder' )
        );
    }
    return $actions;
}
function sek_get_raw_html_from_skope_id( $skope_id = '' ) {
    $html = '';
    if ( empty( $skope_id ) )
      return $html;
    sek_register_modules_when_not_customizing_and_not_ajaxing( $skope_id );
    ob_start();
    foreach ( sek_get_locations() as $loc_id => $loc_params ) {
        $loc_params = is_array($loc_params) ? $loc_params : array();
        $loc_params = wp_parse_args( $loc_params, array('is_header_location' => false, 'is_footer_location' => false ) );
        if ( $loc_params['is_header_location'] || $loc_params['is_footer_location'] )
          continue;
        Nimble_Manager()->_render_seks_for_location( $loc_id, array(), $skope_id );
    }
    $html = ob_get_clean();
    $html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);
    $html = apply_filters( 'the_content', $html );
    $html = preg_replace('/>\s*</', '><', $html);//https://stackoverflow.com/questions/466437/minifying-html
    return $html;
}


add_action( 'wp_ajax_sek_get_nimble_content_for_seo_plugins', '\Nimble\sek_ajax_get_nimble_content_for_seo_plugins' );
function sek_ajax_get_nimble_content_for_seo_plugins() {
    if ( !is_user_logged_in() ) {
        wp_send_json_error( __FUNCTION__ . ' error => unauthenticated' );
    }
    if ( !isset( $_POST['skope_id'] ) || empty( $_POST['skope_id'] ) ) {
        wp_send_json_error( __FUNCTION__ . ' error => missing skope_id' );
    }
    $html = sek_get_raw_html_from_skope_id( sanitize_text_field($_POST['skope_id']) );
    wp_send_json_success($html);
}
add_action( 'admin_head', '\Nimble\sek_print_js_for_yoast_analysis', PHP_INT_MAX );
function sek_print_js_for_yoast_analysis() {
    if ( !defined( 'WPSEO_VERSION' ) )
      return;
    $current_screen = get_current_screen();
    if ( 'post' !== $current_screen->base )
      return;
    $post = get_post();
    $manually_built_skope_id = strtolower( NIMBLE_SKOPE_ID_PREFIX . 'post_' . $post->post_type . '_' . $post->ID );
    ob_start();
    ?>
        jQuery(function($){
            var NimblePlugin = function() {
                YoastSEO.app.registerPlugin( 'nimblePlugin', {status: 'loading'} );
                wp.ajax.post( 'sek_get_nimble_content_for_seo_plugins', {
                    skope_id : '<?php echo esc_attr($manually_built_skope_id); ?>'
                }).done( function( nimbleContent ) {
                    YoastSEO.app.pluginReady('nimblePlugin');
                    YoastSEO.app.registerModification( 'content', function(originalContent) { return originalContent + nimbleContent; }, 'nimblePlugin', 5 );
                }).fail( function( er ) {
                    console.log('NimblePlugin for Yoast => error when fetching Nimble content.');
                });
            }
            $(window).on('YoastSEO:ready', function() {
                try { new NimblePlugin(); } catch(er){ console.log('Yoast NimblePlugin error', er );}
            });
        });
    <?php
    $script = ob_get_clean();
    wp_register_script( 'nb_yoast_compat', '');
    wp_enqueue_script( 'nb_yoast_compat' );
    wp_add_inline_script( 'nb_yoast_compat', $script );
}
add_filter('seopress_content_analysis_content', '\Nimble\sek_add_content_to_seopress_analyser', 10, 2);
function sek_add_content_to_seopress_analyser($content, $id) {
    $post = get_post($id);
    if ( is_wp_error($post) || !$post || !is_object($post) )
      return $content;

    $manually_built_skope_id = strtolower( NIMBLE_SKOPE_ID_PREFIX . 'post_' . $post->post_type . '_' . $post->ID );
    $nb_content = sek_get_raw_html_from_skope_id( $manually_built_skope_id );
    return is_string($nb_content) ? $content.$nb_content : $content;
}
add_action( 'admin_init' , '\Nimble\sek_enqueue_js_for_rank_math_analyser' );
function sek_enqueue_js_for_rank_math_analyser() {
    if ( !defined( 'RANK_MATH_VERSION' ) )
      return;
    wp_enqueue_script(
      'nb-rank-math-integration',
      sprintf(
            '%1$s/assets/admin/js/%2$s' ,
            NIMBLE_BASE_URL,
            'nimble-rank-seo-analyzer.js'
      ),
      [ 'wp-hooks', 'rank-math-analyzer' ],
      NIMBLE_ASSETS_VERSION,
      true
    );
}
add_action( 'admin_head', '\Nimble\sek_print_js_for_rank_math_analyser', PHP_INT_MAX );
function sek_print_js_for_rank_math_analyser() {
    if ( !defined( 'RANK_MATH_VERSION' ) )
      return;
    $current_screen = get_current_screen();
    if ( 'post' !== $current_screen->base )
      return;

    $post = get_post();
    $manually_built_skope_id = strtolower( NIMBLE_SKOPE_ID_PREFIX . 'post_' . $post->post_type . '_' . $post->ID );
    ob_start();
    ?>
        jQuery(function($){
            window.nb_skope_id_for_rank_math_seo = '<?php echo esc_attr($manually_built_skope_id); ?>';
            $(document).trigger('nb-skope-id-ready.rank-math', { skope_id : '<?php echo esc_attr($manually_built_skope_id); ?>' } );
        });
    <?php
    $script = ob_get_clean();
    wp_register_script( 'nb_rank_math_analyzer_js', '');
    wp_enqueue_script( 'nb_rank_math_analyzer_js' );
    wp_add_inline_script( 'nb_rank_math_analyzer_js', $script );
}
add_action( 'wp_dashboard_setup', '\Nimble\sek_register_dashboard_widgets' );
function sek_register_dashboard_widgets() {
    if ( !sek_current_user_can_access_nb_ui() )
      return;
    if ( !sek_is_pro() && !(defined('NIMBLE_DEV') && NIMBLE_DEV ) && 'eligible' === sek_get_feedback_notif_status() && !sek_feedback_notice_is_dismissed() )
      return;

    $theme_name = sek_get_parent_theme_slug();
    $title = __( 'Nimble Builder Overview', 'nimble-builder' );
    wp_add_dashboard_widget(
        'presscustomizr-dashboard',
        !sek_is_presscustomizr_theme( $theme_name ) ? $title : sprintf( __( 'Nimble Builder & %s Overview', 'nimble-builder' ), ucfirst($theme_name) ),
        '\Nimble\sek_nimble_dashboard_callback_fn'
    );

    global $wp_meta_boxes;
    $dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
    $nimble_widget = array( 'presscustomizr-dashboard' => $dashboard['presscustomizr-dashboard'] );
    $wp_meta_boxes['dashboard']['normal']['core'] = array_merge( $nimble_widget, $dashboard );
}
function sek_nimble_dashboard_callback_fn() {
    $post_data = sek_get_latest_posts_api_data();
    $theme_name = sek_get_parent_theme_slug();
    ?>
    <div class="nimble-db-wrapper">
      <div class="nimble-db-header">
        <div class="nimble-logo-version">
          <div class="nimble-logo"><div class="sek-nimble-icon" title="<?php _e('Add sections in live preview with Nimble Builder', 'nimble-builder' );?>"><img src="<?php echo esc_url(NIMBLE_BASE_URL.'/assets/img/nimble/nimble_icon.svg?ver='.NIMBLE_VERSION); ?>" alt="Nimble Builder"></div></div>
          <div class="nimble-version">
            <span class="nimble-version-text"><?php _e('Nimble Builder', 'nimble-builder'); ?> v<?php echo esc_attr(NIMBLE_VERSION); ?></span>
            <?php if ( sek_is_presscustomizr_theme( $theme_name ) ) : ?>
              <?php
                $theme_data = wp_get_theme();
                printf('<span class="nimble-version-text"> + %1$s theme v%2$s</span>', ucfirst($theme_name), $theme_data->version );
              ?>
            <?php endif; ?>
          </div>
        </div>
        <?php if ( sek_is_upsell_enabled() ) : ?>
          <?php printf( '<a class="sek-pro-link-in-dashboard" href="https://presscustomizr.com/nimble-builder-pro/" rel="noopener noreferrer" title="Go Pro" target="_blank">%1$s <span class="dashicons dashicons-external"></span></a>',
                __('Go Pro', 'nimble-builder')
              );
          ?>
        <?php else : ?>
          <?php printf( '<a href="%1$s" class="button button-primary button-hero"><span class="dashicons dashicons-admin-appearance"></span> %2$s</a>',
            esc_url( add_query_arg(
                array(
                  array( 'autofocus' => array( 'section' => '__content_picker__' ) ),
                  'return' => urlencode( remove_query_arg( wp_removable_query_args(), wp_unslash( $_SERVER['REQUEST_URI'] ) ) )
                ),
                admin_url( 'customize.php' )
            ) ),
            __( 'Start building', 'nimble-builder' )
          ); ?>
        <?php endif; ?>
      </div>
      <?php if ( !empty( $post_data ) ) : ?>
        <div class="nimble-post-list">
          <h3 class="nimble-post-list-title"><?php _e( 'News & release notes', 'nimble-builder' ); ?></h3>
          <ul class="nimble-collection">
            <?php foreach ( $post_data as $single_post_data ) : ?>
              <li class="nimble-single-post">
                <a href="<?php echo esc_url( $single_post_data['url'] ); ?>" class="nimble-single-post-link" target="_blank">
                  <?php echo esc_html( $single_post_data['title'] ); ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
      <?php
        $footer_links = array(
          'doc' => array(
            'title' => __( 'Everything about Nimble Builder', 'nimble-builder' ),
            'link' => 'https://docs.presscustomizr.com/article/337-getting-started-with-the-nimble-builder-plugin/?ref=a&amp;utm_source=usersite&amp;utm_medium=link&amp;utm_campaign=dashboard',
          ),
        );
        $start_msg_array = array();
        $theme_name = sek_get_parent_theme_slug();

        if ( sek_is_presscustomizr_theme( $theme_name ) ) {
            $start_msg = sek_start_msg_from_api( $theme_name );
            if ( !empty( $start_msg ) ) {
              $start_msg_array = array(
                'start_msg' => array(
                  'html' => $start_msg,
                ),
              );
            }
        }
        $footer_links = array_merge($footer_links,$start_msg_array);
      ?>
      <div class="nimble-db-footer">
          <?php foreach ( $footer_links as $link_id => $link_data ) : ?>
            <div class="nimble-footer-link-<?php echo esc_attr( $link_id ); ?>">
              <?php if ( !empty( $link_data['html'] ) ) : ?>
                <?php echo esc_attr($link_data['html']); ?>
              <?php else : ?>
              <a href="<?php echo esc_attr( $link_data['link'] ); ?>" target="_blank"><?php echo esc_html( $link_data['title'] ); ?> <span class="screen-reader-text"><?php _e( '(opens in a new window)', 'nimble-builder' ); ?></span></a><span aria-hidden="true" class="dashicons dashicons-external"></span>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>

      </div>
    </div>
    <?php
}






/* ------------------------------------------------------------------------- *
*  UPDATE NOTIFICATIONS
/* ------------------------------------------------------------------------- */
add_action( 'admin_notices'                         , '\Nimble\sek_may_be_display_update_notice');
add_action( 'wp_ajax_dismiss_nimble_update_notice'  ,  '\Nimble\sek_dismiss_update_notice_action' );
foreach ( array( 'wptexturize', 'convert_smilies', 'wpautop') as $callback ) {
  if ( function_exists( $callback ) )
      add_filter( 'sek_update_notice', $callback );
}


/**
* @hook : admin_notices
*/
function sek_may_be_display_update_notice() {
    if ( defined('NIMBLE_SHOW_UPDATE_NOTICE_FOR_VERSION') && NIMBLE_SHOW_UPDATE_NOTICE_FOR_VERSION !== NIMBLE_VERSION )
      return;

    if ( !sek_current_user_can_access_nb_ui() )
      return;
    if ( !sek_welcome_notice_is_dismissed() )
      return;

    $last_update_notice_values  = get_option( 'nimble_last_update_notice' );
    $show_new_notice = false;
    $display_ct = 5;

    if ( !$last_update_notice_values || !is_array($last_update_notice_values) ) {
        $last_update_notice_values = array( "version" => NIMBLE_VERSION, "display_count" => 0 );
        update_option( 'nimble_last_update_notice', $last_update_notice_values, 'no' );
        if ( sek_user_started_before_version( NIMBLE_VERSION ) ) {
            $show_new_notice = true;
        }
    }

    $_db_version          = $last_update_notice_values["version"];
    $_db_displayed_count  = $last_update_notice_values["display_count"];
    if ( version_compare( NIMBLE_VERSION, $_db_version , '>' ) ) {
        if ( $_db_displayed_count < $display_ct ) {
            $show_new_notice = true;
            (int) $_db_displayed_count++;
            $last_update_notice_values["display_count"] = $_db_displayed_count;
            update_option( 'nimble_last_update_notice', $last_update_notice_values, 'no' );
        }
        else {
            $new_val  = array( "version" => NIMBLE_VERSION, "display_count" => 0 );
            update_option('nimble_last_update_notice', $new_val, 'no' );
        }//end else
    }//end if

    if ( !$show_new_notice )
      return;

    ob_start();
      ?>
      <div class="updated czr-update-notice" style="position:relative;">
        <?php
          printf('<h3>%1$s %2$s %3$s %4$s :D</h3>',
              __( "Thanks, you successfully upgraded", 'nimble-builder'),
              'Nimble Builder',
              __( "to version", 'nimble-builder'),
              esc_attr(NIMBLE_VERSION)
          );
        ?>
        <?php
          printf( '<h4>%1$s <a class="" href="%2$s" title="%3$s" target="_blank">%3$s &raquo;</a></h4>',
              '',//__( "Let us introduce the new features we've been working on.", 'text_doma'),
              esc_url(NIMBLE_RELEASE_NOTE_URL),
              __( "Read the detailled release notes" , 'nimble-builder' )
          );
        ?>
        <p style="text-align:right;position: absolute;font-size: 1.1em;<?php echo is_rtl() ? 'left' : 'right';?>: 7px;bottom: -6px;">
        <?php printf('<a href="#" title="%1$s" class="nimble-dismiss-update-notice"> ( %1$s <strong>X</strong> ) </a>',
            __('close' , 'nimble-builder')
          );
        ?>
        </p>
        <!-- <p>
          <?php
          ?>
        </p> -->
      </div>
      <?php
      $_html = ob_get_clean();
      echo wp_kses_post( apply_filters( 'sek_update_notice', $_html ) );
      
      ob_start();
      ?>
        ( function($){
          var _ajax_action = function( $_el ) {
              var AjaxUrl = "<?php echo admin_url( 'admin-ajax.php' ); ?>",
                  _query  = {
                      action  : 'dismiss_nimble_update_notice',
                      dismissUpdateNoticeNonce :  "<?php echo wp_create_nonce( 'dismiss-update-notice-nonce' ); ?>"
                  },
                  $ = jQuery,
                  request = $.post( AjaxUrl, _query );

              request.fail( function ( response ) {});
              request.done( function( response ) {
                if ( '0' === response )
                  return;
                if ( '-1' === response )
                  return;

                $_el.closest('.updated').slideToggle('fast');
              });
          };//end of fn
          $( function($) {
            $('.nimble-dismiss-update-notice').on('click', function( e ) {
              e.preventDefault();
              _ajax_action( $(this) );
            } );
          } );

        })( jQuery );
      <?php
      $script = ob_get_clean();
      wp_register_script( 'nb_update_notice_js', '');
      wp_enqueue_script( 'nb_update_notice_js' );
      wp_add_inline_script( 'nb_update_notice_js', $script );
}


/**
* hook : wp_ajax_dismiss_nimble_update_notice
* => sets the last_update_notice to the current Nimble version when user click on dismiss notice link
*/
function sek_dismiss_update_notice_action() {
    check_ajax_referer( 'dismiss-update-notice-nonce', 'dismissUpdateNoticeNonce' );
    $new_val  = array( "version" => NIMBLE_VERSION, "display_count" => 0 );
    update_option( 'nimble_last_update_notice', $new_val, 'no' );
    wp_die( 1 );
}
















/* ------------------------------------------------------------------------- *
*  FEEDBACK NOTICE
/* ------------------------------------------------------------------------- */
function sek_feedback_notice_is_dismissed() {
  $dismissed = get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true );
  $dismissed_array = array_filter( explode( ',', (string) $dismissed ) );
  if ( defined('NIMBLE_DEV') && NIMBLE_DEV )
    return false;
  return in_array( NIMBLE_FEEDBACK_NOTICE_ID, $dismissed_array );
}


add_action( 'admin_notices'                         , '\Nimble\sek_maybe_display_feedback_notice');
foreach ( array( 'wptexturize', 'convert_smilies') as $callback ) {
  if ( function_exists( $callback ) )
      add_filter( 'sek_feedback_notice', $callback );
}
add_action( 'admin_head', function() {
  if ( !( defined('NIMBLE_DEV') && NIMBLE_DEV ) && sek_is_pro() )
    return;
  if ( 'eligible' !== sek_get_feedback_notif_status() )
    return;
  if ( !current_user_can( 'customize' ) )
    return;
  if ( !sek_current_user_can_access_nb_ui() )
    return;
  if ( sek_feedback_notice_is_dismissed() )
    return;
  ob_start();
  ?>
    jQuery( function( $ ) {
      var $optionGenMenu = $('#adminmenu').find('#menu-settings');
      if ( $optionGenMenu.length < 1 )
        return;
      var $settingsTitle = $optionGenMenu.find('a[href="options-general.php"]').first(),
          $nbTitle = $optionGenMenu.find('a[href="options-general.php?page=nb-options"]').first(),
          noticeHtml = ' <span class="nb-wp-menu-notif"><span class="update-count">1</span></span>';
      if ( $settingsTitle.length > 0 ) {
        $settingsTitle.find('.wp-menu-name').append(noticeHtml);
      }
      if ( $nbTitle.length > 0 ) {
        $nbTitle.append(noticeHtml);
      }
    } );
  <?php
  $script = ob_get_clean();
  wp_register_script( 'nb_feedback_notice_js', '');
  wp_enqueue_script( 'nb_feedback_notice_js' );
  wp_add_inline_script( 'nb_feedback_notice_js', $script );
  $current_screen = get_current_screen();
  if( 'settings_page_nb-options' !== $current_screen->base )
    return;
  
  $notice_id = NIMBLE_FEEDBACK_NOTICE_ID;
  ob_start();
  ?>
    jQuery( function( $ ) {
      $( <?php echo wp_json_encode( "#$notice_id" ); ?> ).on( 'click', '.notice-dismiss', function() {
        $(this).closest('.is-dismissible').slideUp('fast');//<= this line is not mandatory since WP has its own way to remove the is-dismissible block
        $.post( ajaxurl, {
          pointer: <?php echo wp_json_encode( $notice_id ); ?>,
          action: 'dismiss-wp-pointer'
        } );
        var $optionGenMenu = $('#adminmenu').find('#menu-settings');
        if ( $optionGenMenu.length > 0 ) {
          $optionGenMenu.find('.nb-wp-menu-notif').hide();
        }
      } );
    } );
  <?php
  $script = ob_get_clean();
  wp_register_script( 'nb_feedback_other_notice_js', '');
  wp_enqueue_script( 'nb_feedback_other_notice_js' );
  wp_add_inline_script( 'nb_feedback_other_notice_js', $script );
}, PHP_INT_MAX);



/**
* @hook : admin_notices
*/
function sek_maybe_display_feedback_notice() {
  if ( !( defined('NIMBLE_DEV') && NIMBLE_DEV ) && sek_is_pro() )
    return;
  if ( 'eligible' !== sek_get_feedback_notif_status() )
    return;
  if ( !current_user_can( 'customize' ) )
    return;
  if ( !sek_current_user_can_access_nb_ui() )
    return;
  if ( sek_feedback_notice_is_dismissed() )
    return;
  $current_screen = get_current_screen();
  if( 'settings_page_nb-options' !== $current_screen->base )
    return;

    $notice_id = NIMBLE_FEEDBACK_NOTICE_ID;
    ob_start();
    ?>
    <div class="notice notice-success is-dismissible" id="<?php echo esc_attr( $notice_id ); ?>">
      <h3><span class="nb-wp-menu-notif"><span class="update-count">1</span></span> <?php _e('Hi👋 ! A quick note on Nimble Builder Pro', 'nimble-builder'); ?> </h3>
      <div class="nimble-logo-feedback-notice">
        <div class="nimble-logo"><div class="sek-nimble-icon"><img src="<?php echo esc_url(NIMBLE_BASE_URL.'/assets/img/nimble/nimble_icon.svg?ver='.NIMBLE_VERSION); ?>" alt="Nimble Builder"></div></div>
        <div class="nimble-feedback">
          
          <p><?php
            printf( __('If you enjoy using Nimble Builder for your website, you are going to love %1$s. The pro version has a friendly price and includes %2$s, with no impact on performance. <br/>Additionally, our premium support will be there to help you resolve any issues you may have with the plugin. ', 'nimble-builder' ),
              sprintf( '<a href="https://presscustomizr.com/nimble-builder-pro/" target="_blank" rel="noopener noreferrer">%1$s</a>', __( 'Nimble Builder Pro', 'nimble-builder' ) ),
              sprintf( '<a href="https://presscustomizr.com/nimble-builder-pro/#features" target="_blank" rel="noopener noreferrer">%1$s</a>', __( 'many additional features', 'nimble-builder' ) )
            );
          ?>
          </p>
          
        </div>
      </div>
      <p style="font-size:14px;font-weight:600"><?php _e('Thank you 🙏 ! Nimble Builder needs your sponsorship to keep improving and helping you design your website in the best possible way.', 'nimble-builder' ); ?></p>
      <!-- upsell message location -->
      <button type="button" class="notice-dismiss" title="<?php _e('Dismiss this notice.', 'nimble-builder'); ?>">
        <span class="screen-reader-text"><?php _e('Dismiss this notice.', 'nimble-builder'); ?></span>
      </button>
    </div>
    <?php
      $_html = ob_get_clean();
      echo wp_kses_post(apply_filters( 'sek_feedback_notice', $_html ));
    ?>
    <?php
}
function sek_welcome_notice_is_dismissed() {
    $dismissed = get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true );
    $dismissed_array = array_filter( explode( ',', (string) $dismissed ) );
    if ( defined('NIMBLE_DEV') && NIMBLE_DEV )
      return false;
    return in_array( NIMBLE_WELCOME_NOTICE_ID, $dismissed_array );
}

add_action( 'admin_notices', '\Nimble\sek_render_welcome_notice' );
function sek_render_welcome_notice() {
    if ( sek_is_pro() )
      return;
    if ( !current_user_can( 'customize' ) )
      return;
    if ( !sek_current_user_can_access_nb_ui() )
      return;
    if ( sek_welcome_notice_is_dismissed() )
      return;
    if ( isset($_GET['page']) && NIMBLE_OPTIONS_PAGE === sanitize_text_field($_GET['page']) )
      return;
    $current_screen = get_current_screen();
    if( in_array( $current_screen->base, array(
        'site-health',
        'tools',
        'import',
        'export',
        'export-personal-data',
        'erase-personal-data',
        'options-general',
        'options-writing',
        'options-reading',
        'options-discussion',
        'options-media',
        'options-permalinks',
        'options-privacy',
      ) ) )
      return;
    if ( sek_site_has_nimble_sections_created() && !( defined('NIMBLE_DEV') && NIMBLE_DEV ) ) {
        $dismissed = get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true );
        $dismissed_array = array_filter( explode( ',', (string) $dismissed ) );
        if ( !in_array(NIMBLE_WELCOME_NOTICE_ID, $dismissed_array) ) {
            $dismissed_array[] = NIMBLE_WELCOME_NOTICE_ID;
        }
        $dismissed = implode( ',', $dismissed_array );
        update_user_meta( get_current_user_id(), 'dismissed_wp_pointers', $dismissed );
        return;
    }
    $notice_id = NIMBLE_WELCOME_NOTICE_ID;
    ?>
    <div class="nimble-welcome-notice notice notice-info is-dismissible" id="<?php echo esc_attr( $notice_id ); ?>">
      <?php sek_get_welcome_block(); ?>
    </div>

    <?php ob_start(); ?>
    jQuery( function( $ ) {
      $( <?php echo wp_json_encode( "#$notice_id" ); ?> ).on( 'click', '.notice-dismiss', function() {
        $(this).closest('.is-dismissible').slideUp('fast');//<= this line is not mandatory since WP has its own way to remove the is-dismissible block
        $.post( ajaxurl, {
          pointer: <?php echo wp_json_encode( $notice_id ); ?>,
          action: 'dismiss-wp-pointer'
        } );
      } );
    } );
    <?php
    $script = ob_get_clean();
    wp_register_script( 'nb_welcome_notice', '');
    wp_enqueue_script( 'nb_welcome_notice' );
    wp_add_inline_script( 'nb_welcome_notice', $script );
}
function sek_get_welcome_block() {
  ?>
  <div class="nimble-welcome-icon-holder">
    <img class="nimble-welcome-icon" src="<?php echo esc_url(NIMBLE_BASE_URL.'/assets/img/nimble/nimble_banner.svg?ver='.NIMBLE_VERSION); ?>" alt="<?php esc_html_e( 'Nimble Builder', 'nimble-builder' ); ?>" />
  </div>
  <div class="nimble-welcome-content">
    <h1><?php echo apply_filters( 'nimble_parse_admin_text', __('Welcome to Nimble Builder for WordPress :D', 'nimble-builder' ) ); ?></h1>

    <p><?php _e( 'Nimble allows you to drag and drop content modules, or pre-built section templates, into <u>any context</u> of your site, including search results or 404 pages. You can edit your pages in <i>real time</i> from the live customizer, and then publish when you are happy of the result, or save for later.', 'nimble-builder' ); ?></p>
    <p><?php _e( 'The plugin automatically creates fluid and responsive sections for a pixel-perfect rendering on smartphones and tablets, without the need to add complex code.', 'nimble-builder' ); ?></p>
    <?php printf( '<a href="%1$s" target="_blank" class="button button-primary button-hero"><span class="dashicons dashicons-admin-appearance"></span> %2$s</a>',
        esc_url( add_query_arg(
            array(
              array( 'autofocus' => array( 'section' => '__content_picker__' ) ),
              'return' => urlencode( remove_query_arg( wp_removable_query_args(), wp_unslash( $_SERVER['REQUEST_URI'] ) ) )
            ),
            admin_url( 'customize.php' )
        ) ),
        __( 'Start creating content in live preview', 'nimble-builder' )
    ); ?>
    <div class="nimble-link-to-doc">
      <?php printf( '<div class="nimble-doc-link-wrap">%1$s <a href="%2$s" target="_blank" class="">%3$s</a>.</div>',
          __('Or', 'nimble-builder'),
          add_query_arg(
              array(
                'utm_source' => 'usersite',
                'utm_medium' => 'link',
                'utm_campaign' => 'nimble-welcome-notice'
              ),
              'https://docs.presscustomizr.com/article/337-getting-started-with-the-nimble-builder-plugin'
          ),
          __( 'read the getting started guide', 'nimble-builder' )
      ); ?>
    </div>
  </div>

  <?php
}













/* ------------------------------------------------------------------------- *
*  Review link in plugin list table
*  Nov 2020 for https://github.com/presscustomizr/nimble-builder/issues/701
/* ------------------------------------------------------------------------- */
/**
 * Filters the array of row meta for each plugin in the Plugins list table.
 * @param string[] $plugin_meta An array of the plugin's metadata, including
 *                              the version, author, author URI, and plugin URI.
 * @param string   $plugin_file Path to the plugin file relative to the plugins directory.
 * @param array    $plugin_data An array of plugin data.
 * @param string   $status      Status filter currently applied to the plugin list. Possible
 *                              values are: 'all', 'active', 'inactive', 'recently_activated',
 *                              'upgrade', 'mustuse', 'dropins', 'search', 'paused',
 *                              'auto-update-enabled', 'auto-update-disabled'.
 */
add_filter( 'plugin_row_meta', function($plugin_meta, $plugin_file, $plugin_data, $status) {
    if ( false !== strpos($plugin_file, 'nimble-builder.php') ) {
        $is_pro_installed = false;
        $pro_slug = 'nimble-builder-pro/nimble-builder-pro.php';
        $installed_plugins = get_plugins();
        $is_pro_installed = array_key_exists( $pro_slug, $installed_plugins ) || in_array( $pro_slug, $installed_plugins, true );

        if ( sek_is_dev_mode() || !$is_pro_installed ) {
            $plugin_meta = is_array($plugin_meta) ? $plugin_meta : [];
            if ( sek_is_upsell_enabled() ) {
              $plugin_meta[] = sprintf( '<a class="sek-pro-link-in-plugins" href="https://presscustomizr.com/nimble-builder-pro/" rel="noopener noreferrer" title="Go Pro" target="_blank">%1$s <span class="dashicons dashicons-external"></span></a>',
                __('Go Pro', 'nimble-builder')
              );
            } else {
              $plugin_meta[] = sprintf(
                '<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s %3$s</a>',
                'https://wordpress.org/support/plugin/nimble-builder/reviews/?filter=5/#new-post',
                __( 'Enjoying Nimble Builder ? Share a review', 'nimble-builder' ),
                '<span style="color:#ffb900;font-size: 12px;">&#9733;&#9733;&#9733;&#9733;&#9733;</span>'
              );
            }
        }
    }
    return $plugin_meta;
},100,4);
