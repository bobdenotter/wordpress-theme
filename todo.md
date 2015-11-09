Todo's:

 - `add_action` needs to do something.
 - ~~`is_sticky`~~
 - Where does the thumbnail's width and height come from?




-------

Example routes for `routing.yml`

```
wp-homepage:
    path: /
    defaults:
        _controller: 'Bolt\Extension\Bobdenotter\WPTheme\Extension::homepage'

wp-contentlink:
    path: /{contenttypeslug}/{slug}
    defaults:
        _controller: 'Bolt\Extension\Bobdenotter\WPTheme\Extension::record'
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

snippet for LowlevelExveption.php, around line 150:

```php
            // Added for WP-theme extension.
            $match = preg_match('/Call to undefined function ([A-Za-z0-9_-]+)\(\)/i', $error['message'], $matches);
            if (!empty($matches[1])) {
                $functionname = $matches[1];
                $add = <<< EOM

/**
 * Stub for {$functionname}.
 */
function {$functionname}()
{
    WPhelper::stub('{$functionname}', func_get_args());
}

EOM;
                $filename = dirname(dirname(__DIR__)) . '/extensions/local/bobdenotter/wp-theme/wp-functions.php';
                if(file_put_contents($filename, $add, FILE_APPEND)) {
                    echo "<mark>Added $functionname !</mark>";
                }

            }
            // End of added for WP-theme extension.


```