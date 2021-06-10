<?php

namespace App\Http\Controllers;

use App\Helpers\BrowseFiltersHelper;
use App\Services\BrowseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BrowseController extends Controller
{
    protected $_limit_objects = 12;

    public function index(Request $request)
    {
        $result = $this->results($request);

        header('Access-Control-Allow-Origin: *');
        return Response::json($result);
    }

    public function filters(Request $request)
    {
        $group = $request->query('filter_group', '');
        $filters = new BrowseFiltersHelper($group);
        if($group != ''){
            $filters->loadCustomFilters($group);
        }
        else {
            $filters->loadChildren();
        }
        header('Access-Control-Allow-Origin: *');
        return Response::json($filters);
    }
    
    public function filterLookup(Request $request)
    {
        $group = $request->query('filter_group', '');
        $data = $request->all();
        $names = [];
        $filters = new BrowseFiltersHelper('');
        if($group != ''){
            $filters->loadCustomFilters($group);
        }
        else {
            $filters->load();
        }
        foreach($data as $key => $value) {
                Log::info($key);
                if($key == 'custom'){
                    foreach($value as $filter_key => $filter_value){
                        $values = explode("|", $filter_value[0]);
                        foreach($values as $filter_id){
                            $i = array_search($filter_key, array_column($filters->custom, 'searchparameter'));
                            $category = ($i !== false ? $filters->custom[$i] : null);
                            if(property_exists($category, 'values')){
                                $i = array_search($filter_id, array_column($category->values, 'id'));
                                $filter = ($i !== false ? $category->values[$i] : null);
                                array_push($names, array("filter_name"=>$filter->name, "filter_parameter"=>$filter_key, "filter_id"=>$filter_id));
                            }
                            else{
                                $fullFilters = new BrowseFiltersHelper('');
                                $fullFilters->load();
                                $plural = Str::plural($filter_key);
                                $i = array_search($filter_id, array_column($fullFilters->{$plural}, 'id'));
                                $filter = ($i !== false ? $filters->{$plural}[$i] : null);
                                array_push($names, array("filter_name"=>$filter->name, "filter_parameter"=>$key, "filter_id"=>$filter_id));
                            }
                        }  
                    }
                }
                else {
                    if(!empty($value)){
                        $values = explode("|", $value[0]);
                        $plural = Str::plural($key);
                        foreach($values as $filter_id){
                            if(property_exists($filters, $plural)){
                                $i = array_search($filter_id, array_column($filters->{$plural}, 'id'));
                                $filter = ($i !== false ? $filters->{$plural}[$i] : null);
                                array_push($names, array("filter_name"=>$filter->name, "filter_parameter"=>$key, "filter_id"=>$filter_id));
                            }
                        }
                    }
                }
            }
        header('Access-Control-Allow-Origin: *');
        return Response::json($names);
    }

    public function filterCategory($filterCategory)
    {
        $filters = Cache::rememberForever($filterCategory, function () use ($filterCategory){
                $filters = new BrowseFiltersHelper('');
                $filters->loadChildren();
                if(property_exists($filters, $filterCategory)){
                    return $filters->$filterCategory;
                }
                else {
                    return null;
                }
        });  
        header('Access-Control-Allow-Origin: *');
        if($filters == null){
            abort(404);
        }
        header('Access-Control-Allow-Origin: *');
        return Response::json($filters);   
    }

    public function index_tour(Request $request)
    {
        $result = $this->results($request);

        $filters = new BrowseFiltersHelper();
        $filters->load();

        $view_filters = view('site/collection/tour_index', compact('filters'))->render();
        $result->filters_html = $view_filters;

        return Response::json($result);
    }

    public function results($request)
    {
        $input_default = [
        'offset' => 0,
        'group' => '',
        'classification' => [],
        'technique' => [],
        'medium' => [],
        'place' => [],
        'gallery' => [],
        'person' => [],
        'worktype' => [],
        'color' => [],
        'culture' => [],
        'century' => [],
        'period' => [],
        'onview' => [],
        'q' => '',

        'custom' => [],
        'sort' => 'rank',
        ];

        $input = array_merge($input_default, $request->all());

        if (array_key_exists('load_amount', $input)) {
            $this->_limit_objects = $input['load_amount'];
        }

        Session::put('input', $input);

        $service = new BrowseService;
        $result = $service->search($input, $input['offset'], $this->_limit_objects, $input['sort']);

        return $result;
    }
}
