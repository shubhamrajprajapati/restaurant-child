@props(['colorTheme'])

<?php
// Group 1
$tc1 = $colorTheme?->otheme_1;
$tc2 = $colorTheme?->otheme_2;
$tc3 = $colorTheme?->otheme_3;
$tc4 = $colorTheme?->otheme_4;
$ltc1 = $colorTheme?->olight_1;
$ltc2 = $colorTheme?->olight_2;
$ltc3 = $colorTheme?->olight_3;
$ltc4 = $colorTheme?->olight_4;
$dtc1 = $colorTheme?->odark_1;
$dtc2 = $colorTheme?->odark_2;
$dtc3 = $colorTheme?->odark_3;
$dtc4 = $colorTheme?->odark_4;

// Group 2
$mtc1 = $colorTheme?->omarquee_1;
$mtc2 = $colorTheme?->omarquee_2;

// Group 3
$txtwhite = $colorTheme?->otext_white;
$txtblack = $colorTheme?->otext_black;

// Group 4
$backwhite = $colorTheme?->obg_white;
$backblack = $colorTheme?->obg_black;

// Group 5
$white = $colorTheme?->oneutral_white;
$black = $colorTheme?->oneutral_black;
$gray = $colorTheme?->oneutral_gray;
$lgray = $colorTheme?->oneutral_light_gray;
$elgray = $colorTheme?->oneutral_x_light_gray;
$dgray = $colorTheme?->oneutral_dark_gray;
?>

<style>
    :root {
        --white: {{ $white }};
        --black: {{ $black }};
        --gray: {{ $gray }};
        --lgray: {{ $lgray }};
        --elgray: {{ $elgray }};
        --dgray: {{ $dgray }};

        --tc_1: {{ $tc1 }};
        --tc_2: {{ $tc2 }};

        --ltc_1: {{ $ltc1 }};
        --ltc_2: {{ $ltc2 }};

        --mtc_1: #e6966b;
        --mtc_2: #8aad72;

        --dtc_1: {{ $dtc1 }};
        --dtc_2: {{ $dtc2 }};

        --m_color: #000;
        --m_back: #f0f0f0;
    }
</style>
