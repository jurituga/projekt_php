<?php

if (! function_exists('render_stars')) {
    function render_stars(float|int $rating, int $max = 5): string
    {
        $filled = (int) round((float) $rating);
        $filled = max(0, min($max, $filled));
        $empty = $max - $filled;

        return '<span class="stars-display">'
            .'<span class="star-filled">'.str_repeat('&#9733;', $filled).'</span>'
            .'<span class="star-empty">'.str_repeat('&#9734;', $empty).'</span>'
            .'</span>';
    }
}
