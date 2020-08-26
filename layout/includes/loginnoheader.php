<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Version details
 *
 * @package   theme_adaptable
 * @copyright 2019 G J Barnard (http://moodle.org/user/profile.php?id=442195)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die();

// Set HTTPS if needed.
if (empty($CFG->loginhttps)) {
    $wwwroot = $CFG->wwwroot;
} else {
    $wwwroot = str_replace("http://", "https://", $CFG->wwwroot);
}

// JS call. Fix for #85 where alerts could not be dismissed.
$PAGE->requires->js_call_amd('theme_adaptable/bsoptions', 'init', array());

$responsivealerts = $PAGE->theme->settings->responsivealerts;

// Select fonts used.
$fontname = '';
$fontheadername = '';
$fonttitlename = '';
$fontweight = '';
$fontheaderweight = '';
$fonttitleweight = '';
$fontssubset = '';

switch ($PAGE->theme->settings->fontname) {
    case 'default':
        // Get the default font used by the browser.
    break;

    default:
        // Get the Google fonts.
        $fontname = str_replace(" ", "+", $PAGE->theme->settings->fontname);
        $fontheadername = str_replace(" ", "+", $PAGE->theme->settings->fontheadername);
        $fonttitlename = str_replace(" ", "+", $PAGE->theme->settings->fonttitlename);

        $fontweight = ':400,400i';
        $fontheaderweight = ':400,400i';
        $fonttitleweight = ':700,700i';
        $fontssubset = '';

        // Get the Google Font weights.
        $fontweight = ':'.$PAGE->theme->settings->fontweight.','.$PAGE->theme->settings->fontweight.'i';
        $fontheaderweight = ':'.$PAGE->theme->settings->fontheaderweight.','.$PAGE->theme->settings->fontheaderweight.'i';
        $fonttitleweight = ':'.$PAGE->theme->settings->fonttitleweight.','.$PAGE->theme->settings->fonttitleweight.'i';

        // Get the Google fonts subset.
        if (!empty($PAGE->theme->settings->fontsubset)) {
            $fontssubset = '&subset='.$PAGE->theme->settings->fontsubset;
        } else {
            $fontssubset = '';
        }
    break;
}
$standardscreenwidthclass = 'standard';
if (!empty($PAGE->theme->settings->standardscreenwidth)) {
    $standardscreenwidthclass = $PAGE->theme->settings->standardscreenwidth;
}

// HTML header.
echo $OUTPUT->doctype();
?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <title><?php echo $OUTPUT->page_title(); ?></title>
    <link rel="icon" href="<?php echo $OUTPUT->favicon(); ?>" />

<?php

theme_adaptable_initialise_full($PAGE);
$setfull = theme_adaptable_get_full();

// HTML head.
echo $OUTPUT->standard_head_html() ?>
    <!-- CSS print media -->
    <link rel="stylesheet" type="text/css" href="<?php echo $wwwroot; ?>/theme/adaptable/style/print.css" media="print">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Twitter Card data -->
    <meta name="twitter:card" value="summary">
    <meta name="twitter:site" value="<?php echo $SITE->fullname; ?>" />
    <meta name="twitter:title" value="<?php echo $OUTPUT->page_title(); ?>" />

    <!-- Open Graph data -->
    <meta property="og:title" content="<?php echo $OUTPUT->page_title(); ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?php echo $wwwroot; ?>" />
    <meta name="og:site_name" value="<?php echo $SITE->fullname; ?>" />

    <!-- Chrome, Firefox OS and Opera on Android topbar color -->
    <meta name="theme-color" content="<?php echo $PAGE->theme->settings->maincolor; ?>" />

    <!-- Windows Phone topbar color -->
    <meta name="msapplication-navbutton-color" content="<?php echo $PAGE->theme->settings->maincolor; ?>" />

    <!-- iOS Safari topbar color -->
    <meta name="apple-mobile-web-app-status-bar-style" content="<?php echo $PAGE->theme->settings->maincolor; ?>" />

    <?php
    // Load fonts.
    if ((!empty($fontname)) && ($fontname != 'default') && ($fontname != 'custom')) {
        ?>
    <!-- Load Google Fonts -->
    <link href='https://fonts.googleapis.com/css?family=<?php echo $fontname.$fontweight.$fontssubset; ?>'
    rel='stylesheet'
    type='text/css'>
    <?php
    }
    ?>

    <?php
    if ((!empty($fontheadername)) && ($fontheadername != 'default') && ($fontname != 'custom')) {
    ?>
        <link href='https://fonts.googleapis.com/css?family=<?php echo $fontheadername.$fontheaderweight.$fontssubset; ?>'
        rel='stylesheet'
        type='text/css'>
    <?php
    }
    ?>

    <?php
    if ((!empty($fonttitlename)) && ($fonttitlename != 'default') && ($fontname != 'custom')) {
    ?>
        <link href='https://fonts.googleapis.com/css?family=<?php echo $fonttitlename.$fonttitleweight.$fontssubset; ?>'
        rel='stylesheet'
        type='text/css'>
    <?php
    }
    ?>
</head>

<body <?php echo $OUTPUT->body_attributes(array('two-column')); ?>>

<?php
echo $OUTPUT->standard_top_of_body_html();

// Development or wrong moodle version alert.
// echo $OUTPUT->get_dev_alert();.
?>

<div id="page" class="container-fluid <?php echo "$setfull $standardscreenwidthclass"; ?>">

<?php
    // Display alerts.
    echo $OUTPUT->get_alert_messages();
