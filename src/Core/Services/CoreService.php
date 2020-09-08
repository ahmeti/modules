<?php

namespace Ahmeti\Modules\Core\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class CoreService {

    protected $statuses = [];
    protected $breadcrumbs = [];

    public function isDate($date, $format = 'Y-m-d')
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    public function date($date, $fromFormat, $format)
    {
        if($date instanceof Carbon){
            $date = $date->format($fromFormat);
        }

        if ( $this->isDate($date, $fromFormat ) ){
            if ( Str::is('*%*', $format) ){
                return Carbon::createFromFormat($fromFormat, $date)->formatLocalized($format);
            }
            return Carbon::createFromFormat($fromFormat, $date)->format($format);
        }
        return null;
    }

    public function toInteger($id)
    {
        return (int)preg_replace('@[^0-9]@', '', $id);
    }

    public function toDecimal($decimal)
    {
        if ( is_float($decimal) ){
            return $decimal;
        }
        return (float)str_replace(',', '.', str_replace('.', '', $decimal));
    }

    public function pagination($model, $request)
    {
        if( $this->isMobile() ){
            return $model->appends($request->except(['_']))->render("pagination::simple-default");
        }else{
            return $model->appends($request->except(['_']))->render("pagination::default");
        }
    }

    public function isMobile($check = null)
    {
        $result = preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", @$_SERVER["HTTP_USER_AGENT"]);
        if( is_null($check) ){
            return $result;
        }
        return $check;
    }

    protected function enums($key)
    {
        if( empty($this->statuses) ){

            $statuses = DB::table('statuses')
                ->whereNull('deleted_at')
                ->select(['name', 'key', 'value', 'icon', 'color'])
                ->get()
                ->toArray();

            foreach ($statuses as &$status){
                $status = (array)$status;
            }

            $this->statuses = collect($statuses)->groupBy('name')->toArray();

            foreach ($this->statuses as &$status){
                foreach ($status as &$item){
                    unset($item['name']);
                }
            }
        }

        if( array_key_exists($key, $this->statuses) ){
            return $this->statuses[$key];
        }

        return [];
    }

    public function enumsValue($key)
    {
        $enums = $this->enums($key);

        $newEnums = [];
        foreach ((array)$enums as $v) {
            $newEnums[$v['key']] = $v['value'];
        }
        return $newEnums;
    }

    public function enumsSelect($key)
    {
        $enums = $this->enums($key);

        $newEnums = [];
        foreach ((array)$enums as $v) {
            $newEnums[] = ['id' => $v['key'], 'value' => $v['value'], 'icon' => $v['icon'], 'icon-color' => $v['color']];
        }
        return $newEnums;
    }

    public function enumsHtml($key)
    {
        $enums = $this->enums($key);

        $newEnums = [];
        foreach ((array)$enums as $v) {
            if( ! empty($v['html']) ){
                $newEnums[$v['key']] = $v['html'];
            }else{
                $newEnums[$v['key']] = new HtmlString('<i '.(empty($v['color']) ? '' : 'style="color:'.$v['color'].';" ').'class="'.$v['icon'].'"></i> '.$v['value']);
            }
        }
        return $newEnums;
    }

    public function companyId()
    {
        return (int)session('company_id');
    }

    public function userId()
    {
        return (int)session('user_id');
    }

    public function userName()
    {
        if( auth()->user() ){
            return auth()->user()->name;
        }
        return '';
    }

    public function isSuperAdmin()
    {
        if ( auth()->user() && auth()->user()->authority === 'superadmin' ){
            return true;
        }
        return false;
    }

    public function isAdmin()
    {
        if ( auth()->user() && in_array(auth()->user()->authority, ['superadmin', 'admin']) ){
            return true;
        }
        return false;
    }

    public function isRep()
    {
        if ( auth()->user() && auth()->user()->authority === 'representative' ){
            return true;
        }
        return false;
    }

    public function company()
    {
        if( empty($this->companyData) ){
            $this->companyData = DB::table('companies')->where('id', $this->companyId())->first();
        }
        return $this->companyData;
    }


    public function checkPermission($pageId)
    {
        if ( $this->isAdmin() ){
            return true;
        }

        if ( empty($pageId) ){
            abort(403, 'Permission-Error.');
        }

        $permission = DB::table('permissions')
            ->where('company_id', $this->companyId())
            ->where('user_id', $this->userId())
            ->where('page_id', $pageId)
            ->first();

        if ( empty($permission) ){
            abort(403, 'Permission-Error.');
        }

        return true;
    }

    public function addBreadcrumb($title, $url=null, $icon=null, $class='ajaxPage')
    {
        if ( is_null($url) ){
            $url = 'javascript:void(0)';
            $class = '';
        }
        $this->breadcrumbs[] = '<li><i class="fa '.$icon.' fa-fw"></i> <a class="'.$class.'" href="'.$url.'">'.$title.'</a></li>';
    }

