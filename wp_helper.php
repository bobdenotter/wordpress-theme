<?php

class WPhelper {

    var $markCssOutputted;

    /**
     * Print out a stub for an un-implemented function
     *
     */
    public function stub($functionname, $arguments)
    {
        global $markCssOutputted;

        $arguments = self::printParameters($arguments);

        if (!$markCssOutputted) {
            echo "<style>mark { background-color: #fff9c0; text-decoration: none; border: 1px solid #DDB; padding: 1px 3px; display: inline-block; font-size: 13px; } </style>";
            $markCssOutputted = true;
        }

        echo " <mark>{$functionname}({$arguments})</mark> ";
    }

    public function printParameters($parameters = array())
    {
        if (empty($parameters)) {
            return;
        }

        $res = [];

        foreach($parameters as $parameter) {

            if (is_array($parameter)) {
                $res[] = " [ " . self::printParameters($parameter) . " ] ";
            } else {
                $res[] = sprintf("<tt>&quot;%s&quot;</tt>", htmlspecialchars((string) $parameter));
            }
        }

        return implode(", ", $res);
    }


}