<?php
/**
 * Copyright (C) Loopeer, Inc - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential.
 *
 * User: DengYongBin
 * Date: 15/9/22
 * Time: 下午1:41
 */
namespace Loopeer\QuickCms\Http\Controllers;

use App\Http\Controllers\Controller;
use Loopeer\QuickCms\Models\Permission;
use Carbon\Carbon;
use Input;
use Illuminate\Pagination\Paginator;
use Cache;
use Auth;
use Loopeer\QuickCms\Models\System;
use Request;
use Route;
use Session;
use Response;
use Loopeer\QuickCms\Models\PermissionRole;

/**
 * 后台Controller基类
 * Class BaseController
 * @package App\Http\Controllers\Backend
 */
class BaseController extends Controller
{

    protected $systemConfig;
    public function __construct()
    {
        //$route_url = '/' . Route::getCurrentRoute()->getPath();
        /*if(!Session::has($route_url)){
            $permission = Permission::with('parent')->select('id','route', 'name','display_name','parent_id')
                ->where('route', $route_url)
                ->first();
            Session::push($route_url, $permission);
        }*/
        if(!Session::has('permissions')) {
            $roles = Auth::admin()->get()->roles()->first();
            $permission_ids = PermissionRole::where('role_id', $roles->pivot->role_id)->lists('permission_id');
            $permissions = Permission::where('type', 1)->whereIn('id', $permission_ids)->get();
            Session::put('permissions', $permissions);
        }
        $this->systemConfig = Cache::rememberForever('system_config', function() {
            return System::get();
        });
    }

    /**
     * 分页查询封装函数
     * @param array $show_column ['id','name','email']
     * @param object $model new Model
     * @param string $append_column
     * @return array
     */
    public function simplePage($show_column, $model, $append_column = NULL, $str_column = NULL) {
        $search = Input::get('search')['value'];
        $search = addslashes($search);
        $order = Input::get('order')['0'];
        $length = Input::get('length');
        self::setCurrentPage($length);
        $order_sql = $show_column[$order['column']] . ' ' . $order['dir'];
        if ($str_column == NULL) {
            $str_column = implode(',', $show_column);
        }

//        $query = $model->selectRaw($str_column);

//        $columns = Input::get('columns');
//        foreach ($columns as $column) {
//            $value = $column['search']['value'];
//            if ($value) {
//                $query->where($show_column[$column['data']], 'like', '%' . $value . '%');
//            }
//        }

//        $paginate = $query->whereRaw("concat_ws(" . $str_column . ") like '%" . $search . "%'");
//        $query->orderByRaw($order_sql)->paginate($length);

        $paginate = $model->selectRaw($str_column)
            ->whereRaw("concat_ws(" . $str_column . ") like '%" . $search . "%'")
            ->orderByRaw($order_sql)
            ->paginate($length);

        if(isset($append_column)) {
            $show_column[$append_column] = $append_column;
        }
        $ret = self::getPageDate($show_column, $paginate);
        return $ret;
    }

    /**
     * 自定义查询分页函数
     * @param array $show_column ['id','name','email']
     * @param array $paginate query sql
     * @return array
     */
    public function queryPage($show_column, $paginate) {
        $ret = self::getPageDate($show_column, $paginate);
        return $ret;
    }

    /**
     * 设置分页对象的当前页
     */
    public function setCurrentPage() {
        $start = Input::get('start');
        $length = Input::get('length');
        $page = ($start / $length) + 1;
        Paginator::currentPageResolver(function() use ($page) {
            return $page;
        });
    }

    /**
     * 获取分页对象数据
     * @param array $show_column ['id','name','email']
     * @param array $paginate query sql
     * @return array
     */
    private function getPageDate($show_column, $paginate) {
        $draw = Input::get('draw');
        $data = array();
        foreach($paginate->items() as $item) {
            $obj = array();
            foreach($show_column as $column) {
                if($item->$column instanceof Carbon) {
                    array_push($obj, $item->$column->format('Y-m-d H:i:s'));
                } else {
                    array_push($obj, $item->$column);
                }
            }
            array_push($data, $obj);
        }
        $ret = array(
            'draw' => $draw,
            'recordsTotal' => $paginate->total(),
            'recordsFiltered' => $paginate->total(),
            'data' => $data,
        );
        return $ret;
    }

    /**
     * 设置自定义sql语句中得表名前缀
     * @param $column
     * @param array $searches
     * @return mixed
     */
    public function setTablePrefix($column, $searches = array()) {
        $prefix = config('database.connections.mysql.prefix');
        foreach($searches as $search) {
            $column = str_replace($search, $prefix . $search, $column);
        }
        return $column;
    }

    /**
     * 获取多表查询数据
     * @param $obj
     * @param $select_column
     * @param $show_column
     * @param $tables
     * @return mixed
     */
    public function getMultiTableData($obj, $select_column, $show_column, $tables){
        $search = Input::get('search')['value'];
        $search = addslashes($search);
        $order = Input::get('order')['0'];
        $length = Input::get('length');
        $order_sql = $show_column[$order['column']] . ' ' . $order['dir'];
        $str_column = self::setTablePrefix(implode(',', $select_column), $tables);
        self::setCurrentPage();
        $obj = $obj->whereRaw("concat_ws(" . $str_column . ") like '%" . $search . "%'")
            ->orderByRaw($order_sql)
            ->paginate($length);
        $ret = self::queryPage($show_column, $obj);
        return Response::json($ret);
    }

    public function getSystemValue($key)
    {
        $collection = $this->systemConfig;
        $filtered = $collection->where('system_key', $key);
        return $filtered->first()['system_value'];
    }
}
