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
    global $record;

    // dump($record->values);

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


function get_footer( $name = null ) {
    /**
     * Fires before the footer template file is loaded.
     *
     * The hook allows a specific footer template file to be used in place of the
     * default footer template file. If your file is called footer-new.php,
     * you would specify the filename in the hook as get_footer( 'new' ).
     *
     * @since 2.1.0
     * @since 2.8.0 $name parameter added.
     *
     * @param string $name Name of the specific footer file to use.
     */
    do_action( 'get_footer', $name );

    $templates = array();
    $name = (string) $name;
    if ( '' !== $name )
        $templates[] = "footer-{$name}.php";

    $templates[] = 'footer.php';

    // Backward compat code will be removed in a future release
    if ('' == locate_template($templates, true))
        load_template( ABSPATH . WPINC . '/theme-compat/footer.php');
}


function bloginfo()
{
    global $content, $config;

    echo $config->get('general/sitename');
}

function esc_url($str)
{
    return $str;
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

    // WPhelper::stub('add_action', func_get_args());
}

/**
 * Stub for add_filter.
 */
function add_filter()
{
    // WPhelper::stub('add_filter', func_get_args());
}


// ------ Here be unconverted stubs. --------




/**
 * Stub for is_admin.
 */
function is_admin()
{
    global $currentuser;

    return (!empty($currentuser) && !empty($currentuser['username']));
}

/**
 * Displays the language attributes for the html tag.
 *
 * Builds up a set of html attributes containing the text direction and language
 * information for the page.
 *
 * @since 2.1.0
 * @since 4.3.0 Converted into a wrapper for get_language_attributes().
 *
 * @param string $doctype Optional. The type of html document. Accepts 'xhtml' or 'html'. Default 'html'.
 */
function language_attributes( $doctype = 'html' ) {
    echo get_language_attributes( $doctype );
}

/**
 * Gets the language attributes for the html tag.
 *
 * Builds up a set of html attributes containing the text direction and language
 * information for the page.
 *
 * @since 4.3.0
 *
 * @param string $doctype Optional. The type of html document. Accepts 'xhtml' or 'html'. Default 'html'.
 */
function get_language_attributes( $doctype = 'html' ) {
    $attributes = array();

    if ( function_exists( 'is_rtl' ) && is_rtl() )
        $attributes[] = 'dir="rtl"';

    if ( $lang = get_bloginfo('language') ) {
        if ( get_option('html_type') == 'text/html' || $doctype == 'html' )
            $attributes[] = "lang=\"$lang\"";

        if ( get_option('html_type') != 'text/html' || $doctype == 'xhtml' )
            $attributes[] = "xml:lang=\"$lang\"";
    }

    $output = implode(' ', $attributes);

    /**
     * Filter the language attributes for display in the html tag.
     *
     * @since 2.5.0
     * @since 4.3.0 Added the `$doctype` parameter.
     *
     * @param string $output A space-separated list of language attributes.
     * @param string $doctype The type of html document (xhtml|html).
     */
    return apply_filters( 'language_attributes', $output, $doctype );
}


/**
 * Stub for _e.
 */
function _e($label, $domain = 'default' )
{
    echo __($label, $domain);
}

/**
 * Stub for __.
 */
function __($label, $domain = 'default' )
{
    return $label;
}


/**
 * Stub for has_nav_menu.
 */
function has_nav_menu()
{
    WPhelper::stub('has_nav_menu', func_get_args());
}

/**
 * Stub for wp_nav_menu.
 */
function wp_nav_menu()
{
    WPhelper::stub('wp_nav_menu', func_get_args());
}

/**
 * Stub for is_active_sidebar.
 */
function is_active_sidebar()
{
    WPhelper::stub('is_active_sidebar', func_get_args());
}

/**
 * Stub for dynamic_sidebar.
 */
function dynamic_sidebar()
{
    WPhelper::stub('dynamic_sidebar', func_get_args());
}

/**
 * Stub for get_post_format.
 */
function get_post_format()
{
    return false;
    // WPhelper::stub('get_post_format', func_get_args());
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
    global $record;

    return (!empty($record->getImage()));
}

/**
 * Stub for is_singular.
 */
function is_singular()
{
    return is_single();
}



/**
 * Stub for is_singular.
 */
function is_page()
{
    return is_single();
}



