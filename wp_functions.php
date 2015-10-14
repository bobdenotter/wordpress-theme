<?php


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


function get_footer()
{
    require_once('footer.php');
}

function language_attributes()
{
    echo "[language_attributes]";
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

function _e()
{
    echo "[_e]";

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

function has_nav_menu()
{
    echo "[has_nav_menu]";

}


function is_active_sidebar()
{
    echo "[]";
}

function do_action()
{
    echo "[do_action]";

}

function __()
{
    echo "[ __]";

}


function wp_footer()
{
    echo "[wp_footer]";
}


function get_template_part()
{
    echo "[get_template_part]";

}


function get_post_format()
{
    echo "[get_post_format]";

}

function comments_open()
{
    echo "[comments_open]";

}

function get_comments_number()
{
    echo "[get_comments_number]";

}

function the_post_navigation()
{
    echo "[the_post_navigation]";

}

function h7()
{
    echo "[]";

}

function h8()
{
    echo "[]";

}

function h9()
{
    echo "[]";

}