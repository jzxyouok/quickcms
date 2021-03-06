<?php
/**
 * Copyright (C) Loopeer, Inc - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential.
 *
 * User: DengYongBin
 * Date: 16/4/19
 * Time: 下午5:37
 */
namespace Loopeer\QuickCms\Services\Utils;

use Illuminate\Support\Facades\DB;
use Auth;
use Loopeer\QuickCms\Models\Permission;
use Route;
use App;

class GeneralUtil {

    public static function curdAction($curl_action = array()) {
        $default_action = array('create' => true, 'edit' => true, 'detail' => true, 'delete' => true);
        if(count($curl_action) > 0) {
            foreach($curl_action as $action_key => $action_val) {
                if(isset($action_val)) {
                    $default_action[$action_key] = $action_val;
                }
            }
        }
        return $default_action;
    }

    public static function queryComment($model) {
        try{
            $results = DB::connection('mysql_system')->select('select COLUMN_NAME,COLUMN_COMMENT FROM columns WHERE table_schema = ? AND table_name = ?', [env('DB_DATABASE'), with($model)->getTable()]);
        } catch(\Exception $ex) {
            return [];
        }
        $column_names = [];
        foreach($results as $result) {
            switch($result->COLUMN_NAME) {
                case 'created_at':
                    $column_names[$result->COLUMN_NAME] = '创建时间';
                    break;
                case 'updated_at':
                    $column_names[$result->COLUMN_NAME] = '修改时间';
                    break;
                case 'deleted_at':
                    $column_names[$result->COLUMN_NAME] = '删除时间';
                    break;
                default:
                    $column_names[$result->COLUMN_NAME] = $result->COLUMN_COMMENT;
                    break;
            }
        }
        return $column_names;
    }

    public static function filterOperationPermission($request, $permission, $route_name) {
        $method = $request->method();
        $path = Route::getCurrentRoute()->getPath();
        $path = str_replace("/", ".", $path);
        $route = Route::currentRouteName();
        $user = Auth::admin()->get();
        if(config('quickcms.permission_switch')) {
            if($route != 'admin.' . $route_name . '.search' && $route != 'admin.' . $route_name . '.store'  && !$user->can($route)) {
                App::abort('403');
            }
        } else {
            $permissions = Permission::where('name', $route)->first();
            if(isset($permissions) && $permissions->type == 0) {
                if(!$user->can($route)) {
                    App::abort('403');
                }
            }
        }
    }
}