/**
 * Display the post thumbnail.
 *
 * When a theme adds 'post-thumbnail' support, a special 'post-thumbnail' image size
 * is registered, which differs from the 'thumbnail' image size managed via the
 * Settings > Media screen.
 *
 * When using the_post_thumbnail() or related functions, the 'post-thumbnail' image
 * size is used by default, though a different size can be specified instead as needed.
 *
 * @since 2.9.0
 *
 * @see get_the_post_thumbnail()
 *
 * @param string|array $size Optional. Registered image size to use, or flat array of height
 *                           and width values. Default 'post-thumbnail'.
 * @param string|array $attr Optional. Query string or array of attributes. Default empty.
 */
function the_post_thumbnail( $size = 'post-thumbnail', $attr = '' ) {
    echo get_the_post_thumbnail( null, $size, $attr );
}

/**
 * <img width="825" height="510" src="http://wordpress.localhost/wp-content/uploads/2015/10/277688_hungry-like-the-wolf-825x510.jpg" class="attachment-post-thumbnail wp-post-image" alt="277688_hungry like the wolf">
 */
function get_the_post_thumbnail( $post_id = null, $size = 'post-thumbnail', $attr = '' )
{
    global $record;

    // Todo: Conjure the desired width and height from somewhere. Replace hardcoded values.
    $width = 825;
    $height = 510;

    $data = [
        'width' => $width,
        'height' => $height,
        'img' => $record->getImage()
    ];

    echo WPhelper::render('wp_twighelpers/post_thumbnail.twig', $data);
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
 * Stub for wp_link_pages.
 */
function wp_link_pages()
{
    // WPhelper::stub('wp_link_pages', func_get_args());
}

function get_the_author_meta( $field = '', $user_id = false )
{
    global $record;

    switch($field) {

        case 'login':
            return $record->user['username'];

        case 'email':
            return $record->user['email'];

        case 'true':
            return $record->user['enabled'];

        case 'description':
            return '-';

        default:
            return false;

    }

    // WPhelper::stub('get_the_author_meta', func_get_args());
}

/**
 * Stub for get_avatar.
 */
function get_avatar()
{
    // WPhelper::stub('get_avatar', func_get_args());
}

/**
 * Stub for get_the_author.
 */
function get_the_author()
{
    global $record;

    return $record->user['displayname'];
}

/**
 * Stub for the_author_meta.
 */
function the_author_meta( $field = '', $user_id = false )
{
    echo get_the_author_meta( $field, $user_id );
}

/**
 * Stub for get_author_posts_url.
 */
function get_author_posts_url()
{
    // @todo: Make a nice link

    return "/";
}

/**
 * Stub for is_sticky.
 */
function is_sticky()
{
    global $record;

    return $record['sticky'];
}

/**
 * Stub for current_theme_supports.
 */
function current_theme_supports( $feature )
{
    global $record;

    if ( 'title-tag' == $feature ) {
        // Don't confirm support unless called internally.
        $trace = debug_backtrace();
        if ( ! in_array( $trace[1]['function'], array( '_wp_render_title_tag', 'wp_title' ) ) ) {
            return false;
        }
    }

    // If no args passed then no extra checks need be performed
    if ( func_num_args() <= 1 )
        return true;

    $args = array_slice( func_get_args(), 1 );

    switch ( $feature ) {
        case 'post-thumbnails':
        case 'html5':
            return true;

        case 'post-formats':
            return false;

        case 'custom-header':
        case 'custom-background' :
            return false;

        default:
            return true; // #whatcouldgowrong?
    }
}

/**
 * Stub for _x.
 */
function _x( $text, $context, $domain = 'default' )
{
    return __( $text, $domain );
}

/**
 * Stub for _ex.
 */
function _ex( $text, $context, $domain = 'default' )
{
    echo __( $text, $domain );
}

/**
 * Stub for get_post_format_link.
 */
function get_post_format_link()
{
    global $record;

    return $record->link();
}

/**
 * Stub for get_post_format_string.
 */
function get_post_format_string()
{
    WPhelper::stub('get_post_format_string', func_get_args());
}

/**
 * Stub for get_post_type.
 */
function get_post_type()
{
    global $record;

    return $record->contenttype['singular_slug'];

    //WPhelper::stub('get_post_type', func_get_args());
}

/**
 * Stub for edit_post_link.
 */
function edit_post_link( $text = null, $before = '', $after = '', $id = 0 )
{
    global $record, $currentuser;

    if (empty($record) || empty($currentuser['username'])) {
        return;
    }

    $path = \Bolt\Library::path('editcontent', ['contenttypeslug' => $record->contenttype['slug'], 'id' => $record['id']]);

    if ( null === $text ) {
        $text = __( 'Edit This' );
    }

    $link = '<a class="post-edit-link" href="' . $path . '">' . $text . '</a>';

    /**
     * Filter the post edit link anchor tag.
     *
     * @since 2.3.0
     *
     * @param string $link    Anchor tag for the edit link.
     * @param int    $post_id Post ID.
     * @param string $text    Anchor text.
     */
    echo $before . apply_filters( 'edit_post_link', $link, $record['id'], $text ) . $after;

}

/**
 * Stub for comments_open.
 */
function comments_open()
{
    global $app;

    $enabled = array_keys($app['extensions']->getEnabled());
    $needed = ['Disqus', 'Facebook Comments'];

    return (!empty(array_intersect($needed, $enabled)));
}


/**
 * Load the comment template specified in $file.
 *
 * Will not display the comments template if not on single post or page, or if
 * the post does not have comments.
 *
 * Uses the WordPress database object to query for the comments. The comments
 * are passed through the 'comments_array' filter hook with the list of comments
 * and the post ID respectively.
 *
 * The $file path is passed through a filter hook called, 'comments_template'
 * which includes the TEMPLATEPATH and $file combined. Tries the $filtered path
 * first and if it fails it will require the default comment template from the
 * default theme. If either does not exist, then the WordPress process will be
 * halted. It is advised for that reason, that the default theme is not deleted.
 *
 * @uses $withcomments Will not try to get the comments if the post has none.
 *
 * @since 1.5.0
 *
 * @global WP_Query $wp_query
 * @global WP_Post  $post
 * @global wpdb     $wpdb
 * @global int      $id
 * @global object   $comment
 * @global string   $user_login
 * @global int      $user_ID
 * @global string   $user_identity
 * @global bool     $overridden_cpage
 *
 * @param string $file              Optional. The file to load. Default '/comments.php'.
 * @param bool   $separate_comments Optional. Whether to separate the comments by comment type.
 *                                  Default false.
 */
function comments_template( $file = '', $separate_comments = false ) {
    global $record, $paths;

    if ( !(is_single() || is_page() || $withcomments) || empty($record) )
        return;

    if ( empty($file) ) {
        $file = $paths['themepath'] . '/comments.php';
    }

    $comments = [];

    if (file_exists($file)) {
        require($file);
    } elseif (file_exists($paths['themepath'] . '/' . $file)) {
        require($paths['themepath'] . '/' . $file);
    } elseif (file_exists($paths['themepath'] . $file)) {
        require($paths['themepath'] . $file);
    }
}


/**
 * Display navigation to next/previous post when applicable.
 *
 * @since 4.1.0
 *
 * @param array $args Optional. See {@see get_the_post_navigation()} for available
 *                    arguments. Default empty array.
 */
function the_post_navigation( $args = array() ) {
    echo get_the_post_navigation( $args );
}

/**
 * Return navigation to next/previous post when applicable.
 *
 * @since 4.1.0
 *
 * @param array $args {
 *     Optional. Default post navigation arguments. Default empty array.
 *
 *     @type string $prev_text          Anchor text to display in the previous post link. Default `%title`.
 *     @type string $next_text          Anchor text to display in the next post link. Default `%title`.
 *     @type string $screen_reader_text Screen reader text for nav element. Default 'Post navigation'.
 * }
 * @return string Markup for post links.
 */
function get_the_post_navigation( $args = array() ) {
    $args = wp_parse_args( $args, array(
        'prev_text'          => '%title',
        'next_text'          => '%title',
        'screen_reader_text' => __( 'Post navigation' ),
    ) );

    $navigation = '';
    $previous   = get_previous_post_link( '<div class="nav-previous">%link</div>', $args['prev_text'] );
    $next       = get_next_post_link( '<div class="nav-next">%link</div>', $args['next_text'] );

    // Only add markup if there's somewhere to navigate to.
    if ( $previous || $next ) {
        $navigation = _navigation_markup( $previous . $next, 'post-navigation', $args['screen_reader_text'] );
    }

    return $navigation;
}


/**
 * Stub for do_action.
 */
function do_action()
{
    WPhelper::stub('do_action', func_get_args());
}

/**
 * Stub for wp_footer.
 */
function wp_footer()
{
    WPhelper::stub('wp_footer', func_get_args());
}

/**
 * Stub for get_permalink.
 */
function get_permalink()
{
    global $record;

    return $record->link();
}

/**
 * Stub for get_comments_number.
 */
function get_comments_number()
{
    WPhelper::stub('get_comments_number', func_get_args());
}

/**
 * Stub for comments_popup_link.
 */
function comments_popup_link()
{
    WPhelper::stub('comments_popup_link', func_get_args());
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
    global $record, $config;

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

    $classes[] = 'post-' . $post->id;
    if ( ! is_admin() )
        $classes[] = $post->post_type;
    $classes[] = 'type-' . $post->contenttype['singular_slug'];
    $classes[] = 'status-' . $post['status'];

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

    $taxonomies = $config->get('taxonomy');

    // All public taxonomies
    foreach ( (array) $record->taxonomy as $taxonomy => $terms ) {
        $slug = $taxonomies[$taxonomy]['singular_slug'];
        foreach ($terms as $term) {
            $classes[] = $slug . '-' . $term;
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
    return false;
}

/**
 * Stub for get_taxonomies.
 */
function get_taxonomies()
{
    global $record;

    $atoms = array();

    if (!empty($record->taxonomy)) {
        foreach($record->taxonomy as $taxonomies) {
            $atoms = array_merge($atoms, $taxonomies);
        }
    }

    return $atoms;
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
function get_option($what)
{
    switch ($what) {
        case 'html_type':
            return 'text/html';

        case 'blog_charset':
            return 'UTF-8';

        case 'date_format':
            return '%A %B %e, %Y';

        default:
            WPhelper::stub('get_option', func_get_args());
            return false;
    }
}



/**
 * Stub for get_locale.
 */
function get_locale()
{
    global $config;

    return($config->get('general/locale'));
}

/**
 * Stub for the_permalink.
 */
function the_permalink()
{
    WPhelper::stub('the_permalink', func_get_args());
}

/**
 * Stub for is_object_in_taxonomy.
 */
function is_object_in_taxonomy()
{
    WPhelper::stub('is_object_in_taxonomy', func_get_args());
}

/**
 * Stub for get_the_time.
 */
function get_the_time()
{
    global $record;

    $date = new DateTime($record['datecreated']);

    return $date->getTimestamp();
}

/**
 * Stub for get_the_modified_time.
 */
function get_the_modified_time()
{
    global $record;

    $date = new DateTime($record['datechanged']);

    return $date->getTimestamp();
}

/**
 * Stub for get_the_date.
 */
function get_the_date()
{
    global $record;

    return strftime("%A %B %e, %Y", get_the_time());
}

/**
 * Stub for get_the_modified_date.
 */
function get_the_modified_date()
{
    global $record;

    return strftime("%A %B %e, %Y", get_the_modified_time());
}

/**
 * Stub for get_the_category_list.
 */
function get_the_category_list( $separator = '' )
{
    global $record;

    if (empty($record->taxonomy['categories'])) {
        return '';
    }

    $items = [];

    foreach($record->taxonomy['categories'] as $link => $term) {
        $items[] = sprintf('<a href="%s">%s</a>', $link, $term);
    }

    if ($separator != '') {
        $res = implode($separator, $items);
    } else {
        $res = "<ul><li>" . implode("</li><li>", $items) . "</li></ul>";
    }

    return $res;

}

/**
 * Stub for get_the_tag_list.
 */
function get_the_tag_list( $before = '', $sep = '', $after = '', $id = 0 )
{
    global $record;

    if (empty($record->taxonomy['tags'])) {
        return '';
    }

    $items = [];

    foreach($record->taxonomy['tags'] as $link => $term) {
        $items[] = sprintf('<a href="%s">%s</a>', $link, $term);
    }

    if ($sep != '') {
        $res = implode($sep, $items);
    } else {
        $res = "<ul><li>" . implode("</li><li>", $items) . "</li></ul>";
    }

    return $before . $res . $after;

}

/**
 * Stub for get_transient.
 */
function get_transient()
{
    return false;
}

/**
 * Stub for get_categories.
 */
function get_categories()
{
    global $config;

    if (!empty($config->get('taxonomy/categories'))) {
        $categories = $config->get('taxonomy/categories');
        return($categories['options']);
    } else {
        return false;
    }

}

/**
 * Stub for set_transient.
 */
function set_transient()
{
    return true;
}

/**
 * Stub for wp_get_current_commenter.
 */
function wp_get_current_commenter()
{
    WPhelper::stub('wp_get_current_commenter', func_get_args());
}

/**
 * Stub for get_comments.
 */
function get_comments()
{
    WPhelper::stub('get_comments', func_get_args());
}

/**
 * Stub for get_query_var.
 */
function get_query_var()
{
    WPhelper::stub('get_query_var', func_get_args());
}

/**
 * Stub for have_comments.
 */
function have_comments()
{
    WPhelper::stub('have_comments', func_get_args());
}

/**
 * Stub for comment_form.
 */
function comment_form()
{
    $data = [];

    echo WPhelper::render('wp_twighelpers/comment_form.twig', $data);
}


/**
 * Merge user defined arguments into defaults array.
 *
 * This function is used throughout WordPress to allow for both string or array
 * to be merged into another array.
 *
 * @since 2.2.0
 *
 * @param string|array $args     Value to merge with $defaults
 * @param array        $defaults Optional. Array that serves as the defaults. Default empty.
 * @return array Merged user defined values with defaults.
 */
function wp_parse_args( $args, $defaults = '' ) {
    if ( is_object( $args ) )
        $r = get_object_vars( $args );
    elseif ( is_array( $args ) )
        $r =& $args;
    else
        wp_parse_str( $args, $r );

    if ( is_array( $defaults ) )
        return array_merge( $defaults, $r );
    return $r;
}



/**
 * Stub for get_previous_post_link.
 */
function get_previous_post_link( $format = '&laquo; %link', $link = '%title' )
{
    global $record;

    if (empty($record)) {
        return false;
    }

    $temp_rec = $record->previous();

    if (empty($temp_rec)) {
        return false;
    }

    if ($temp_rec->title()) {
        $title = $temp_rec->title();
    } else {
        $title = __( 'Previous Post' );
    }

    /** This filter is documented in wp-includes/post-template.php */
    $title = apply_filters( 'the_title', $title, $temp_rec['id'] );

    $date = mysql2date( get_option( 'date_format' ), $temp_rec['datecreated'] );
    $rel = $previous ? 'prev' : 'next';

    $string = '<a href="' . $temp_rec->link() . '" rel="'.$rel.'">';
    $inlink = str_replace( '%title', $title, $link );
    $inlink = str_replace( '%date', $date, $inlink );
    $inlink = $string . $inlink . '</a>';

    $output = str_replace( '%link', $inlink, $format );

    return apply_filters( "previous_post_link", $output, $format, $link, $post, $adjacent );
}

/**
 * Stub for get_next_post_link.
 */
function get_next_post_link( $format = '&laquo; %link', $link = '%title' )
{
    global $record;

    if (empty($record)) {
        return false;
    }

    $temp_rec = $record->next();

    if (empty($temp_rec)) {
        return false;
    }

    if ($temp_rec->title()) {
        $title = $temp_rec->title();
    } else {
        $title = __( 'Next Post' );
    }

    /** This filter is documented in wp-includes/post-template.php */
    $title = apply_filters( 'the_title', $title, $temp_rec['id'] );

    $date = mysql2date( get_option( 'date_format' ), $temp_rec['datecreated'] );
    $rel = $previous ? 'prev' : 'next';

    $string = '<a href="' . $temp_rec->link() . '" rel="'.$rel.'">';
    $inlink = str_replace( '%title', $title, $link );
    $inlink = str_replace( '%date', $date, $inlink );
    $inlink = $string . $inlink . '</a>';

    $output = str_replace( '%link', $inlink, $format );

    return apply_filters( "next_post_link", $output, $format, $link, $post, $adjacent );
}



/**
 * Convert given date string into a different format.
 *
 * $format should be either a PHP date format string, e.g. 'U' for a Unix
 * timestamp, or 'G' for a Unix timestamp assuming that $date is GMT.
 *
 * If $translate is true then the given date and format string will
 * be passed to date_i18n() for translation.
 *
 * @since 0.71
 *
 * @param string $format    Format of the date to return.
 * @param string $date      Date string to convert.
 * @param bool   $translate Whether the return date should be translated. Default true.
 * @return string|int|bool Formatted date string or Unix timestamp. False if $date is empty.
 */
function mysql2date( $format, $date, $translate = true ) {
    if ( empty( $date ) )
        return false;

    if ( 'G' == $format )
        return strtotime( $date . ' +0000' );

    $i = strtotime( $date );

    if ( 'U' == $format )
        return $i;

    if ( $translate )
        return date_i18n( $format, $i );
    else
        return date( $format, $i );
}

/**
 * Stub for date_i18n.
 */
function date_i18n( $dateformatstring, $i )
{
    return strftime($format, $i);
}


/**
 * Wraps passed links in navigational markup.
 *
 * @since 4.1.0
 * @access private
 *
 * @param string $links              Navigational links.
 * @param string $class              Optional. Custom class for nav element. Default: 'posts-navigation'.
 * @param string $screen_reader_text Optional. Screen reader text for nav element. Default: 'Posts navigation'.
 * @return string Navigation template tag.
 */
function _navigation_markup( $links, $class = 'posts-navigation', $screen_reader_text = '' ) {
    if ( empty( $screen_reader_text ) ) {
        $screen_reader_text = __( 'Posts navigation' );
    }

    $template = '
    <nav class="navigation %1$s" role="navigation">
        <h2 class="screen-reader-text">%2$s</h2>
        <div class="nav-links">%3$s</div>
    </nav>';

    return sprintf( $template, sanitize_html_class( $class ), esc_html( $screen_reader_text ), $links );
}

/**
 * Sanitizes an HTML classname to ensure it only contains valid characters.
 *
 * Strips the string down to A-Z,a-z,0-9,_,-. If this results in an empty
 * string then it will return the alternative value supplied.
 *
 * @todo Expand to support the full range of CDATA that a class attribute can contain.
 *
 * @since 2.8.0
 *
 * @param string $class    The classname to be sanitized
 * @param string $fallback Optional. The value to return if the sanitization ends up as an empty string.
 *  Defaults to an empty string.
 * @return string The sanitized value
 */
function sanitize_html_class( $class, $fallback = '' ) {
    //Strip out any % encoded octets
    $sanitized = preg_replace( '|%[a-fA-F0-9][a-fA-F0-9]|', '', $class );

    //Limit to A-Z,a-z,0-9,_,-
    $sanitized = preg_replace( '/[^A-Za-z0-9_-]/', '', $sanitized );

    if ( '' == $sanitized )
        $sanitized = $fallback;

    /**
     * Filter a sanitized HTML class string.
     *
     * @since 2.8.0
     *
     * @param string $sanitized The sanitized HTML class.
     * @param string $class     HTML class before sanitization.
     * @param string $fallback  The fallback string.
     */
    return apply_filters( 'sanitize_html_class', $sanitized, $class, $fallback );
}

/**
 * Escaping for HTML blocks.
 *
 * @since 2.8.0
 *
 * @param string $text
 * @return string
 */
function esc_html( $text ) {
    $safe_text = wp_check_invalid_utf8( $text );
    $safe_text = _wp_specialchars( $safe_text, ENT_QUOTES );
    /**
     * Filter a string cleaned and escaped for output in HTML.
     *
     * Text passed to esc_html() is stripped of invalid or special characters
     * before output.
     *
     * @since 2.8.0
     *
     * @param string $safe_text The text after it has been escaped.
     * @param string $text      The text prior to being escaped.
     */
    return apply_filters( 'esc_html', $safe_text, $text );
}

/**
 * Retrieve the name of the highest priority template file that exists.
 *
 * Searches in the STYLESHEETPATH before TEMPLATEPATH so that themes which
 * inherit from a parent theme can just overload one file.
 *
 * @since 2.7.0
 *
 * @param string|array $template_names Template file(s) to search for, in order.
 * @param bool         $load           If true the template file will be loaded if it is found.
 * @param bool         $require_once   Whether to require_once or require. Default true. Has no effect if $load is false.
 * @return string The template filename if one is located.
 */
function locate_template($template_names, $load = false, $require_once = true )
{
    global $paths;

    $located = '';
    foreach ( (array) $template_names as $template_name ) {
        if ( !$template_name )
            continue;
        if ( file_exists($paths['themepath'] . '/' . $template_name)) {
            $located = $paths['themepath'] . '/' . $template_name;
            break;
        } elseif ( file_exists($paths['themepath'] . $template_name) ) {
            $located = $paths['themepath'] . '/' . $template_name;
            break;
        } elseif ( file_exists($template_name) ) {
            $located = $template_name;
            break;
        }
    }

    if ( $load && '' != $located )
        load_template( $located, $require_once );

    return $located;
}


/**
 * Require the template file with WordPress environment.
 *
 * The globals are set up for the template file to ensure that the WordPress
 * environment is available from within the function. The query variables are
 * also available.
 *
 *
 * @param string $_template_file Path to template file.
 * @param bool   $require_once   Whether to require_once or require. Default true.
 */
function load_template( $_template_file, $require_once = true )
{
    if ( $require_once ) {
        require_once( $_template_file );
    } else {
        require( $_template_file );
    }
}
