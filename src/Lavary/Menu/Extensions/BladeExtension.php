<?php

/*
|--------------------------------------------------------------------------
| @lm-attrs
|--------------------------------------------------------------------------
|
| Buffers the output if there's any.	
| The output will be passed to mergeStatic()
| where it is merged with item's attributes
|
*/

Blade::extend( function($view, $compiler){

    $pattern = '/(\s*)@lm-attrs\s*\((\$[^)]+)\)/';
    return preg_replace($pattern, 
                       '$1<?php $lm_attrs = $2->attr(); ob_start(); ?>',
                        $view);
});

/*
|--------------------------------------------------------------------------
| @lm-endattrs
|--------------------------------------------------------------------------
|
| Reads the buffer data using ob_get_clean()
| and passes it to MergeStatic(). 
| mergeStatic() takes the static string,
| converts it into a normal array and merges it with others.
| 
*/

Blade::extend( function($view, $compiler){

    $pattern = $compiler->CreatePlainMatcher('lm-endattrs');
    return preg_replace($pattern, 
			           '$1<?php echo \Lavary\Menu\Builder::mergeStatic(ob_get_clean(), $lm_attrs); ?>$2', 
			            $view);
});