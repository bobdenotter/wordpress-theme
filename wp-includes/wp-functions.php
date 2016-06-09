<?php

use Bolt\Extension\Bobdenotter\WordpressTheme\WordpressHelper;

function have_posts()
{
    global $posts;

    return (!empty($posts));

}

function the_post()
{
    global $posts, $post;

    $post = array_pop($posts);

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


/**
 * Load header template.
 *
 * Includes the header template for a theme or if a name is specified then a
 * specialised header will be included.
 *
 * For the parameter, if the file is called "header-special.php" then specify
 * "special".
 *
 * @since 1.5.0
 *
 * @param string $name The name of the specialised header.
 */
function get_header( $name = null ) {
    /**
     * Fires before the header template file is loaded.
     *
     * The hook allows a specific header template file to be used in place of the
     * default header template file. If your file is called header-new.php,
     * you would specify the filename in the hook as get_header( 'new' ).
     *
     * @since 2.1.0
     * @since 2.8.0 $name parameter added.
     *
     * @param string $name Name of the specific header file to use.
     */
    do_action( 'get_header', $name );

    $templates = array();
    $name = (string) $name;
    if ( '' !== $name )
        $templates[] = "header-{$name}.php";

    $templates[] = 'header.php';

    // Backward compat code will be removed in a future release
    if ('' == locate_template($templates, true))
        load_template( ABSPATH . WPINC . '/theme-compat/header.php');
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





/**
 * Fire the wp_head action
 *
 * @since 1.2.0
 */
function wp_head() {
    /**
     * Print scripts or data in the head tag on the front end.
     *
     * @since 1.5.0
     */

    do_action( 'wp_head' );
}

/**
 * Fire the wp_footer action
 *
 * @since 1.5.1
 */
function wp_footer() {
    /**
     * Print scripts or data before the closing body tag on the front end.
     *
     * @since 1.5.1
     */
    do_action( 'wp_footer' );
}



/**
 * Display the classes for the body element.
 *
 * @since 2.8.0
 *
 * @param string|array $class One or more classes to add to the class list.
 */
function body_class( $class = '' ) {
    // Separates classes with a single space, collates classes for body element
    echo 'class="' . join( ' ', get_body_class( $class ) ) . '"';
}

/**
 * Retrieve the classes for the body element as an array.
 *
 * @since 2.8.0
 *
 * @global WP_Query $wp_query
 * @global wpdb     $wpdb
 *
 * @param string|array $class One or more classes to add to the class list.
 * @return array Array of classes.
 */
function get_body_class( $class = '' ) {
    global $wp_query, $wpdb, $post;

    $classes = array();

    if ( is_rtl() )
        $classes[] = 'rtl';

    if ( is_front_page() )
        $classes[] = 'home';
    if ( is_home() )
        $classes[] = 'blog';
    if ( is_archive() )
        $classes[] = 'archive';
    if ( is_date() )
        $classes[] = 'date';
    if ( is_search() ) {
        $classes[] = 'search';
        $classes[] = $wp_query->posts ? 'search-results' : 'search-no-results';
    }
    if ( is_paged() )
        $classes[] = 'paged';
    if ( is_attachment() )
        $classes[] = 'attachment';
    if ( is_404() )
        $classes[] = 'error404';

    if ( is_single() ) {
        $post_id = $post['id'];
        $post = $post['values'];

        $classes[] = 'single';
        if ( isset( $post->post_type ) ) {
            $classes[] = 'single-' . sanitize_html_class($post->post_type, $post_id);
            $classes[] = 'postid-' . $post_id;

            // Post Format
            if ( post_type_supports( $post->post_type, 'post-formats' ) ) {
                $post_format = get_post_format( $post->ID );

                if ( $post_format && !is_wp_error($post_format) )
                    $classes[] = 'single-format-' . sanitize_html_class( $post_format );
                else
                    $classes[] = 'single-format-standard';
            }
        }

        if ( is_attachment() ) {
            $mime_type = get_post_mime_type($post_id);
            $mime_prefix = array( 'application/', 'image/', 'text/', 'audio/', 'video/', 'music/' );
            $classes[] = 'attachmentid-' . $post_id;
            $classes[] = 'attachment-' . str_replace( $mime_prefix, '', $mime_type );
        }
    } elseif ( is_archive() ) {
        if ( is_post_type_archive() ) {
            $classes[] = 'post-type-archive';
            $post_type = get_query_var( 'post_type' );
            if ( is_array( $post_type ) )
                $post_type = reset( $post_type );
            $classes[] = 'post-type-archive-' . sanitize_html_class( $post_type );
        } elseif ( is_author() ) {
            $author = $wp_query->get_queried_object();
            $classes[] = 'author';
            if ( isset( $author->user_nicename ) ) {
                $classes[] = 'author-' . sanitize_html_class( $author->user_nicename, $author->ID );
                $classes[] = 'author-' . $author->ID;
            }
        } elseif ( is_category() ) {
            $cat = $wp_query->get_queried_object();
            $classes[] = 'category';
            if ( isset( $cat->term_id ) ) {
                $cat_class = sanitize_html_class( $cat->slug, $cat->term_id );
                if ( is_numeric( $cat_class ) || ! trim( $cat_class, '-' ) ) {
                    $cat_class = $cat->term_id;
                }

                $classes[] = 'category-' . $cat_class;
                $classes[] = 'category-' . $cat->term_id;
            }
        } elseif ( is_tag() ) {
            $tag = $wp_query->get_queried_object();
            $classes[] = 'tag';
            if ( isset( $tag->term_id ) ) {
                $tag_class = sanitize_html_class( $tag->slug, $tag->term_id );
                if ( is_numeric( $tag_class ) || ! trim( $tag_class, '-' ) ) {
                    $tag_class = $tag->term_id;
                }

                $classes[] = 'tag-' . $tag_class;
                $classes[] = 'tag-' . $tag->term_id;
            }
        } elseif ( is_tax() ) {
            $term = $wp_query->get_queried_object();
            if ( isset( $term->term_id ) ) {
                $term_class = sanitize_html_class( $term->slug, $term->term_id );
                if ( is_numeric( $term_class ) || ! trim( $term_class, '-' ) ) {
                    $term_class = $term->term_id;
                }

                $classes[] = 'tax-' . sanitize_html_class( $term->taxonomy );
                $classes[] = 'term-' . $term_class;
                $classes[] = 'term-' . $term->term_id;
            }
        }
    } elseif ( is_page() ) {
        $classes[] = 'page';

        $page_id = $wp_query->get_queried_object_id();

        $post = get_post($page_id);

        $classes[] = 'page-id-' . $page_id;

        if ( get_pages( array( 'parent' => $page_id, 'number' => 1 ) ) ) {
            $classes[] = 'page-parent';
        }

        if ( $post->post_parent ) {
            $classes[] = 'page-child';
            $classes[] = 'parent-pageid-' . $post->post_parent;
        }
        if ( is_page_template() ) {
            $classes[] = 'page-template';

            $template_slug  = get_page_template_slug( $page_id );
            $template_parts = explode( '/', $template_slug );

            foreach ( $template_parts as $part ) {
                $classes[] = 'page-template-' . sanitize_html_class( str_replace( array( '.', '/' ), '-', basename( $part, '.php' ) ) );
            }
            $classes[] = 'page-template-' . sanitize_html_class( str_replace( '.', '-', $template_slug ) );
        } else {
            $classes[] = 'page-template-default';
        }
    }

    if ( is_user_logged_in() )
        $classes[] = 'logged-in';

    if ( is_admin_bar_showing() ) {
        $classes[] = 'admin-bar';
        $classes[] = 'no-customize-support';
    }

    if ( get_background_color() !== get_theme_support( 'custom-background', 'default-color' ) || get_background_image() )
        $classes[] = 'custom-background';

    // $page = $wp_query->get( 'page' );

    // if ( ! $page || $page < 2 )
    //     $page = $wp_query->get( 'paged' );

    // if ( $page && $page > 1 && ! is_404() ) {
    //     $classes[] = 'paged-' . $page;

    //     if ( is_single() )
    //         $classes[] = 'single-paged-' . $page;
    //     elseif ( is_page() )
    //         $classes[] = 'page-paged-' . $page;
    //     elseif ( is_category() )
    //         $classes[] = 'category-paged-' . $page;
    //     elseif ( is_tag() )
    //         $classes[] = 'tag-paged-' . $page;
    //     elseif ( is_date() )
    //         $classes[] = 'date-paged-' . $page;
    //     elseif ( is_author() )
    //         $classes[] = 'author-paged-' . $page;
    //     elseif ( is_search() )
    //         $classes[] = 'search-paged-' . $page;
    //     elseif ( is_post_type_archive() )
    //         $classes[] = 'post-type-paged-' . $page;
    // }

    if ( ! empty( $class ) ) {
        if ( !is_array( $class ) )
            $class = preg_split( '#\s+#', $class );
        $classes = array_merge( $classes, $class );
    } else {
        // Ensure that we always coerce class to being an array.
        $class = array();
    }

    $classes = array_map( 'esc_attr', $classes );

    /**
     * Filter the list of CSS body classes for the current post or page.
     *
     * @since 2.8.0
     *
     * @param array  $classes An array of body classes.
     * @param string $class   A comma-separated list of additional classes added to the body.
     */
    $classes = apply_filters( 'body_class', $classes, $class );

    return array_unique( $classes );
}


function is_front_page()
{
    global $request;

    // Todo, make distinction between 'is_home' and 'is_front_page'. Derp.

    if ($request->get('_route') == 'wp-homepage') {
        return true;
    } else {
        return false;
    }
}

function is_home()
{
    // Todo, make distinction between 'is_home' and 'is_front_page'. Derp.

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


function post_password_required()
{
    return false;
}


function is_attachment()
{
    return false;
}



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
function has_nav_menu($name)
{
    global $app;

    if ($name == 'primary' && ($app['config']->get('menu/primary') == null)) {
        $name = 'main';
    }

    // If we have a menu..
    if ($app['config']->get('menu/' . $name) != null) {
        return true;
    }

    echo "<!-- WordpressTheme: No menu for $name -->";
}

/**
 * Stub for wp_nav_menu.
 */
function wp_nav_menu($args)
{
    global $app;

    if ($args['theme_location'] == 'primary' && ($app['config']->get('menu/primary') == null)) {
        $args['theme_location'] = 'main';
    }

    // If we have a menu..
    if ($app['config']->get('menu/' . $args['theme_location']) != null) {
        echo WordpressHelper::render('wp-twighelpers/wp_nav_menu.twig', ['args' => $args]);
    }
}

/**
 * Stub for get_post_format.
 */
function get_post_format()
{
    return false;
    // WordpressHelper::stub('get_post_format', func_get_args());
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
    global $post;

    if (!is_object($post)) {
        return;
    }

    return (!empty($post->getImage()));
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
    global $post;

    if (!is_object($post)) {
        return;
    }

    // Todo: Conjure the desired width and height from somewhere. Replace hardcoded values.
    $width = 825;
    $height = 510;

    $data = [
        'width' => $width,
        'height' => $height,
        'img' => $post->getImage()
    ];

    echo WordpressHelper::render('wp-twighelpers/post_thumbnail.twig', $data);
}


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
    // WordpressHelper::stub('wp_link_pages', func_get_args());
}

function get_the_author_meta( $field = '', $user_id = false )
{
    global $post;

    if (!is_object($post)) {
        return;
    }

    switch($field) {

        case 'login':
            return $post->user['username'];

        case 'email':
            return $post->user['email'];

        case 'true':
            return $post->user['enabled'];

        case 'description':
            return '-';

        default:
            return false;

    }

    // WordpressHelper::stub('get_the_author_meta', func_get_args());
}

/**
 * Stub for get_avatar.
 */
function get_avatar()
{
    // WordpressHelper::stub('get_avatar', func_get_args());
}

/**
 * Stub for get_the_author.
 */
function get_the_author()
{
    global $post;

    if (!is_object($post)) {
        return;
    }

    return $post->user['displayname'];
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
    global $post;

    if (!is_object($post)) {
        return;
    }

    return $post['sticky'];
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
    global $post;

    if (!is_object($post)) {
        return;
    }

    return $post->link();
}

/**
 * Stub for get_post_format_string.
 */
function get_post_format_string()
{
    WordpressHelper::stub('get_post_format_string', func_get_args());
}

/**
 * Stub for get_post_type.
 */
function get_post_type()
{
    global $post;

    if (!is_object($post)) {
        return;
    }

    return $post->contenttype['singular_slug'];
}

/**
 * Stub for edit_post_link.
 */
function edit_post_link( $text = null, $before = '', $after = '', $id = 0 )
{
    global $post, $currentuser;

    if (!is_object($post) || empty($currentuser['username'])) {
        return;
    }

    $path = \Bolt\Library::path('editcontent', ['contenttypeslug' => $post->contenttype['slug'], 'id' => $post['id']]);

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
    echo $before . apply_filters( 'edit_post_link', $link, $post['id'], $text ) . $after;

}

/**
 * Stub for comments_open.
 */
function comments_open()
{
    global $app;

    $enabled = array_keys($app['extensions']->all());
    $needed = ['Bolt/Disqus', 'Bolt/FacebookComments'];

    return (!empty(array_intersect($needed, $enabled)));
}

/**
 * Stub for have_comments.
 */
function have_comments()
{
    return false;
}

/**
 * Stub for comment_form.
 */
function comment_form()
{
    global $app;

    $enabled = array_keys($app['extensions']->all());

    if (in_array('Bolt/Disqus', $enabled)) {
        echo WordpressHelper::render('wp-twighelpers/comment_form_disqus.twig');
    } elseif (in_array('Bolt/FacebookComments', $enabled)) {
        echo WordpressHelper::render('wp-twighelpers/comment_form_facebook.twig');
    }
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
    global $post, $paths;

    if ( !(is_single() || is_page() || $withcomments) || !is_object($post) )
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
 * Stub for get_permalink.
 */
function get_permalink()
{
    global $post;

    if (!is_object($post)) {
        return;
    }

    return $post->link();
}

/**
 * Stub for get_comments_number.
 */
function get_comments_number()
{
    return '';
}

/**
 * Stub for comments_popup_link.
 */
function comments_popup_link($text = '')
{
    global $post;

    if (!is_object($post) || !comments_open()) {
        return;
    }

    if (empty($text)) {
        $text = __("Leave a comment");
    }

    $link = sprintf('<a href="%s">%s</a>', $post->link(), $text);

    echo $link;
}

/**
 * Stub for get_the_title.
 */
function get_the_title()
{
    global $post;

    if (!is_object($post)) {
        return;
    }

    return $post->title();
}

/**
 * Stub for get_the_content.
 */
function get_the_content()
{
    global $post;

    if (!is_object($post)) {
        return;
    }

    if (isset($post['body'])) {
        return $post['body'];
    } else {
        return $post->excerpt(10000);
    }
}

/**
 * Stub for get_the_ID.
 */
function get_the_ID()
{
    global $post;

    if (!is_object($post)) {
        return;
    }

    if (isset($post['id'])) {
        return $post['id'];
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
    global $post, $config;

    if (!is_object($post)) {
        return;
    }

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
    foreach ( (array) $post->taxonomy as $taxonomy => $terms ) {
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
    global $post;

    if (!is_object($post)) {
        return;
    }

    return $post;
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
    global $post;

    if (!is_object($post)) {
        return;
    }

    $atoms = array();

    if (!empty($post->taxonomy)) {
        foreach($post->taxonomy as $taxonomies) {
            $atoms = array_merge($atoms, $taxonomies);
        }
    }

    return $atoms;
}


/**
 * Stub for get_option.
 */
function get_option($what)
{
    global $app;

    switch ($what) {
        case 'html_type':
            return 'text/html';

        case 'blog_charset':
            return 'UTF-8';

        case 'date_format':
            return '%A %B %e, %Y';

        case 'blogname':
            return $app['config']->get('general/sitename');


        default:
            // WordpressHelper::stub('get_option', func_get_args());
            return '';
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
    echo get_permalink();
}

/**
 * Stub for is_object_in_taxonomy.
 */
function is_object_in_taxonomy()
{
    WordpressHelper::stub('is_object_in_taxonomy', func_get_args());
}

/**
 * Stub for get_the_time.
 */
function get_the_time()
{
    global $post;

    if (!is_object($post)) {
        return;
    }

    $date = new DateTime($post['datecreated']);

    return $date->getTimestamp();
}

/**
 * Stub for get_the_modified_time.
 */
function get_the_modified_time()
{
    global $post;

    if (!is_object($post)) {
        return;
    }

    $date = new DateTime($post['datechanged']);

    return $date->getTimestamp();
}

/**
 * Stub for get_the_date.
 */
function get_the_date()
{
    global $post;

    if (!is_object($post)) {
        return;
    }

    return strftime("%A %B %e, %Y", get_the_time());
}

/**
 * Stub for get_the_modified_date.
 */
function get_the_modified_date()
{
    global $post;

    if (!is_object($post)) {
        return;
    }

    return strftime("%A %B %e, %Y", get_the_modified_time());
}

/**
 * Stub for get_the_category_list.
 */
function get_the_category_list( $separator = '' )
{
    global $post;

    if (!is_object($post) || empty($post->taxonomy['categories'])) {
        return;
    }

    $items = [];

    foreach($post->taxonomy['categories'] as $link => $term) {
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
    global $post;

    if (!is_object($post) || empty($post->taxonomy['tags'])) {
        return;
    }

    $items = [];

    foreach($post->taxonomy['tags'] as $link => $term) {
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
    WordpressHelper::stub('wp_get_current_commenter', func_get_args());
}

/**
 * Stub for get_comments.
 */
function get_comments()
{
    return '';
}

/**
 * Stub for get_query_var.
 */
function get_query_var()
{
    WordpressHelper::stub('get_query_var', func_get_args());
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
    global $post;

    if (!is_object($post)) {
        return;
    }

    $temp_rec = $post->previous();

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
    global $post;

    if (!is_object($post)) {
        return;
    }

    $temp_rec = $post->next();

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
 * Stub for previous_post_link.
 */
function previous_post_link()
{
    echo get_previous_post_link();
}

/**
 * Stub for next_post_link.
 */
function next_post_link()
{
    echo get_next_post_link();
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

/**
 * Stub for is_paged.
 */
function is_paged()
{
    global $app;

    return !$app['storage']->isEmptyPager();
}

/**
 * Stub for is_multi_author.
 */
function is_multi_author()
{
    return false;
}

function the_posts_pagination( $args = array() ) {
    echo get_the_posts_pagination( $args );
}


function get_the_posts_pagination( $args = array() )
{
    return WordpressHelper::render('wp-twighelpers/pager.twig');
}




/**
 * Stub for wp_enqueue_script.
 */
function wp_enqueue_script( $handle, $src = false, $deps = array(), $ver = false, $in_footer = false )
{
    WordpressHelper::enqueueScript($handle, $src, $deps, $ver, $in_footer);
}


/**
 * Stub for wp_enqueue_style.
 */
function wp_enqueue_style( $handle, $src = false, $deps = array(), $ver = false, $media = 'all' )
{
    WordpressHelper::enqueueStyleSheet( $handle, $src, $deps, $ver, $media );
}


/**
 * Stub for wp_style_add_data.
 */
function wp_style_add_data($handle, $key, $value)
{
    WordpressHelper::addStyleData($handle, $key, $value);
}

/**
 * Stub for wp_add_inline_style.
 */
function wp_add_inline_style($handle, $data)
{
    WordpressHelper::enqueueInlineStyle( $handle, $data );
}


/**
 * Retrieve a modified URL query string.
 *
 * You can rebuild the URL and append a new query variable to the URL query by
 * using this function. You can also retrieve the full URL with query data.
 *
 * Adding a single key & value or an associative array. Setting a key value to
 * an empty string removes the key. Omitting oldquery_or_uri uses the $_SERVER
 * value. Additional values provided are expected to be encoded appropriately
 * with urlencode() or rawurlencode().
 *
 * @since 1.5.0
 *
 * @param string|array $param1 Either newkey or an associative_array.
 * @param string       $param2 Either newvalue or oldquery or URI.
 * @param string       $param3 Optional. Old query or URI.
 * @return string New URL query string.
 */
function add_query_arg() {
    $args = func_get_args();
    if ( is_array( $args[0] ) ) {
        if ( count( $args ) < 2 || false === $args[1] )
            $uri = $_SERVER['REQUEST_URI'];
        else
            $uri = $args[1];
    } else {
        if ( count( $args ) < 3 || false === $args[2] )
            $uri = $_SERVER['REQUEST_URI'];
        else
            $uri = $args[2];
    }

    if ( $frag = strstr( $uri, '#' ) )
        $uri = substr( $uri, 0, -strlen( $frag ) );
    else
        $frag = '';

    if ( 0 === stripos( $uri, 'http://' ) ) {
        $protocol = 'http://';
        $uri = substr( $uri, 7 );
    } elseif ( 0 === stripos( $uri, 'https://' ) ) {
        $protocol = 'https://';
        $uri = substr( $uri, 8 );
    } else {
        $protocol = '';
    }

    if ( strpos( $uri, '?' ) !== false ) {
        list( $base, $query ) = explode( '?', $uri, 2 );
        $base .= '?';
    } elseif ( $protocol || strpos( $uri, '=' ) === false ) {
        $base = $uri . '?';
        $query = '';
    } else {
        $base = '';
        $query = $uri;
    }

    wp_parse_str( $query, $qs );
    $qs = urlencode_deep( $qs ); // this re-URL-encodes things that were already in the query string
    if ( is_array( $args[0] ) ) {
        foreach ( $args[0] as $k => $v ) {
            $qs[ $k ] = $v;
        }
    } else {
        $qs[ $args[0] ] = $args[1];
    }

    foreach ( $qs as $k => $v ) {
        if ( $v === false )
            unset( $qs[$k] );
    }

    $ret = build_query( $qs );
    $ret = trim( $ret, '?' );
    $ret = preg_replace( '#=(&|$)#', '$1', $ret );
    $ret = $protocol . $base . $ret . $frag;
    $ret = rtrim( $ret, '?' );
    return $ret;
}



/**
 * Stub for wp_attachment_is_image.
 */
function wp_attachment_is_image()
{
    return false;
    // WordpressHelper::stub('wp_attachment_is_image', func_get_args());
}


/**
 * Stub for get_adjacent_post.
 */
function get_adjacent_post()
{
    WordpressHelper::stub('get_adjacent_post', func_get_args());
}



/**
 * Build URL query based on an associative and, or indexed array.
 *
 * This is a convenient function for easily building url queries. It sets the
 * separator to '&' and uses _http_build_query() function.
 *
 * @since 2.3.0
 *
 * @see _http_build_query() Used to build the query
 * @see http://us2.php.net/manual/en/function.http-build-query.php for more on what
 *      http_build_query() does.
 *
 * @param array $data URL-encode key/value pairs.
 * @return string URL-encoded string.
 */
function build_query( $data ) {
    return _http_build_query( $data, null, '&', '', false );
}

/**
 * From php.net (modified by Mark Jaquith to behave like the native PHP5 function).
 *
 * @since 3.2.0
 * @access private
 *
 * @see http://us1.php.net/manual/en/function.http-build-query.php
 *
 * @param array|object  $data       An array or object of data. Converted to array.
 * @param string        $prefix     Optional. Numeric index. If set, start parameter numbering with it.
 *                                  Default null.
 * @param string        $sep        Optional. Argument separator; defaults to 'arg_separator.output'.
 *                                  Default null.
 * @param string        $key        Optional. Used to prefix key name. Default empty.
 * @param bool          $urlencode  Optional. Whether to use urlencode() in the result. Default true.
 *
 * @return string The query string.
 */
function _http_build_query( $data, $prefix = null, $sep = null, $key = '', $urlencode = true ) {
    $ret = array();

    foreach ( (array) $data as $k => $v ) {
        if ( $urlencode)
            $k = urlencode($k);
        if ( is_int($k) && $prefix != null )
            $k = $prefix.$k;
        if ( !empty($key) )
            $k = $key . '%5B' . $k . '%5D';
        if ( $v === null )
            continue;
        elseif ( $v === false )
            $v = '0';

        if ( is_array($v) || is_object($v) )
            array_push($ret,_http_build_query($v, '', $sep, $k, $urlencode));
        elseif ( $urlencode )
            array_push($ret, $k.'='.urlencode($v));
        else
            array_push($ret, $k.'='.$v);
    }

    if ( null === $sep )
        $sep = ini_get('arg_separator.output');

    return implode($sep, $ret);
}

/**
 * Stub for wp_title.
 */
function wp_title( $sep = '&raquo;', $display = true, $seplocation = '' )
{
    global $post;

    $data = [
        'post' => $post,
    ];

    $title = WordpressHelper::render('wp-twighelpers/title.twig', $data);

    if ($display) {
        echo $title;
    } else {
        return $title;
    }
}

/**
 * Stub for is_404.
 */
function is_404()
{
    return false;
}

/**
 * Stub for is_archive.
 */
function is_archive()
{
    return false;
}

/**
 * Stub for is_search.
 */
function is_search()
{
    global $request;

    $route = $request->get('_route');
    $okroutes = [ 'search' ];

    return in_array($route, $okroutes);
}

/**
 * Stub for get_post_custom.
 */
function get_post_custom()
{
    WordpressHelper::stub('get_post_custom', func_get_args());
}


/**
 * Stub for the_time.
 */
function the_time()
{
    WordpressHelper::stub('the_time', func_get_args());
}

/**
 * Stub for the_author.
 */
function the_author()
{
    WordpressHelper::stub('the_author', func_get_args());
}

/**
 * Stub for the_category.
 */
function the_category()
{
    WordpressHelper::stub('the_category', func_get_args());
}

/**
 * Stub for do_shortcode.
 */
function do_shortcode()
{
    WordpressHelper::stub('do_shortcode', func_get_args());
}

/**
 * Stub for get_the_tags.
 */
function get_the_tags()
{
    WordpressHelper::stub('get_the_tags', func_get_args());
}



// /**
//  * Stub for get_header_image.
//  */
// function get_header_image()
// {
//     WordpressHelper::stub('get_header_image', func_get_args());
// }

/**
 * Stub for esc_html_e.
 */
function esc_html_e()
{
    WordpressHelper::stub('esc_html_e', func_get_args());
}

/**
 * Stub for wp_load_alloptions.
 */
function wp_load_alloptions()
{
    WordpressHelper::stub('wp_load_alloptions', func_get_args());
}

/**
 * Stub for is_rtl.
 */
function is_rtl()
{
    return false;
}

/**
 * Stub for get_post_meta.
 */
function get_post_meta()
{
    WordpressHelper::stub('get_post_meta', func_get_args());
}

/**
 * Stub for the_title_attribute.
 */
function the_title_attribute()
{
    WordpressHelper::stub('the_title_attribute', func_get_args());
}

/**
 * Stub for _nx.
 */
function _nx()
{
    WordpressHelper::stub('_nx', func_get_args());
}

/**
 * Stub for number_format_i18n.
 */
function number_format_i18n()
{
    WordpressHelper::stub('number_format_i18n', func_get_args());
}

/**
 * Stub for get_comment_pages_count.
 */
function get_comment_pages_count()
{
    WordpressHelper::stub('get_comment_pages_count', func_get_args());
}

/**
 * Stub for wp_list_comments.
 */
function wp_list_comments()
{
    WordpressHelper::stub('wp_list_comments', func_get_args());
}

/**
 * Retrieve a list of protocols to allow in HTML attributes.
 *
 * @since 3.3.0
 * @since 4.3.0 Added 'webcal' to the protocols array.
 *
 * @see wp_kses()
 * @see esc_url()
 *
 * @staticvar array $protocols
 *
 * @return array Array of allowed protocols. Defaults to an array containing 'http', 'https',
 *               'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet',
 *               'mms', 'rtsp', 'svn', 'tel', 'fax', 'xmpp', and 'webcal'.
 */
function wp_allowed_protocols() {
    static $protocols = array();

    if ( empty( $protocols ) ) {
        $protocols = array( 'http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet', 'mms', 'rtsp', 'svn', 'tel', 'fax', 'xmpp', 'webcal' );

        /**
         * Filter the list of protocols allowed in HTML attributes.
         *
         * @since 3.0.0
         *
         * @param array $protocols Array of allowed protocols e.g. 'http', 'ftp', 'tel', and more.
         */
        $protocols = apply_filters( 'kses_allowed_protocols', $protocols );
    }
    return $protocols;
}

/**
 * Stub for get_file_data.
 */
function get_file_data()
{
    WordpressHelper::stub('get_file_data', func_get_args());
}

/**
 * Stub for load_theme_textdomain.
 */
function load_theme_textdomain()
{
    WordpressHelper::stub('load_theme_textdomain', func_get_args());
}

// /**
//  * Stub for add_theme_support.
//  */
// function add_theme_support()
// {
//     WordpressHelper::stub('add_theme_support', func_get_args());
// }

/**
 * Stub for register_nav_menus.
 */
function register_nav_menus()
{
    WordpressHelper::stub('register_nav_menus', func_get_args());
}

/**
 * Stub for set_post_thumbnail_size.
 */
function set_post_thumbnail_size()
{
    WordpressHelper::stub('set_post_thumbnail_size', func_get_args());
}

/**
 * Stub for add_image_size.
 */
function add_image_size()
{
    WordpressHelper::stub('add_image_size', func_get_args());
}

/**
 * Stub for current_user_can.
 */
function current_user_can()
{
    WordpressHelper::stub('current_user_can', func_get_args());
}

/**
 * Stub for wp_page_menu.
 */
function wp_page_menu()
{
    WordpressHelper::stub('wp_page_menu', func_get_args());
}

/**
 * Stub for is_page_template.
 */
function is_page_template()
{
    WordpressHelper::stub('is_page_template', func_get_args());
}

/**
 * Stub for get_search_form.
 */
function get_search_form()
{
    WordpressHelper::stub('get_search_form', func_get_args());
}

/**
 * Stub for wp_get_archives.
 */
function wp_get_archives()
{
    WordpressHelper::stub('wp_get_archives', func_get_args());
}

/**
 * Stub for wp_register.
 */
function wp_register()
{
    WordpressHelper::stub('wp_register', func_get_args());
}

/**
 * Stub for wp_loginout.
 */
function wp_loginout()
{
    WordpressHelper::stub('wp_loginout', func_get_args());
}

/**
 * Stub for wp_meta.
 */
function wp_meta()
{
    WordpressHelper::stub('wp_meta', func_get_args());
}

/**
 * Stub for esc_attr__.
 */
function esc_attr__()
{
    WordpressHelper::stub('esc_attr__', func_get_args());
}


/**
 * Stub for is_author.
 */
function is_author()
{
    return false;
}


/**
 * Stub for is_multisite.
 */
function is_multisite()
{
    return false;
}



/**
 * Temporarily suspend cache additions.
 *
 * Stops more data being added to the cache, but still allows cache retrieval.
 * This is useful for actions, such as imports, when a lot of data would otherwise
 * be almost uselessly added to the cache.
 *
 * Suspension lasts for a single page load at most. Remember to call this
 * function again if you wish to re-enable cache adds earlier.
 *
 * @since 3.3.0
 *
 * @staticvar bool $_suspend
 *
 * @param bool $suspend Optional. Suspends additions if true, re-enables them if false.
 * @return bool The current suspend setting
 */
function wp_suspend_cache_addition( $suspend = null ) {
    static $_suspend = false;

    if ( is_bool( $suspend ) )
        $_suspend = $suspend;

    return $_suspend;
}

/**
 * Suspend cache invalidation.
 *
 * Turns cache invalidation on and off. Useful during imports where you don't wont to do
 * invalidations every time a post is inserted. Callers must be sure that what they are
 * doing won't lead to an inconsistent cache when invalidation is suspended.
 *
 * @since 2.7.0
 *
 * @global bool $_wp_suspend_cache_invalidation
 *
 * @param bool $suspend Optional. Whether to suspend or enable cache invalidation. Default true.
 * @return bool The current suspend setting.
 */
function wp_suspend_cache_invalidation( $suspend = true ) {
    global $_wp_suspend_cache_invalidation;

    $current_suspend = $_wp_suspend_cache_invalidation;
    $_wp_suspend_cache_invalidation = $suspend;
    return $current_suspend;
}



/**
 * Set the scheme for a URL
 *
 * @since 3.4.0
 *
 * @param string $url    Absolute url that includes a scheme
 * @param string $scheme Optional. Scheme to give $url. Currently 'http', 'https', 'login', 'login_post', 'admin', or 'relative'.
 * @return string $url URL with chosen scheme.
 */
function set_url_scheme( $url, $scheme = null ) {
    $orig_scheme = $scheme;

    if ( ! $scheme ) {
        $scheme = is_ssl() ? 'https' : 'http';
    } elseif ( $scheme === 'admin' || $scheme === 'login' || $scheme === 'login_post' || $scheme === 'rpc' ) {
        $scheme = is_ssl() || force_ssl_admin() ? 'https' : 'http';
    } elseif ( $scheme !== 'http' && $scheme !== 'https' && $scheme !== 'relative' ) {
        $scheme = is_ssl() ? 'https' : 'http';
    }

    $url = trim( $url );
    if ( substr( $url, 0, 2 ) === '//' )
        $url = 'http:' . $url;

    if ( 'relative' == $scheme ) {
        $url = ltrim( preg_replace( '#^\w+://[^/]*#', '', $url ) );
        if ( $url !== '' && $url[0] === '/' )
            $url = '/' . ltrim($url , "/ \t\n\r\0\x0B" );
    } else {
        $url = preg_replace( '#^\w+://#', $scheme . '://', $url );
    }

    /**
     * Filter the resulting URL after setting the scheme.
     *
     * @since 3.4.0
     *
     * @param string $url         The complete URL including scheme and path.
     * @param string $scheme      Scheme applied to the URL. One of 'http', 'https', or 'relative'.
     * @param string $orig_scheme Scheme requested for the URL. One of 'http', 'https', 'login',
     *                            'login_post', 'admin', 'rpc', or 'relative'.
     */
    return apply_filters( 'set_url_scheme', $url, $scheme, $orig_scheme );
}


/**
 * Determine if SSL is used.
 *
 * @since 2.6.0
 *
 * @return bool True if SSL, false if not used.
 */
function is_ssl() {
    if ( isset($_SERVER['HTTPS']) ) {
        if ( 'on' == strtolower($_SERVER['HTTPS']) )
            return true;
        if ( '1' == $_SERVER['HTTPS'] )
            return true;
    } elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
        return true;
    }
    return false;
}


/**
 * Stub for is_date.
 */
function is_date()
{
    return false;
}

/**
 * Stub for is_user_logged_in.
 */
function is_user_logged_in()
{
    global $app;

    $currentuser = $app['users']->getCurrentUser();

    return (!empty($currentuser));
}

/**
 * Stub for is_admin_bar_showing.
 */
function is_admin_bar_showing()
{
    return false;
}

/**
 * Stub for esc_attr_e.
 */
function esc_attr_e()
{
    WordpressHelper::stub('esc_attr_e', func_get_args());
}

/**
 * Stub for has_excerpt.
 */
function has_excerpt()
{
    WordpressHelper::stub('has_excerpt', func_get_args());
}


/**
 * Mark something as being incorrectly called.
 *
 * There is a hook doing_it_wrong_run that will be called that can be used
 * to get the backtrace up to what file and function called the deprecated
 * function.
 *
 * The current behavior is to trigger a user error if WP_DEBUG is true.
 *
 * @since 3.1.0
 * @access private
 *
 * @param string $function The function that was called.
 * @param string $message  A message explaining what has been done incorrectly.
 * @param string $version  The version of WordPress where the message was added.
 */
function _doing_it_wrong( $function, $message, $version ) {

    /**
     * Fires when the given function is being used incorrectly.
     *
     * @since 3.1.0
     *
     * @param string $function The function that was called.
     * @param string $message  A message explaining what has been done incorrectly.
     * @param string $version  The version of WordPress where the message was added.
     */
    do_action( 'doing_it_wrong_run', $function, $message, $version );

    /**
     * Filter whether to trigger an error for _doing_it_wrong() calls.
     *
     * @since 3.1.0
     *
     * @param bool $trigger Whether to trigger the error for _doing_it_wrong() calls. Default true.
     */
    if ( WP_DEBUG && apply_filters( 'doing_it_wrong_trigger_error', true ) ) {
        if ( function_exists( '__' ) ) {
            $version = is_null( $version ) ? '' : sprintf( __( '(This message was added in version %s.)' ), $version );
            /* translators: %s: Codex URL */
            $message .= ' ' . sprintf( __( 'Please see <a href="%s">Debugging in WordPress</a> for more information.' ),
                    __( 'https://codex.wordpress.org/Debugging_in_WordPress' )
                );
            trigger_error( sprintf( __( '%1$s was called <strong>incorrectly</strong>. %2$s %3$s' ), $function, $message, $version ) );
        } else {
            $version = is_null( $version ) ? '' : sprintf( '(This message was added in version %s.)', $version );
            $message .= sprintf( ' Please see <a href="%s">Debugging in WordPress</a> for more information.',
                'https://codex.wordpress.org/Debugging_in_WordPress'
            );
            trigger_error( sprintf( '%1$s was called <strong>incorrectly</strong>. %2$s %3$s', $function, $message, $version ) );
        }
    }
}



/**
 * Encode a variable into JSON, with some sanity checks.
 *
 * @since 4.1.0
 *
 * @param mixed $data    Variable (usually an array or object) to encode as JSON.
 * @param int   $options Optional. Options to be passed to json_encode(). Default 0.
 * @param int   $depth   Optional. Maximum depth to walk through $data. Must be
 *                       greater than 0. Default 512.
 * @return string|false The JSON encoded string, or false if it cannot be encoded.
 */
function wp_json_encode( $data, $options = 0, $depth = 512 ) {
    /*
     * json_encode() has had extra params added over the years.
     * $options was added in 5.3, and $depth in 5.5.
     * We need to make sure we call it with the correct arguments.
     */
    if ( version_compare( PHP_VERSION, '5.5', '>=' ) ) {
        $args = array( $data, $options, $depth );
    } elseif ( version_compare( PHP_VERSION, '5.3', '>=' ) ) {
        $args = array( $data, $options );
    } else {
        $args = array( $data );
    }

    // Prepare the data for JSON serialization.
    $data = _wp_json_prepare_data( $data );

    $json = @call_user_func_array( 'json_encode', $args );

    // If json_encode() was successful, no need to do more sanity checking.
    // ... unless we're in an old version of PHP, and json_encode() returned
    // a string containing 'null'. Then we need to do more sanity checking.
    if ( false !== $json && ( version_compare( PHP_VERSION, '5.5', '>=' ) || false === strpos( $json, 'null' ) ) )  {
        return $json;
    }

    try {
        $args[0] = _wp_json_sanity_check( $data, $depth );
    } catch ( Exception $e ) {
        return false;
    }

    return call_user_func_array( 'json_encode', $args );
}

/**
 * Perform sanity checks on data that shall be encoded to JSON.
 *
 * @ignore
 * @since 4.1.0
 * @access private
 *
 * @see wp_json_encode()
 *
 * @param mixed $data  Variable (usually an array or object) to encode as JSON.
 * @param int   $depth Maximum depth to walk through $data. Must be greater than 0.
 * @return mixed The sanitized data that shall be encoded to JSON.
 */
function _wp_json_sanity_check( $data, $depth ) {
    if ( $depth < 0 ) {
        throw new Exception( 'Reached depth limit' );
    }

    if ( is_array( $data ) ) {
        $output = array();
        foreach ( $data as $id => $el ) {
            // Don't forget to sanitize the ID!
            if ( is_string( $id ) ) {
                $clean_id = _wp_json_convert_string( $id );
            } else {
                $clean_id = $id;
            }

            // Check the element type, so that we're only recursing if we really have to.
            if ( is_array( $el ) || is_object( $el ) ) {
                $output[ $clean_id ] = _wp_json_sanity_check( $el, $depth - 1 );
            } elseif ( is_string( $el ) ) {
                $output[ $clean_id ] = _wp_json_convert_string( $el );
            } else {
                $output[ $clean_id ] = $el;
            }
        }
    } elseif ( is_object( $data ) ) {
        $output = new stdClass;
        foreach ( $data as $id => $el ) {
            if ( is_string( $id ) ) {
                $clean_id = _wp_json_convert_string( $id );
            } else {
                $clean_id = $id;
            }

            if ( is_array( $el ) || is_object( $el ) ) {
                $output->$clean_id = _wp_json_sanity_check( $el, $depth - 1 );
            } elseif ( is_string( $el ) ) {
                $output->$clean_id = _wp_json_convert_string( $el );
            } else {
                $output->$clean_id = $el;
            }
        }
    } elseif ( is_string( $data ) ) {
        return _wp_json_convert_string( $data );
    } else {
        return $data;
    }

    return $output;
}

/**
 * Convert a string to UTF-8, so that it can be safely encoded to JSON.
 *
 * @ignore
 * @since 4.1.0
 * @access private
 *
 * @see _wp_json_sanity_check()
 *
 * @staticvar bool $use_mb
 *
 * @param string $string The string which is to be converted.
 * @return string The checked string.
 */
function _wp_json_convert_string( $string ) {
    static $use_mb = null;
    if ( is_null( $use_mb ) ) {
        $use_mb = function_exists( 'mb_convert_encoding' );
    }

    if ( $use_mb ) {
        $encoding = mb_detect_encoding( $string, mb_detect_order(), true );
        if ( $encoding ) {
            return mb_convert_encoding( $string, 'UTF-8', $encoding );
        } else {
            return mb_convert_encoding( $string, 'UTF-8', 'UTF-8' );
        }
    } else {
        return wp_check_invalid_utf8( $string, true );
    }
}

/**
 * Prepares response data to be serialized to JSON.
 *
 * This supports the JsonSerializable interface for PHP 5.2-5.3 as well.
 *
 * @ignore
 * @since 4.4.0
 * @access private
 *
 * @param mixed $data Native representation.
 * @return bool|int|float|null|string|array Data ready for `json_encode()`.
 */
function _wp_json_prepare_data( $data ) {
    if ( ! defined( 'WP_JSON_SERIALIZE_COMPATIBLE' ) || WP_JSON_SERIALIZE_COMPATIBLE === false ) {
        return $data;
    }

    switch ( gettype( $data ) ) {
        case 'boolean':
        case 'integer':
        case 'double':
        case 'string':
        case 'NULL':
            // These values can be passed through.
            return $data;

        case 'array':
            // Arrays must be mapped in case they also return objects.
            return array_map( '_wp_json_prepare_data', $data );

        case 'object':
            // If this is an incomplete object (__PHP_Incomplete_Class), bail.
            if ( ! is_object( $data ) ) {
                return null;
            }

            if ( $data instanceof JsonSerializable ) {
                $data = $data->jsonSerialize();
            } else {
                $data = get_object_vars( $data );
            }

            // Now, pass the array (or whatever was returned from jsonSerialize through).
            return _wp_json_prepare_data( $data );

        default:
            return null;
    }
}

/**
 * Send a JSON response back to an Ajax request.
 *
 * @since 3.5.0
 *
 * @param mixed $response Variable (usually an array or object) to encode as JSON,
 *                        then print and die.
 */
function wp_send_json( $response ) {
    @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
    echo wp_json_encode( $response );
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
        wp_die();
    else
        die;
}

/**
 * Send a JSON response back to an Ajax request, indicating success.
 *
 * @since 3.5.0
 *
 * @param mixed $data Data to encode as JSON, then print and die.
 */
function wp_send_json_success( $data = null ) {
    $response = array( 'success' => true );

    if ( isset( $data ) )
        $response['data'] = $data;

    wp_send_json( $response );
}

/**
 * Send a JSON response back to an Ajax request, indicating failure.
 *
 * If the `$data` parameter is a {@see WP_Error} object, the errors
 * within the object are processed and output as an array of error
 * codes and corresponding messages. All other types are output
 * without further processing.
 *
 * @since 3.5.0
 * @since 4.1.0 The `$data` parameter is now processed if a {@see WP_Error}
 *              object is passed in.
 *
 * @param mixed $data Data to encode as JSON, then print and die.
 */
function wp_send_json_error( $data = null ) {
    $response = array( 'success' => false );

    if ( isset( $data ) ) {
        if ( is_wp_error( $data ) ) {
            $result = array();
            foreach ( $data->errors as $code => $messages ) {
                foreach ( $messages as $message ) {
                    $result[] = array( 'code' => $code, 'message' => $message );
                }
            }

            $response['data'] = $result;
        } else {
            $response['data'] = $data;
        }
    }

    wp_send_json( $response );
}

/**
 * Stub for the_archive_title.
 */
function the_archive_title()
{
    WordpressHelper::stub('the_archive_title', func_get_args());
}

/**
 * Stub for the_archive_description.
 */
function the_archive_description()
{
    WordpressHelper::stub('the_archive_description', func_get_args());
}

/**
 * Stub for get_search_query.
 */
function get_search_query()
{
    WordpressHelper::stub('get_search_query', func_get_args());
}

/**
 * Stub for has_category.
 */
function has_category()
{
    WordpressHelper::stub('has_category', func_get_args());
}
