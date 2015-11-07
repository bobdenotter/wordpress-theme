<?php

function wpPrintParameters($parameters = array())
{
    if (empty($parameters)) {
        return;
    }

    $res = [];

    foreach($parameters as $parameter) {

        if (is_array($parameter)) {
            $res[] = " [ " . wpPrintParameters($parameter) . " ] ";
        } else {
            $res[] = sprintf("<tt>&quot;%s&quot;</tt>", htmlspecialchars((string) $parameter));
        }
    }

    return implode(", ", $res);
}

/**
 * Stub for edit_post_link.
 *
 */
function wpStub($functionname, $arguments)
{
    global $markCssOutputted;
    $arguments = wpPrintParameters($arguments);

    if (!$markCssOutputted) {
        echo "<style>mark { background-color: #fff9c0; text-decoration: none; border: 1px solid #DDB; padding: 1px 3px; display: inline-block; font-size: 13px; } </style>";
        $markCssOutputted = true;
    }

    echo " <mark>{$functionname}({$arguments})</mark> ";
}


function get_header()
{
    require_once('header.php');
}


function have_posts()
{

    return (!empty($GLOBALS['content']));

}

function the_post()
{
    dump($GLOBALS['content']);

    $GLOBALS['content'] = null;

}



function the_title()
{
    global $record;
    // dump($GLOBALS['content']);

    // $GLOBALS['content'] = null;

    return $record->title();

}


function get_footer()
{
    require_once('footer.php');
}

function bloginfo()
{
    global $content, $config;

    echo $config->get('general/sitename');
}

function esc_url($str)
{
    echo $str;
}

function get_template_directory_uri()
{
    echo "[get_template_directory_uri]";
}


function wp_head()
{
    require_once('wp_head.php');
}

function body_class()
{
    echo "[body_class]";

}

function is_front_page()
{
    global $request;

    // dump($request->get('_route'));

    if ($request->get('_route') == 'wp-homepage') {
        return true;
    } else {
        return false;
    }

}

function is_home()
{
    return is_front_page();
}

function home_url()
{
    global $paths;

    return $paths['rooturl'];

}

