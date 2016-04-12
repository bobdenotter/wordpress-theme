Todo's:

 - `add_action` needs to do something.
 - ~~`is_sticky`~~
 - Where does the thumbnail's width and height come from?

 - "defaults" must be added to theme's config.yml.
 See 'vantage's settings.php
    $defaults['logo_header_text'] = __('Call me! Maybe?', 'vantage');

Added 2016-04-10:
 - How to "trigger" copying of assets from assets/ folder to public/
 - http://wptheme.localhost/extensions/bobdenotter/Wordpress-theme/Wordpress-theme/assets/jquery-2.2.3.min.js < does that name make sense?




-------

Example routes for `routing.yml`

```
wp-homepage:
    path: /
    defaults:
        _controller: 'Bolt\Extension\Bobdenotter\WordpressTheme\WordpressThemeExtension::homepage'

wp-contentlink:
    path: /{contenttypeslug}/{slug}
    defaults:
        _controller: 'Bolt\Extension\Bobdenotter\WordpressTheme\WordpressThemeExtension::record'
    requirements:
        contenttypeslug: controller.requirement:anyContentType
```



-------

Example contenttype for `posts`

```

posts:
    name: Posts
    singular_name: Post
    fields:
        title:
            type: text
            class: large
            group: content

        slug:
            type: slug
            uses: title
        teaser:
            type: html
            height: 150px
        image:
            type: image
        body:
            type: html
            height: 300px
    taxonomy: [ categories, tags ]
    listing_records: 10
    default_status: publish
    sort: -datepublish
    recordsperpage: 10
    icon_many: "fa:wordpress"
    icon_one: "fa:wordpress"

```


-------

snippet for LowlevelException.php, around line 150:

```php
            // Added for WordpressTheme extension.
            $match = preg_match('/Call to undefined function ([A-Za-z0-9_-]+)\(\)/i', $error['message'], $matches);
            if (!empty($matches[1])) {
                $functionname = $matches[1];
                $add = <<< EOM

/**
 * Stub for {$functionname}.
 */
function {$functionname}()
{
    WordpressHelper::stub('{$functionname}', func_get_args());
}

EOM;
                $filename = dirname(dirname(dirname(dirname(dirname(__DIR__))))) .
                    '/extensions/local/bobdenotter/wordpress-theme/wp-includes/wp-functions.php';
                if(file_put_contents($filename, $add, FILE_APPEND)) {
                   echo "<mark>Added $functionname !</mark>";
                }

            }
            // End of added for WordpressTheme extension.

```
