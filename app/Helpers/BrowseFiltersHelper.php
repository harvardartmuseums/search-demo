<?php

namespace App\Helpers;

class BrowseFiltersHelper
{
    public $_limit_filters = 5000;

    public $classifications;
    public $places;
    public $sorted_places;
    public $galleries;
    public $sorted_galleries;
    public $periods;
    public $centuries;
    public $cultures;
    public $worktypes;
    public $colors;
    public $techniques;
    public $mediums;
    public $mediumstechniques;
    public $sorted_mediumstechniques;

    public $custom_filters;

    public function load($group = '')
    {
        //TODO: All repeated scopes MUST to be inserted into the classes
        $this->classifications = \HamClassification::sort('name')
        ->sortorder('asc')->usedby($group)->limit($this->_limit_filters)->find();
        $this->places = \HamPlace::sort('name')
        ->sortorder('asc')->usedby($group)->limit($this->_limit_filters)->find();
        $this->sorted_places = \App\Helpers\Helpers::sortFiltersByLevel($this->places);
        $this->galleries = \HamGallery::sort('name')
        ->sortorder('asc')->usedby($group)->limit($this->_limit_filters)->find();
        $this->sorted_galleries = \App\Helpers\Helpers::sortFiltersByFloor($this->galleries);
        $this->periods = \HamPeriod::sort('name')
        ->sortorder('asc')->usedby($group)->limit($this->_limit_filters)->find();
        $this->centuries = \HamCentury::sort('temporalorder')
        ->sortorder('asc')->usedby($group)->limit($this->_limit_filters)->find();
        $this->cultures = \HamCulture::sort('name')
        ->sortorder('asc')->usedby($group)->limit($this->_limit_filters)->find();
        $this->worktypes = \HamWorktype::sort('name')
        ->sortorder('asc')->usedby($group)->limit($this->_limit_filters)->find();
        $this->colors = \HamColor::sort('name')
        ->sortorder('asc')->usedby($group)->limit($this->_limit_filters)->find();

        $this->techniques = \HamTechnique::sort('name')
        ->sortorder('asc')->usedby($group)->limit($this->_limit_filters)->find();
        $this->mediums = \HamMedium::sort('name')
        ->sortorder('asc')->usedby($group)->limit($this->_limit_filters)->find();
        $this->mediumstechniques =
        \App\Helpers\Helpers::mergeMediumsTechniques($this->techniques, $this->mediums);
        $this->sorted_mediumstechniques = \App\Helpers\Helpers::sortFiltersByLevel($this->mediumstechniques);
    }

    public function loadChildren($group = '')
    {
        //TODO: All repeated scopes MUST to be inserted into the classes
        $this->classifications = \HamClassification::sort('name')
        ->sortorder('asc')->usedby($group)->limit($this->_limit_filters)->find();
        $this->places = \HamPlace::sort('name')
        ->sortorder('asc')->usedby($group)->limit($this->_limit_filters)->find();
        $this->sorted_places = \App\Helpers\Helpers::sortFiltersByChildren($this->places);
        $this->galleries = \HamGallery::sort('name')
        ->sortorder('asc')->usedby($group)->limit($this->_limit_filters)->find();
        $this->sorted_galleries = \App\Helpers\Helpers::sortFiltersByFloor($this->galleries);
        $this->periods = \HamPeriod::sort('name')
        ->sortorder('asc')->usedby($group)->limit($this->_limit_filters)->find();
        $this->centuries = \HamCentury::sort('temporalorder')
        ->sortorder('asc')->usedby($group)->limit($this->_limit_filters)->find();
        $this->cultures = \HamCulture::sort('name')
        ->sortorder('asc')->usedby($group)->limit($this->_limit_filters)->find();
        $this->worktypes = \HamWorktype::sort('name')
        ->sortorder('asc')->usedby($group)->limit($this->_limit_filters)->find();
        $this->colors = \HamColor::sort('name')
        ->sortorder('asc')->usedby($group)->limit($this->_limit_filters)->find();

        $this->techniques = \HamTechnique::sort('name')
        ->sortorder('asc')->usedby($group)->limit($this->_limit_filters)->find();
        $this->mediums = \HamMedium::sort('name')
        ->sortorder('asc')->usedby($group)->limit($this->_limit_filters)->find();
        $this->mediumstechniques =
        \App\Helpers\Helpers::mergeMediumsTechniques($this->techniques, $this->mediums);
        $this->sorted_mediumstechniques = \App\Helpers\Helpers::sortFiltersByChildren($this->mediumstechniques);
    }

    public function loadCustomFilters($id)
    {
        $this->custom = \HamCustomCollection::forId($id)->find();
        $this->sorted_custom = \HamCustomCollection::forId($id)->sorted(true)->find();

        return $this;
    }

    public function selectable_options()
    {
        $this->load();

        $filters = [];
        $filters['classifications'] = $this->extract_options($this->classifications);
        $filters['galleries'] = $this->extract_options($this->galleries);
        $filters['periods'] = $this->extract_options($this->periods);
        $filters['centuries'] = $this->extract_options($this->centuries);
        $filters['worktypes'] = $this->extract_options($this->worktypes);
        $filters['colors'] = $this->extract_options($this->colors);
        $filters['techniques'] = $this->extract_options($this->techniques);
        $filters['mediums'] = $this->extract_options($this->mediums);
        $filters['places'] = $this->extract_options($this->places);

        return $filters;
    }

    protected function extract_options($source, $field = 'name')
    {
        $options = [];
        if (! empty($source)) {
            foreach ($source as $element) {
                $options[$element->name] = $element->name;
            }
        }

        return $options;
    }
}