function get_bloginfo($show = '', $filter = 'raw')
{
    global $content, $config;

        switch( $show ) {
                case 'home' : // DEPRECATED
                case 'siteurl' : // DEPRECATED
                        _deprecated_argument( __FUNCTION__, '2.2', sprintf(
                                /* translators: 1: 'siteurl'/'home' argument, 2: bloginfo() function name, 3: 'url' argument */
                                __( 'The %1$s option is deprecated for the family of %2$s functions. Use the %3$s option instead.' ),
                                '<code>' . $show . '</code>',
                                '<code>bloginfo()</code>',
                                '<code>url</code>'
                        ) );
                case 'url' :
                        $output = home_url();
                        break;
                case 'wpurl' :
                        $output = site_url();
                        break;
                case 'description':
                        $output = $config->get('general/payoff');
                        break;
                case 'rdf_url':
                        $output = get_feed_link('rdf');
                        break;
                case 'rss_url':
                        $output = get_feed_link('rss');
                        break;
                case 'rss2_url':
                        $output = get_feed_link('rss2');
                        break;
                case 'atom_url':
                        $output = get_feed_link('atom');
                        break;
                case 'comments_atom_url':
                        $output = get_feed_link('comments_atom');
                        break;
                case 'comments_rss2_url':
                        $output = get_feed_link('comments_rss2');
                        break;
                case 'pingback_url':
                        $output = site_url( 'xmlrpc.php' );
                        break;
                case 'stylesheet_url':
                        $output = get_stylesheet_uri();
                        break;
                case 'stylesheet_directory':
                        $output = get_stylesheet_directory_uri();
                        break;
                case 'template_directory':
                case 'template_url':
                        $output = get_template_directory_uri();
                        break;
                case 'admin_email':
                        $output = get_option('admin_email');
                        break;
                case 'charset':
                        $output = get_option('blog_charset');
                        if ('' == $output) $output = 'UTF-8';
                        break;
                case 'html_type' :
                        $output = get_option('html_type');
                        break;
                case 'version':
                        global $wp_version;
                        $output = $wp_version;
                        break;
                case 'language':
                        $output = get_locale();
                        $output = str_replace('_', '-', $output);
                        break;
                case 'text_direction':
                        _deprecated_argument( __FUNCTION__, '2.2', sprintf(
                                /* translators: 1: 'text_direction' argument, 2: bloginfo() function name, 3: is_rtl() function name */
                                __( 'The %1$s option is deprecated for the family of %2$s functions. Use the %3$s function instead.' ),
                                '<code>' . $show . '</code>',
                                '<code>bloginfo()</code>',
                                '<code>is_rtl()</code>'
                        ) );
                        if ( function_exists( 'is_rtl' ) ) {
                                $output = is_rtl() ? 'rtl' : 'ltr';
                        } else {
                                $output = 'ltr';
                        }
                        break;
                case 'name':
                default:
                        $output = get_option('blogname');
                        break;
        }

        $url = true;
        if (strpos($show, 'url') === false &&
                strpos($show, 'directory') === false &&
                strpos($show, 'home') === false)
                $url = false;

        if ( 'display' == $filter ) {
                if ( $url ) {
                        /**
                         * Filter the URL returned by get_bloginfo().
                         *
                         * @since 2.0.5
                         *
                         * @param mixed $output The URL returned by bloginfo().
                         * @param mixed $show   Type of information requested.
                         */
                        $output = apply_filters( 'bloginfo_url', $output, $show );
                } else {
                        /**
                         * Filter the site information returned by get_bloginfo().
                         *
                         * @since 0.71
                         *
                         * @param mixed $output The requested non-URL site information.
                         * @param mixed $show   Type of information requested.
                         */
                        $output = apply_filters( 'bloginfo', $output, $show );
                }
        }

        return $output;
}

function apply_filters($filter, $output, $show)
{
    return $output;
}

function is_customize_preview()
{
    return false;
}

function get_sidebar()
{
    require('sidebar.php');

}


function get_template_part($slug)
{
    if (is_readable($slug . '.php')) {
        return include($slug . '.php');
    } else {
        return "[get_template_part]";
    }

}

function get_template_directory()
{
    global $paths;
    return $paths['themepath'];
}


function post_password_required()
{
    return false;
}


function is_attachment()
{
    return false;
}

/**
 * Stub for add_action.
 */
function add_action()
{

    // @todo: Do something with this.

    // echo wpStub('add_action', func_get_args());
}

/**
 * Stub for add_filter.
 */
function add_filter()
{
    // echo wpStub('add_filter', func_get_args());
}


// ------ Here be unconverted stubs. --------




/**
 * Stub for is_admin.
 */
function is_admin()
{
    echo wpStub('is_admin', func_get_args());
}

/**
 * Stub for language_attributes.
 */
function language_attributes()
{
    echo wpStub('language_attributes', func_get_args());
}

/**
 * Stub for _e.
 */
function _e()
{
    echo wpStub('_e', func_get_args());
}

/**
 * Stub for has_nav_menu.
 */
function has_nav_menu()
{
    echo wpStub('has_nav_menu', func_get_args());
}

/**
 * Stub for wp_nav_menu.
 */
function wp_nav_menu()
{
    echo wpStub('wp_nav_menu', func_get_args());
}

/**
 * Stub for is_active_sidebar.
 */
function is_active_sidebar()
{
    echo wpStub('is_active_sidebar', func_get_args());
}

/**
 * Stub for dynamic_sidebar.
 */
function dynamic_sidebar()
{
    echo wpStub('dynamic_sidebar', func_get_args());
}

/**
 * Stub for get_post_format.
 */
function get_post_format()
{
    echo wpStub('get_post_format', func_get_args());
}

/**
 * Stub for the_ID.
 */
