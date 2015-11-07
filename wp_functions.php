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


/**
 * Display or retrieve the current post title with optional content.
 *
 * @since 0.71
 *
 * @param string $before Optional. Content to prepend to the title.
 * @param string $after  Optional. Content to append to the title.
 * @param bool   $echo   Optional, default to true.Whether to display or return.
 * @return string|void String if $echo parameter is false.
 */
function the_title( $before = '', $after = '', $echo = true ) {
    $title = get_the_title();

    if ( strlen($title) == 0 )
        return;

    $title = $before . $title . $after;

    if ( $echo )
        echo $title;
    else
        return $title;
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

function apply_filters($filter, $output, $show = false)
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

    // wpStub('add_action', func_get_args());
}

/**
 * Stub for add_filter.
 */
function add_filter()
{
    // wpStub('add_filter', func_get_args());
}


// ------ Here be unconverted stubs. --------




/**
 * Stub for is_admin.
 */
function is_admin()
{
    wpStub('is_admin', func_get_args());
}

/**
 * Stub for language_attributes.
 */
function language_attributes()
{
    wpStub('language_attributes', func_get_args());
}

/**
 * Stub for _e.
 */
function _e()
{
    wpStub('_e', func_get_args());
}

/**
 * Stub for has_nav_menu.
 */
function has_nav_menu()
{
    wpStub('has_nav_menu', func_get_args());
}

/**
 * Stub for wp_nav_menu.
 */
function wp_nav_menu()
{
    wpStub('wp_nav_menu', func_get_args());
}

/**
 * Stub for is_active_sidebar.
 */
function is_active_sidebar()
{
    wpStub('is_active_sidebar', func_get_args());
}

/**
 * Stub for dynamic_sidebar.
 */
function dynamic_sidebar()
{
    wpStub('dynamic_sidebar', func_get_args());
}

/**
 * Stub for get_post_format.
 */
function get_post_format()
{
    wpStub('get_post_format', func_get_args());
}

/**
 * Display the ID of the current item in the WordPress Loop.
 *
 * @since 0.71
 */
function the_ID() {
    echo get_the_ID();
}


/**
 * Display the classes for the post div.
 *
 * @since 2.7.0
 *
 * @param string|array $class One or more classes to add to the class list.
 * @param int|WP_Post $post_id Optional. Post ID or post object.
 */
function post_class( $class = '', $post_id = null ) {
    // Separates classes with a single space, collates classes for post DIV
    echo 'class="' . join( ' ', get_post_class( $class, $post_id ) ) . '"';
}

/**
 * Stub for has_post_thumbnail.
 */
function has_post_thumbnail()
{
    wpStub('has_post_thumbnail', func_get_args());
}

/**
 * Stub for is_singular.
 */
function is_singular()
{
    wpStub('is_singular', func_get_args());
}

/**
 * Stub for the_post_thumbnail.
 */
function the_post_thumbnail()
{
    wpStub('the_post_thumbnail', func_get_args());
}

/**
 * Stub for is_single.
 */
function is_single()
{
    global $request;

    $route = $request->get('_route');
    $okroutes = [ 'wp-contentlink' ];

    return in_array($route, $okroutes);
}

/**
 * Display the post content.
 *
 * @since 0.71
 *
 * @param string $more_link_text Optional. Content for when there is more text.
 * @param bool   $strip_teaser   Optional. Strip teaser content before the more text. Default is false.
 */
function the_content( $more_link_text = null, $strip_teaser = false) {
    $content = get_the_content( $more_link_text, $strip_teaser );

    /**
     * Filter the post content.
     *
     * @since 0.71
     *
     * @param string $content Content of the current post.
     */
    $content = apply_filters( 'the_content', $content );
    $content = str_replace( ']]>', ']]&gt;', $content );
    echo $content;
}

/**
 * Stub for __.
 */
function __($label, $context)
{
    return $label;
}

/**
 * Stub for wp_link_pages.
 */
function wp_link_pages()
{
    wpStub('wp_link_pages', func_get_args());
}

/**
 * Stub for get_the_author_meta.
 */
function get_the_author_meta()
{
    wpStub('get_the_author_meta', func_get_args());
}

/**
 * Stub for get_avatar.
 */
