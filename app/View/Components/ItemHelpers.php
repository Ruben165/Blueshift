<?php

    function getBerat($input)
    {
        if(!isset(explode('@', $input)[1])){
            return '';
        }

        if(!isset(explode(',', explode('@', $input)[1])[0])){
            return '';
        }

        $parts = explode(',', explode('@', $input)[1]);
        if (isset($parts[0])) {
            return $parts[0];
        }
        return '';
    }
?>