function the_ID()
{
    echo wpStub('the_ID', func_get_args());
}

/**
 * Stub for post_class.
 */
function post_class()
{
    echo wpStub('post_class', func_get_args());
}

/**
 * Stub for has_post_thumbnail.
 */
function has_post_thumbnail()
{
    echo wpStub('has_post_thumbnail', func_get_args());
}

/**
 * Stub for is_singular.
 */
function is_singular()
{
    echo wpStub('is_singular', func_get_args());
}

/**
 * Stub for the_post_thumbnail.
 */
function the_post_thumbnail()
{
    echo wpStub('the_post_thumbnail', func_get_args());
}

/**
 * Stub for is_single.
 */
function is_single()
{
    echo wpStub('is_single', func_get_args());
}

/**
 * Stub for the_content.
 */
function the_content()
{
    echo wpStub('the_content', func_get_args());
}

/**
 * Stub for __.
 */
function __()
{
    echo wpStub('__', func_get_args());
}

/**
 * Stub for wp_link_pages.
 */
function wp_link_pages()
{
    echo wpStub('wp_link_pages', func_get_args());
}

/**
 * Stub for get_the_author_meta.
 */
function get_the_author_meta()
{
    echo wpStub('get_the_author_meta', func_get_args());
}

/**
 * Stub for get_avatar.
 */
function get_avatar()
{
    echo wpStub('get_avatar', func_get_args());
}

/**
 * Stub for get_the_author.
 */
function get_the_author()
{
    echo wpStub('get_the_author', func_get_args());
}

/**
 * Stub for the_author_meta.
 */
function the_author_meta()
{
    echo wpStub('the_author_meta', func_get_args());
}

/**
 * Stub for get_author_posts_url.
 */
function get_author_posts_url()
{
    echo wpStub('get_author_posts_url', func_get_args());
}

/**
 * Stub for is_sticky.
 */
function is_sticky()
{
    echo wpStub('is_sticky', func_get_args());
}

/**
 * Stub for current_theme_supports.
 */
function current_theme_supports()
{
    echo wpStub('current_theme_supports', func_get_args());
}

/**
 * Stub for _x.
 */
function _x()
{
    echo wpStub('_x', func_get_args());
}

/**
 * Stub for get_post_format_link.
 */
function get_post_format_link()
{
    echo wpStub('get_post_format_link', func_get_args());
}

/**
 * Stub for get_post_format_string.
 */
function get_post_format_string()
{
    echo wpStub('get_post_format_string', func_get_args());
}

/**
 * Stub for get_post_type.
 */
function get_post_type()
{
    echo wpStub('get_post_type', func_get_args());
}

/**
 * Stub for edit_post_link.
 */
function edit_post_link()
{
    echo wpStub('edit_post_link', func_get_args());
}

/**
 * Stub for comments_open.
 */
function comments_open()
{
    echo wpStub('comments_open', func_get_args());
}

/**
 * Stub for comments_template.
 */
function comments_template()
{
    echo wpStub('comments_template', func_get_args());
}

/**
 * Stub for the_post_navigation.
 */
function the_post_navigation()
{
    echo wpStub('the_post_navigation', func_get_args());
}

/**
 * Stub for do_action.
 */
function do_action()
{
    echo wpStub('do_action', func_get_args());
}

/**
 * Stub for wp_footer.
 */
function wp_footer()
{
    echo wpStub('wp_footer', func_get_args());
}

/**
 * Stub for get_permalink.
 */
function get_permalink()
{
    return wpStub('get_permalink', func_get_args());
}

/**
 * Stub for get_comments_number.
 */
function get_comments_number()
{
    return wpStub('get_comments_number', func_get_args());
}

/**
 * Stub for comments_popup_link.
 */
function comments_popup_link()
{
    return wpStub('comments_popup_link', func_get_args());
}

/**
 * Stub for get_the_title.
 */
function get_the_title()
{
    return wpStub('get_the_title', func_get_args());
}
