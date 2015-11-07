Todo's: 

 - `add_action` needs to do something.
 - ~~`is_sticky`~~
 - Where does the thumbnail's width and height come from?


-------

Example contenttype for `posts`




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
    wpStub('{$functionname}', func_get_args());
}

EOM;
                $filename = dirname(dirname(__DIR__)) . '/extensions/local/bobdenotter/wp-theme/wp_functions.php';
                if(file_put_contents($filename, $add, FILE_APPEND)) {
                    echo "<mark>Added $functionname !</mark>";
                }

            }
            // End of added for WP-theme extension.
```