<?php

function devel_example_extra($wildcard){
    $content = array(
        '#type' => 'markup',
        '#markup' => '<p>'.t('The wildcard contains the value "%wildcard".', array('%wildcard' => $wildcard)).'</p>',
    );
    
    return $content;
}