function get_avatar()
{
    wpStub('get_avatar', func_get_args());
}

/**
 * Stub for get_the_author.
 */
function get_the_author()
{
    wpStub('get_the_author', func_get_args());
}

/**
 * Stub for the_author_meta.
 */
function the_author_meta()
{
    wpStub('the_author_meta', func_get_args());
}

/**
 * Stub for get_author_posts_url.
 */
function get_author_posts_url()
{
    wpStub('get_author_posts_url', func_get_args());
}

/**
 * Stub for is_sticky.
 */
function is_sticky()
{
    wpStub('is_sticky', func_get_args());
}

/**
 * Stub for current_theme_supports.
 */
function current_theme_supports()
{
    wpStub('current_theme_supports', func_get_args());
}

/**
 * Stub for _x.
 */
function _x()
{
    wpStub('_x', func_get_args());
}

/**
 * Stub for get_post_format_link.
 */
function get_post_format_link()
{
    wpStub('get_post_format_link', func_get_args());
}

/**
 * Stub for get_post_format_string.
 */
function get_post_format_string()
{
    wpStub('get_post_format_string', func_get_args());
}

/**
 * Stub for get_post_type.
 */
function get_post_type()
{
    wpStub('get_post_type', func_get_args());
}

/**
 * Stub for edit_post_link.
 */
function edit_post_link()
{
    wpStub('edit_post_link', func_get_args());
}

/**
 * Stub for comments_open.
 */
function comments_open()
{
    wpStub('comments_open', func_get_args());
}

/**
 * Stub for comments_template.
 */
function comments_template()
{
    wpStub('comments_template', func_get_args());
}

/**
 * Stub for the_post_navigation.
 */
function the_post_navigation()
{
    wpStub('the_post_navigation', func_get_args());
}

/**
 * Stub for do_action.
 */
function do_action()
{
    wpStub('do_action', func_get_args());
}

/**
 * Stub for wp_footer.
 */
function wp_footer()
{
    wpStub('wp_footer', func_get_args());
}

/**
 * Stub for get_permalink.
 */
function get_permalink()
{
    wpStub('get_permalink', func_get_args());
}

/**
 * Stub for get_comments_number.
 */
function get_comments_number()
{
    wpStub('get_comments_number', func_get_args());
}

/**
 * Stub for comments_popup_link.
 */
function comments_popup_link()
{
    wpStub('comments_popup_link', func_get_args());
}

/**
 * Stub for get_the_title.
 */
function get_the_title()
{
    global $record;

    return $record->title();
}

/**
 * Stub for get_the_content.
 */
function get_the_content()
{
    global $record;

    if (isset($record['body'])) {
        return $record['body'];
    } else {
        return $record->excerpt(10000);
    }
}

/**
 * Stub for get_the_ID.
 */
function get_the_ID()
{
    global $record;

    if (isset($record['id'])) {
        return $record['id'];
    } else {
        return false;
    }
}


/**
 * Retrieve the classes for the post div as an array.
 *
 * The class names are many. If the post is a sticky, then the 'sticky'
 * class name. The class 'hentry' is always added to each post. If the post has a
 * post thumbnail, 'has-post-thumbnail' is added as a class. For each taxonomy that
 * the post belongs to, a class will be added of the format '{$taxonomy}-{$slug}' -
 * eg 'category-foo' or 'my_custom_taxonomy-bar'. The 'post_tag' taxonomy is a special
 * case; the class has the 'tag-' prefix instead of 'post_tag-'. All classes are
 * passed through the filter, 'post_class' with the list of classes, followed by
 * $class parameter value, with the post ID as the last parameter.
 *
 * @since 2.7.0
 * @since 4.2.0 Custom taxonomy classes were added.
 *
 * @param string|array $class   One or more classes to add to the class list.
 * @param int|WP_Post  $post_id Optional. Post ID or post object.
 * @return array Array of classes.
 */
