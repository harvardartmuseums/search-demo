<?php

namespace App\Helpers;

use App\Article;
use App\Events;
use App\FeaturedCustomArticle;
use App\FeaturedExhibition;
use App\SpecialCollection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class Helpers
{
    public static function titleExplode($title = '', $section = 1, $slice_num = 3)
    {
        $data = explode(' ', $title);
        $part = ceil(count($data) / $slice_num);
        if ($slice_num == 3) {
            $title_split[1] = implode(' ', array_slice($data, 0, $part));
            $title_split[2] = implode(' ', array_slice($data, $part, $part));
            $title_split[3] = implode(' ', array_slice($data, $part * 2));
        } elseif ($slice_num == 2) {
            $title_split[1] = implode(' ', array_slice($data, 0, ceil(count($data) / $slice_num)));
            $title_split[2] = implode(' ', array_slice($data, ceil(count($data) / $slice_num), ceil(count($data))));
        }

        return $title_split[$section];
    }

    public static function datetime($date, $format = 'F j, Y')
    {
        if(is_null($date)){
            return "";
        }
        else {
            return date($format, strtotime($date));
        }
    }

    public static function clean_empty_caracters($term = null)
    {
        $arr = explode(' ', $term);
        $clean_array = [];
        foreach ($arr as $text) {
            $text = preg_replace('/[^\P{C}\n]+/u', '', $text);
            $clean_array[] = $text;
        }

        return implode(' ', $clean_array);
    }

    public static function trim_w_ellipsis($in, $size)
    {
        return strlen($in) > $size ? substr($in, 0, $size).'...' : $in;
    }

    public static function clean_data_filter($term = null)
    {
        return strtolower(preg_replace('/[^0-9a-zA-Z-]/', '-', $term));
    }

    public static function printExhibitionVenues($exhibition = null, $onlyham = false)
    {
        $venues = '';
        $countHamVenues = 0;
        if (! empty($exhibition) && ! empty($exhibition->venues)) {
            foreach ($exhibition->venues as $key => $venue) {
                if ($onlyham && $venue->ishamvenue) {
                    $countHamVenues++;
                }
            }
            foreach ($exhibition->venues as $key => $venue) {
                if ($onlyham && $venue->ishamvenue) {
                    $venues .= $venue->name;
                    if ($key + 1 < $countHamVenues) {
                        $venues .= ', ';
                    }
                }
                if (! $onlyham) {
                    $venues .= $venue->name;
                    if ($key + 1 < count($exhibition->venues)) {
                        $venues .= ', ';
                    }
                }
            }

            foreach ($exhibition->venues as $key => $venue) {
                if (! empty($venue->galleries)) {
                    foreach ($venue->galleries as $key => $gallery) {
                        $venues = $gallery->name.', '.$venues;
                    }
                }
            }
        }

        return $venues;
    }

    public static function mergeMediumsTechniques($techniques, $mediums)
    {
        $mediumsTechniques = [];
        if (! empty($techniques)) {
            foreach ($techniques as $technique) {
                $technique->type = 'technique';
                $mediumsTechniques[strtolower($technique->name)] = $technique;
            }
        }
        if (! empty($mediums)) {
            foreach ($mediums as $medium) {
                $medium->type = 'medium';
                $mediumsTechniques[strtolower($medium->name)] = $medium;
            }
        }
        ksort($mediumsTechniques);

        return $mediumsTechniques;
    }

    public static function sortFiltersByLevel($resources = null)
    {
        $sorted_resources = [
        1 => [],
        2 => [],
        3 => [],
        ];
        if (! empty($resources)) {
            //Put each filters on different levels array
            foreach ($resources as $resource) {
                if (empty($resource->level) || $resource->level == 1) {
                    $sorted_resources[1][] = $resource;
                } else {
                    if (! empty($resource->parentmediumid)) {
                        $sorted_resources[$resource->level][$resource->parentmediumid][] = $resource;
                    } else {
                        $sorted_resources[$resource->level][$resource->parentplaceid][] = $resource;
                    }
                }
            }
        }

        return $sorted_resources;
    }

    public static function sortFiltersByChildren($resources = null){
        $sorted_resources = [];

        if (! empty($resources)) {
            foreach($resources as $resource){
                    $resource->children = self::findChildren($resources, $resource->id);
                    foreach($resource->children as $child1){
                        if(($child1->haschildren)){
                            $child1->children = self::findChildren($resources, $child1->id);
                            foreach($child1->children as $child2){
                                if($child2->haschildren){
                                    $child2->children = self::findChildren($resources, $child2->id);
                                    foreach($child2->children as $child3){
                                        if($child3->haschildren){
                                            $child3->children = self::findChildren($resources, $child3->id);
                                            foreach($child3->children as $child4){
                                                if($child4->haschildren){
                                                    $child4->children = self::findChildren($resources, $child4->id);
                                                }
                                            }
                                        }
                                    }
                                }
                                
                            }
                        }
                    }
                    if (empty($resource->level) || $resource->level == 1) {
                        array_push($sorted_resources, $resource);
                    }
                }
            return $sorted_resources;
        }
    }

    public static function findChildren(array $resources, $resource_id = null)
    {
        $children = array_filter(
            // the array you wanna search in
            $resources, 
            // callback function to search for key that has parent and value that matches id
            function ($element) use($resource_id){ 
                if(property_exists($element, 'parentplaceid')){
                    if($resource_id){
                        return $element->parentplaceid == $resource_id;
                    }
                }
                if(property_exists($element, 'parentmediumid')){
                    if($resource_id){
                        return $element->parentmediumid == $resource_id;
                    }
                }
            }, 
        );
        return $children;
    }
    public static function sortFiltersByFloor($resources = null)
    {
        $sorted_resources = [];
        if (! empty($resources)) {
            //Put each filters on different levels array
            foreach ($resources as $resource) {
                if (isset($resource->floor)) {
                    $sorted_resources[$resource->floor][] = $resource;
                }
            }
        }

        return $sorted_resources;
    }

    public static function get_available_position_on_homepage($position = null)
    {
        $initial_list_position = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $positions_taken = [];

        $featuredCustomArticles = FeaturedCustomArticle::select('position')->where('position', '!=', '0')->get();
        foreach ($featuredCustomArticles as $featuredCustomArticle) {
            $positions_taken[] = $featuredCustomArticle->position;
        }

        $featuredSpecialCollections = SpecialCollection::select('homepage_position')
        ->where('homepage_position', '!=', '0')->get();
        foreach ($featuredSpecialCollections as $featuredSpecialCollection) {
            $positions_taken[] = $featuredSpecialCollection->homepage_position;
        }

        $featuredEvents = Events::select('position')->where('position', '!=', '0')->get();
        foreach ($featuredEvents as $featuredEvent) {
            $positions_taken[] = $featuredEvent->position;
        }

        $featuredArticles = Article::select('position')->where('position', '!=', '0')->get();
        foreach ($featuredArticles as $featuredArticle) {
            $positions_taken[] = $featuredArticle->position;
        }

        $featuredExhibitions = FeaturedExhibition::select('position')->where('position', '!=', '0')->get();
        foreach ($featuredExhibitions as $featuredExhibition) {
            $positions_taken[] = $featuredExhibition->position;
        }

        $positions_differences = array_diff($initial_list_position, $positions_taken);
        $position_available = [''=>'Select Position'];
        foreach ($positions_differences as $diff) {
            $position_available[$diff] = $diff;
        }

        if ($position) {
            $position_available[$position] = $position;
        }

        ksort($position_available);

        return $position_available;
    }

    public static function setActive($section = 'Visit')
    {
        $routes = [
        'Visit' => [
        'visit.exhibitions.index',
        'visit.exhibitions.show',
        'visit.visit-plan',
        'events.index',
        'events.view',
        'interactivefloor',
        'interactivefloor.floor',
        'interactivefloor.room',
        'visit.rentals',
        'pages.root',
        ],
         'Browse' => [
         'collections',
         'collections.special-collections.index',
         'collections.special-collections.show',
         'collecting-policy',
         'api',
         ],
        'Tours' => [
        'tour.index',
        ],
        'Support' => [
        'support.members',
        'support.fellows',
        'support.give',
        ],
         'TeachingResearch' => [
         'teaching-and-research.index',
         'teaching-and-research.education-departments',
         'teaching-and-research.art-study-center.create',
         'teaching-and-research.curatorial-divisions',
         'teaching-and-research.research-centers',
         ],
        'About' => [
        'about.index',
        'about.mission',
        'about.directors-message',
        'about.renovation-history',
        'about.contact',
        'press.releases',
        'press.media_library',
        'about.rentals',
        ],
        'IndexMagazine' => [
        'index-magazine.index',
        'index-magazine.listing',
        'index-magazine.detail',
        'index-magazine.search',
        'index-magazine.tag',
        ],
        ];
        $currentRoute = Route::currentRouteName();
        if (array_key_exists($section, $routes)) {
            if (in_array($currentRoute, $routes[$section])) {
                return true;
            }
        }

        return false;
    }

    public static function setActiveNew($section = 'Visit')
    {
        $paths = [
        'Visit' => [
        'visit.exhibitions.index',
        'visit.exhibitions.show',
        'visit.visit-plan',
        'events.index',
        'events.view',
        'interactivefloor',
        'interactivefloor.floor',
        'interactivefloor.room',
        'visit.rentals',
        'pages.root',
        ],
         'Browse' => [
         'collections',
         'collections.special-collections.index',
         'collections.special-collections.show',
         'collecting-policy',
         'api',
         ],
        'Tours' => [
        'tour.index',
        ],
        'Support' => [
        'support.members',
        'support.fellows',
        'support.give',
        ],
         'TeachingResearch' => [
         'teaching-and-research.index',
         'teaching-and-research.education-departments',
         'teaching-and-research.art-study-center.create',
         'teaching-and-research.curatorial-divisions',
         'teaching-and-research.research-centers',
         ],
        'About' => [
        'about.index',
        'about.mission',
        'about.directors-message',
        'about.renovation-history',
        'about.contact',
        'press.releases',
        'press.media_library',
        'about.rentals',
        ],
        'IndexMagazine' => [
        'index-magazine.index',
        'index-magazine.listing',
        'index-magazine.detail',
        'index-magazine.search',
        'index-magazine.tag',
        ],
        ];
        $currentRoute = Route::currentRouteName();
        if (array_key_exists($section, $routes)) {
            if (in_array($currentRoute, $routes[$section])) {
                return true;
            }
        }

        return false;
    }

    public static function getVimeoThumb($vimeo_id)
    {
        if (@file_get_contents('http://vimeo.com/api/v2/video/'.$vimeo_id.'.php') != false) {
            $hash = unserialize(file_get_contents('http://vimeo.com/api/v2/video/'.$vimeo_id.'.php'));

            return $hash[0]['thumbnail_medium'];
        } else {
            return null;
        }
    }

    public static function nl2p($string)
    {
        return preg_replace('/[^\r\n]+/', '<p>$0</p>', $string);
    }

    public static function getTextilized($string)
    {
        $parser = new \Netcarver\Textile\Parser();

        return $parser->parse($string);
    }

    public static function cleanMiradorJSON($config)
    {
        $data = json_decode($config, true);
        if ($data != null) {
            $data['mainMenuSettings']['show'] = false;
            $data['buildPath'] = asset('mirador').'/';
            $data['windowSettings']['canvasControls']['annotations']['annotationLayer'] = false;
            $data = json_encode($data);

            return $data;
        } else {
            return $data;
        }
    }

    public static function autolink($str, $attributes = [])
    {
        $attrs = '';
        foreach ($attributes as $attribute => $value) {
            $attrs .= " {$attribute}=\"{$value}\"";
        }

        $str = ' '.$str;
        $str = preg_replace(
            '`([^"=\'>])((http|https|ftp)://[^\s<]+[^\s<\.)])`i',
            '$1<a href="$2"'.$attrs.'target="_blank">$2</a>',
            $str
        );
        $str = substr($str, 1);

        return $str;
    }

    public static function s3ify($string)
    {
        //$environment = config('app.env');
        //return preg_replace('/\/system\/App\//', "https://s3.amazonaws.com/media.harvardartmuseums.org/" . $environment . "/file_uploads/", $string, 1);
        return $string;
    }

    public static function entryAuthorsHelper($entry)
    {
        $entryArray = json_decode(json_encode($entry), true);
        $authors = array_filter($entryArray['publication']['people'], function ($var) {
            return $var['role'] == 'Author';
        });

        return $authors;
    }
}