    public function getBreadcrumbs()
    {
        if ( $this->isMobile() ){
            return '<div class="row" style="height:15px"></div>';
        }

        $out='<!-- Page Heading -->';
        $out.='<div class="row"><div class="col-sm-12"><ol class="breadcrumb" style="color:#337ab7">
        <li><i class="fa fa-home fa-fw"></i> <a class="ajaxPage" href="'.route('home').'">'.__('Homepage').'</a></li>';
        foreach ($this->breadcrumbs as $b) {
            $out .= $b;
        }
        $out.='</ol></div></div><!-- row -->';
        return $out;
    }

    public function getUserMenu()
    {
        $userId = $this->userId();
        $companyID = $this->companyId();
        $permissionTable = 'permissions';
        $pageTable = 'pages';
        $items = DB::table('pages')
            ->leftJoin($permissionTable.' AS p', function ($join) use ($companyID, $pageTable, $permissionTable){
                $join->on('p.page_id', '=', $pageTable.'.id');
            })->whereRaw("( p.company_id={$companyID} AND p.user_id={$userId} AND {$pageTable}.show=1 ) OR ( parent_id=0 AND {$pageTable}.show=1 AND {$pageTable}.url IS NULL ) OR {$pageTable}.id=1")
            ->groupBy($pageTable.'.id')
            ->orderBy($pageTable.'.priority')
            ->selectRaw(implode(',', [
                'DISTINCT('.$pageTable.'.id)',
                $pageTable.'.parent_id',
                $pageTable.'.name',
                $pageTable.'.url',
                $pageTable.'.class',
                $pageTable.'.icon',

            ]))
            ->get()
            ->toArray();

        $items = array_map(function ($item){ return (array)$item; }, $items);

        $menu = [];
        foreach ($items as $r) {

            $r['name'] = __($r['name']);

            if ( $r['parent_id'] == 0){
                $menu[$r['id']] = $r;
            }else{
                $menu[$r['parent_id']]['sub'][]=$r;
            }
        }
        return $menu;
    }

    public function alert($status=false, $desc='', $mb=10)
    {
        return new HtmlString('<div style="margin-bottom:'.$mb.'px;padding:8px;border-width:3px" class="alert '.
            ($status ? 'alert-success' : 'alert-danger').'" role="alert">'.$desc.'</div>');
    }

    public function openPanel($title, $data = [])
    {
        // $data['default'] => in | empty
        // $data['links'] => []
        // $data['badge'] => null || 1
        // $data['class'] => null
        // $data['bodyClass'] => null
        $iid = strtoupper(uniqid());

        //$data['icon'] = empty($data['icon']) ? 'fa-bars' : $data['icon'];

        $html = '
        <div class="panel panel-app'.(empty($data['class']) ? '' : ' '.$data['class'] ).'">
            <div class="panel-heading noselect clearfix">
                '.(isset($data['links']) ? '
                <div class="btn-group pull-left app-panel-btn-group" style="margin-right:6px">
                    <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'
                .(isset($data['icon']) ? '<i class="fa '.$data['icon'].'" aria-hidden="true"></i> ': '<i class="fa fa-bars" aria-hidden="true"></i>').'
                    </button>
                    <ul class="dropdown-menu app-dropdown-menu">'.implode('',$data['links']).'</ul>
                </div>' : '').
            ( ! isset($data['links']) && isset($data['icon']) ? '<i class="pull-left fa '.$data['icon'].'" aria-hidden="true" '.
                'style="width:30px;text-align:center;margin-right: 6px;padding: 4px 6px;'.
                'border: 1px solid #ccc;background-color: #f2f2f2;border-radius: 3px;"></i>' : '').
            '<strong class="app-panel-title-collapse pull-left" data-toggle="collapse" data-target="#'.$iid.'">'.$title. '</strong>'.
            (isset($data['badge']) ? '<span aria-hidden="true" class="pull-left badge" style="background-color:darkorange;color:white;display:inline-block;margin-left:6px;padding:5px 10px;font-size:14px;border-radius:12px">'.$data['badge'].'</span>' : '')
            .'<span data-toggle="collapse" data-target="#'.$iid.'" class="app-panel-collapse pull-right fa fa-chevron-right'.
            (isset($data['default']) && $data['default'] != 'in' ? ' collapsed' : '').'"></span>
            </div>
            <div id="'.$iid.'" class="collapse '.(isset($data['default']) ? $data['default'] : 'in').'">
                <div class="panel-body'.(isset($data['bodyClass']) ? ' '.$data['bodyClass'] : '').'" style="'.$this->isMobile('position:relative !important;overflow-x:hidden !important;background-color:#F8F9F9').'">';

        return new HtmlString($html);
    }

    public function closePanel()
    {
        return new HtmlString('</div></div></div>');
    }

    public function strLimit($value, $limit = 100, $end = '...')
    {
        return Str::limit($value, $limit, $end);
    }
}