function get_post_class( $class = '', $post_id = null ) {
    $post = get_post( $post_id );

    $classes = array();

    if ( $class ) {
        if ( ! is_array( $class ) ) {
            $class = preg_split( '#\s+#', $class );
        }
        $classes = array_map( 'esc_attr', $class );
    }

    if ( ! $post ) {
        return $classes;
    }

    $classes[] = 'post-' . $post->ID;
    if ( ! is_admin() )
        $classes[] = $post->post_type;
    $classes[] = 'type-' . $post->post_type;
    $classes[] = 'status-' . $post->post_status;

    // Post Format
    if ( post_type_supports( $post->post_type, 'post-formats' ) ) {
        $post_format = get_post_format( $post->ID );

        if ( $post_format && !is_wp_error($post_format) )
            $classes[] = 'format-' . sanitize_html_class( $post_format );
        else
            $classes[] = 'format-standard';
    }

    // Post requires password
    if ( post_password_required( $post->ID ) ) {
        $classes[] = 'post-password-required';
    // Post thumbnails
    } elseif ( ! is_attachment( $post ) && current_theme_supports( 'post-thumbnails' ) && has_post_thumbnail( $post->ID ) ) {
        $classes[] = 'has-post-thumbnail';
    }

    // sticky for Sticky Posts
    if ( is_sticky( $post->ID ) ) {
        if ( is_home() && ! is_paged() ) {
            $classes[] = 'sticky';
        } elseif ( is_admin() ) {
            $classes[] = 'status-sticky';
        }
    }

    // hentry for hAtom compliance
    $classes[] = 'hentry';

    // All public taxonomies
    $taxonomies = get_taxonomies( array( 'public' => true ) );
    foreach ( (array) $taxonomies as $taxonomy ) {
        if ( is_object_in_taxonomy( $post->post_type, $taxonomy ) ) {
            foreach ( (array) get_the_terms( $post->ID, $taxonomy ) as $term ) {
                if ( empty( $term->slug ) ) {
                    continue;
                }

                $term_class = sanitize_html_class( $term->slug, $term->term_id );
                if ( is_numeric( $term_class ) || ! trim( $term_class, '-' ) ) {
                    $term_class = $term->term_id;
                }

                // 'post_tag' uses the 'tag' prefix for backward compatibility.
                if ( 'post_tag' == $taxonomy ) {
                    $classes[] = 'tag-' . $term_class;
                } else {
                    $classes[] = sanitize_html_class( $taxonomy . '-' . $term_class, $taxonomy . '-' . $term->term_id );
                }
            }
        }
    }

    $classes = array_map( 'esc_attr', $classes );

    /**
     * Filter the list of CSS classes for the current post.
     *
     * @since 2.7.0
     *
     * @param array  $classes An array of post classes.
     * @param string $class   A comma-separated list of additional classes added to the post.
     * @param int    $post_id The post ID.
     */
    $classes = apply_filters( 'post_class', $classes, $class, $post->ID );

    return array_unique( $classes );
}

/**
 * Stub for get_post.
 */
function get_post()
{
    global $record;

    return $record;
}

/**
 * Stub for post_type_supports.
 */
function post_type_supports()
{
    wpStub('post_type_supports', func_get_args());
}

/**
 * Stub for get_taxonomies.
 */
function get_taxonomies()
{
    wpStub('get_taxonomies', func_get_args());
}

/**
 * Escaping for HTML attributes.
 *
 * @since 2.8.0
 *
 * @param string $text
 * @return string
 */
function esc_attr( $text ) {
    $safe_text = wp_check_invalid_utf8( $text );
    $safe_text = _wp_specialchars( $safe_text, ENT_QUOTES );
    /**
     * Filter a string cleaned and escaped for output in an HTML attribute.
     *
     * Text passed to esc_attr() is stripped of invalid or special characters
     * before output.
     *
     * @since 2.0.6
     *
     * @param string $safe_text The text after it has been escaped.
     * @param string $text      The text prior to being escaped.
     */
    return apply_filters( 'attribute_escape', $safe_text, $text );
}


/**
 * Checks for invalid UTF8 in a string.
 *
 * @since 2.8.0
 *
 * @staticvar bool $is_utf8
 * @staticvar bool $utf8_pcre
 *
 * @param string  $string The text which is to be checked.
 * @param bool    $strip Optional. Whether to attempt to strip out invalid UTF8. Default is false.
 * @return string The checked text.
 */
