<?php
/**
 * Generate PNG favicon from SVG design
 * Run this script once to generate favicon.png
 */

// Create a 64x64 image
$width = 64;
$height = 64;
$image = imagecreatetruecolor($width, $height);

// Define colors
$bg_color = imagecolorallocate($image, 10, 14, 26); // #0A0E1A
$gold_dark = imagecolorallocate($image, 255, 215, 0); // #FFD700
$gold_light = imagecolorallocate($image, 255, 165, 0); // #FFA500

// Fill background
imagefill($image, 0, 0, $bg_color);

// Draw W letter (simplified polygon)
$w_points = [
    16, 20,  // Top left
    20, 20,  // Top left inner
    24, 44,  // Bottom left
    28, 28,  // Middle
    32, 44,  // Bottom right
    36, 20,  // Top right inner
    40, 20,  // Top right
    34, 48,  // Bottom right outer
    30, 36,  // Middle right
    26, 48   // Bottom left outer
];
imagefilledpolygon($image, $w_points, count($w_points) / 2, $gold_dark);

// Draw gear icon at (48, 16)
$gear_x = 48;
$gear_y = 16;
$gear_radius = 6;

// Draw gear circle outline
imageellipse($image, $gear_x, $gear_y, $gear_radius * 2, $gear_radius * 2, $gold_dark);

// Draw center dot
imagefilledellipse($image, $gear_x, $gear_y, 5, 5, $gold_dark);

// Draw gear teeth (simple lines)
imageline($image, $gear_x, $gear_y - $gear_radius, $gear_x, $gear_y - $gear_radius - 3, $gold_dark);
imageline($image, $gear_x, $gear_y + $gear_radius, $gear_x, $gear_y + $gear_radius + 3, $gold_dark);
imageline($image, $gear_x - $gear_radius, $gear_y, $gear_x - $gear_radius - 3, $gear_y, $gold_dark);
imageline($image, $gear_x + $gear_radius, $gear_y, $gear_x + $gear_radius + 3, $gear_y, $gold_dark);

// Save the image
imagepng($image, 'favicon.png');
imagedestroy($image);

echo "Favicon generated successfully! favicon.png created.\n";
?>

