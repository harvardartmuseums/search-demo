<?php

namespace App\Helpers;

use App\Artwork;

// Include Laravel helpers

class ArtworkHelper
{
    public static function image_url($image, $permission_level, $width = 3000, $height = 3000)
    {
        switch ($permission_level) {
            case Artwork::PERMISSION_ALL:
                if ($width || $height) {
                    return self::build_url($image, $width, $height);
                } else {
                    return $image;
                }
                break;
            case Artwork::PERMISSION_RESTRICTED:
                $width = '256';
                $height = '256';

                return self::build_url($image, $width, $height);
            break;
        }
    }

    public static function thumb_url($image)
    {
        $parsed = parse_url($image);

        return $parsed['scheme'].'://'.$parsed['host'].$parsed['path'].'?width=125&height=125';
    }

    public static function zoom_allowed($permission_level)
    {
        switch ($permission_level) {
            case Artwork::PERMISSION_ALL:
                return '1';
            break;
            case Artwork::PERMISSION_RESTRICTED:
            case Artwork::PERMISSION_DENIED:
                return '0';
            break;
        }
    }

    public static function no_pin($permission_level)
    {
        switch ($permission_level) {
            case Artwork::PERMISSION_ALL:
                return '';
            break;
            case Artwork::PERMISSION_RESTRICTED:
            case Artwork::PERMISSION_DENIED:
                return 'nopin="nopin"';
            break;
        }
    }

    protected static function build_url($image, $width, $height)
    {
        $parsed = parse_url($image);

        return $parsed['scheme'].'://'.$parsed['host'].$parsed['path']."?width={$width}&height={$height}";
    }

    public static function isHAMSpecialExhibition($exhibition)
    {
        if (($exhibition->begindate >= '2012-05-01') && (strpos($exhibition->citation, 'Harvard Art Museums, Cambridge')) && (! strpos($exhibition->citation, '32Q:'))) {
            return '1';
        } else {
            return '0';
        }
    }

    public static function addCopyrightLinks($copyright)
    {
        if (strpos($copyright, 'Artists Rights Society (ARS), New York')) {
            return str_replace('Artists Rights Society (ARS), New York',
                        link_to('https://www.arsny.com/', 'Artists Rights Society (ARS), New York'),
                        $copyright);
        } else {
            return $copyright;
        }
    }
}