function wp_check_invalid_utf8( $string, $strip = false ) {
    $string = (string) $string;

    if ( 0 === strlen( $string ) ) {
        return '';
    }

    // Store the site charset as a static to avoid multiple calls to get_option()
    static $is_utf8 = null;
    if ( ! isset( $is_utf8 ) ) {
        $is_utf8 = in_array( get_option( 'blog_charset' ), array( 'utf8', 'utf-8', 'UTF8', 'UTF-8' ) );
    }
    if ( ! $is_utf8 ) {
        return $string;
    }

    // Check for support for utf8 in the installed PCRE library once and store the result in a static
    static $utf8_pcre = null;
    if ( ! isset( $utf8_pcre ) ) {
        $utf8_pcre = @preg_match( '/^./u', 'a' );
    }
    // We can't demand utf8 in the PCRE installation, so just return the string in those cases
    if ( !$utf8_pcre ) {
        return $string;
    }

    // preg_match fails when it encounters invalid UTF8 in $string
    if ( 1 === @preg_match( '/^./us', $string ) ) {
        return $string;
    }

    // Attempt to strip the bad chars if requested (not recommended)
    if ( $strip && function_exists( 'iconv' ) ) {
        return iconv( 'utf-8', 'utf-8', $string );
    }

    return '';
}

/**
 * Converts a number of special characters into their HTML entities.
 *
 * Specifically deals with: &, <, >, ", and '.
 *
 * $quote_style can be set to ENT_COMPAT to encode " to
 * &quot;, or ENT_QUOTES to do both. Default is ENT_NOQUOTES where no quotes are encoded.
 *
 * @since 1.2.2
 * @access private
 *
 * @staticvar string $_charset
 *
 * @param string $string         The text which is to be encoded.
 * @param int    $quote_style    Optional. Converts double quotes if set to ENT_COMPAT, both single and double if set to ENT_QUOTES or none if set to ENT_NOQUOTES. Also compatible with old values; converting single quotes if set to 'single', double if set to 'double' or both if otherwise set. Default is ENT_NOQUOTES.
 * @param string $charset        Optional. The character encoding of the string. Default is false.
 * @param bool   $double_encode  Optional. Whether to encode existing html entities. Default is false.
 * @return string The encoded text with HTML entities.
 */
function _wp_specialchars( $string, $quote_style = ENT_NOQUOTES, $charset = false, $double_encode = false ) {
    $string = (string) $string;

    if ( 0 === strlen( $string ) )
        return '';

    // Don't bother if there are no specialchars - saves some processing
    if ( ! preg_match( '/[&<>"\']/', $string ) )
        return $string;

    // Account for the previous behaviour of the function when the $quote_style is not an accepted value
    if ( empty( $quote_style ) )
        $quote_style = ENT_NOQUOTES;
    elseif ( ! in_array( $quote_style, array( 0, 2, 3, 'single', 'double' ), true ) )
        $quote_style = ENT_QUOTES;

    // Store the site charset as a static to avoid multiple calls to wp_load_alloptions()
    if ( ! $charset ) {
        static $_charset = null;
        if ( ! isset( $_charset ) ) {
            $alloptions = wp_load_alloptions();
            $_charset = isset( $alloptions['blog_charset'] ) ? $alloptions['blog_charset'] : '';
        }
        $charset = $_charset;
    }

    if ( in_array( $charset, array( 'utf8', 'utf-8', 'UTF8' ) ) )
        $charset = 'UTF-8';

    $_quote_style = $quote_style;

    if ( $quote_style === 'double' ) {
        $quote_style = ENT_COMPAT;
        $_quote_style = ENT_COMPAT;
    } elseif ( $quote_style === 'single' ) {
        $quote_style = ENT_NOQUOTES;
    }

    if ( ! $double_encode ) {
        // Guarantee every &entity; is valid, convert &garbage; into &amp;garbage;
        // This is required for PHP < 5.4.0 because ENT_HTML401 flag is unavailable.
        $string = wp_kses_normalize_entities( $string );
    }

    $string = @htmlspecialchars( $string, $quote_style, $charset, $double_encode );

    // Backwards compatibility
    if ( 'single' === $_quote_style )
        $string = str_replace( "'", '&#039;', $string );

    return $string;
}



/**
 * Stub for get_option.
 */
function get_option()
{
    wpStub('get_option', func_get_args());
}
