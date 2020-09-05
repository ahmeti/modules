<?php

namespace Ahmeti\Modules\Core\Services;

use App\Core;

class FormService {

    protected $_type = 'normal'; # normal | filter | settings
    protected $_inside = false; # Sadece form içeriğini döndürür. form tagını döndürmez.
    protected $_param = true; # Default parametreleri forma dahil eder.
    protected $_tag = true; # Sadece form başlangıç tagını döndürür.
    protected $_ajaxform = true;
    protected $_id = null;
    protected $_beforeSerialize = '';
    protected $_beforeSubmit = '';
    protected $_action = '';
    protected $_method = 'post';
    protected $_enctype = '';
    protected $_class = 'form-horizontal';
    protected $_addclass = ''; // Sadece Form Tagına ilave class ekler

    protected $_only = false; # Sadece elementleri döndürür. Divleri döndürmez.

    protected $_left = '<div class="row">';
    protected $_right = '</div>';
    protected $_return = false; # true | false

    protected $_html = ''; # Form datasını tutuyor

    protected $_request = [];

    public function reset()
    {
        $this->_type = 'normal';
        $this->_inside = false;
        $this->_param = true;
        $this->_tag = false;
        $this->_ajaxform = true;
        $this->_id = strtoupper(uniqid());
        $this->_beforeSerialize = '';
        $this->_beforeSubmit = '';
        $this->_action = '';
        $this->_method = 'post';
        $this->_enctype = '';
        $this->_class = 'form-horizontal';
        $this->_addclass = '';

        $this->_left = '<div class="row">';
        $this->_right = '</div>';
        $this->_return = false;

        $this->_html = '';

        $this->_request = [];
    }

    public function id()
    {
        return $this->_id;
    }

    public function open(array $data = [])
    {
        $this->reset();

        foreach($data as $key=> $value){
            $makeKey = '_'.$key;
            if( property_exists($this, $makeKey) ){
                $this->$makeKey = $value;
            }
        }

        # FROM OPEN TAG
        $p = [];
        $p[] = '<!-- Start Form -->'.$this->_left.'<form data-before-serialize="'.$this->_beforeSerialize.'" data-before-submit="'.$this->_beforeSubmit.'" role="form" action="'.$this->_action.'" method="'.$this->_method.'"';

        if ( $this->_ajaxform === true && $this->_type !== 'filter' ){ $p[] = 'set_ajaxform="1"'; }
        if ($this->_type==='filter'){ $p[] = 'set_filterform="1"'; }
        if ( !empty($this->_id) ){ $p[] = 'id="'.$this->_id.'"'; }
        if ( !empty($this->_enctype) ){ $p[] = 'enctype="'.$this->_enctype.'"'; }

        if ( !empty($this->_class) ){
            $p[] = 'class="'.$this->_class.rtrim(' '.$this->_addclass).'"';
        }else{
            $p[] = 'class="form-horizontal'.rtrim(' '.$this->_addclass).'"';
        }

        $p[] = '>';

        if ( $this->_param === true ){
            // Varsayılan REQUEST ile alınan değerleri forma ekler....
            $p[] = $this->hidden('baseid', empty(request('baseid')) ? 'ajaxPageContainer' : request('baseid') );
            $p[] = $this->hidden('output', request('output') == 'modal' ? 'modal' : 'normal' );
            $p[] = $this->hidden('targetid', empty(request('targetid')) ? 'ajaxPageContainer' : request('targetid') );
            $p[] = $this->hidden('hidemodal', request('hidemodal') );
            # $p[] = $this->hidden('set_form_id', request('set_form_id') );
            # $p[] = $this->hidden('element_name', request('element_name') );
        }

        if ( $this->_type === 'filter' && ! empty($this->_request) ){
            $p[] = $this->hidden('order_column', $this->_request->input('order_column'));
            $p[] = $this->hidden('order_type', $this->_request->input('order_type'));
        }

        if ( $this->_type === 'normal' && ! empty($this->_request) ){
            $p[] = $this->hidden('gourl', $this->_request->input('gourl'));
            # $p[] = $this->hidden('set_element_name', $this->_request->input('set_element_name'));
        }

        if ( $this->_method === 'put' ){
            $p[] = $this->hidden('_method', 'PUT');
        }

        return implode(' ', $p);
    }

    public function close()
    {
        return '</form>'.$this->_right.'<!-- End Form -->';
    }

    protected function template($label, $tag, $label_title='', $desc='', $class='', $style='', $extraLine='')
    {
        if ( empty($label_title) ){ $label_title = $label; }
        if ( empty($style) ){ $s = ''; }else{ $s = ' style="'.$style.'"'; }
        if ( !empty($class) ){ $class = ' '.$class; }
        return
            '<div class="form-group'.$class.'"'.$s.'>'.
            '<label class="col-sm-3 control-label">'.
            '<span class="ellipsis">'.
            '<span data-toggle="tooltip" title="'.trim(str_replace('*', '', $label_title)).'">'.$label.'</span>'.
            '</span>'.
            '</label>'.
                '<div class="col-sm-9 errorMessage">'.
                    implode(' ', $tag).( empty($desc) ? '' : '<p class="help-block">'.$desc.'</p>').
                    ( empty($extraLine) ? '' : $extraLine).
            '</div>'.
            '</div>';
    }

    protected function templateInputGroup($type, $label, $tag, $label_title = '', $desc = '', $btn = [], $extraLine = null)
    {
        if ( empty($label_title) ){ $label_title = $label; }

        $inputGroup = '';

        if ($type=='btn'){
            foreach ((array)$btn as $b) {
                $inputGroup .= $b;
            }
            $inputGroup = '<span class="input-group-btn">'.$inputGroup.'</span>';
        }else{
            foreach ((array)$btn as $b) {
                $inputGroup .= $b;
            }
        }

        return
            '<div class="form-group">'.
            '<label class="col-sm-3 control-label">'.
            '<span class="ellipsis">'.
            '<span data-toggle="tooltip" title="'.trim(str_replace('*', '', $label_title)).'">'.$label.'</span>'.
            '</span>'.
            '</label>'.
            '<div class="col-sm-9 errorMessage">'.
            '<div class="input-group">'.implode(' ', $tag).''.$inputGroup.'</div>'.
            ( empty($desc) ? '' : '<p class="help-block">'.$desc.'</p>').
            ( empty($extraLine) ? '' : $extraLine).
            '</div>'.
            '</div>';
    }

    /**
     *  Form'a hidden ekler...
     *
     *  @param string $name Name
     *  @param string $value Varsayılan boştur. Value set edilir.
     *  @param integer $id Varsayılan boştur. ID set edilir.
     *  @return mixed Değer döndürmez.
     */
    public function hidden($name, $value='', $id='', $data = [])
    {
        $p = [];
        $p[]='<input type="hidden"';
        if ( !empty($id) ){ $p[] = 'id="'.$id.'"'; }
        if ( !empty($name) ){ $p[] = 'name="'.$name.'"'; }
        if ( !empty($value) ){ $p[] = 'value="'.$value.'"'; }
        if ( !empty($data['disabled']) ){ $p[]='disabled'; }
        $p[]='/>';

        if ($this->_only){ return implode(' ', $p); }
        return implode(' ', $p);
    }

    /**
     *  Form'a Text Input ekler...
     *
     *  @param string $name Name
     *  @param string $label Label
     *  @param string $value Varsayılan boştur. Value set edilir.
     *  @param string $maxlength Varsayılan boştur. Maxlength set edilir.
     *  @param array $data Varsayılan boştur. Keyler: <br>id<br>class<br>disabled<br>readonly<br>labeltitle<br>desc<br>ph<br>min-width=40<br>groupclass<br>dstyle<br>igroup
     *  @return mixed Değer döndürmez.
     */
    public function text($name, $label, $value = '', $maxlength = '', $data = [])
    {
        $p = [];
        $p[]='<input type="text"';
        if ( !empty($data['id']) ){ $p[]='id="'.$data['id'].'"'; }
        if ( !empty($name) ){ $p[]='name="'.$name.'"'; }
        if ( !empty($value) ){ $p[]='value="'.str_replace('"', '”', $value).'"'; }
        if ( !empty($data['class']) ){ $p[]='class="form-control input-sm '.$data['class'].'"'; }else{ $p[]='class="form-control input-sm"'; }
        if ( !empty($data['disabled']) ){ $p[]='disabled'; }
        if ( !empty($data['readonly']) ){ $p[]='readonly="readonly"'; }
        if ( !empty($data['ph']) ){ $p[]='placeholder="'.$data['ph'].'"'; }
        if ( !empty($maxlength) ){ $p[]='maxlength="'.$maxlength.'"'; }
        if ( !empty($data['min-width']) ){ $p[]='style="min-width:'.$data['min-width'].'px !important"'; }
        $p[]='autocomplete="off"';
        $p[]='/>';

        if ($this->_only || !empty($data['only'])){ return implode(' ', $p); }
        if ( empty($data['labeltitle']) && isset($data['ph']) ){ $data['labeltitle'] = $data['ph']; }

        $labeltitle = isset( $data['labeltitle'] ) ? $data['labeltitle'] : '';
        $desc = isset( $data['desc'] ) ? $data['desc'] : '';
        $groupclass = isset( $data['groupclass'] ) ? $data['groupclass'] : '';
        $dstyle = isset( $data['dstyle'] ) ? $data['dstyle'] : '';
        $btn = isset( $data['btn'] ) ? $data['btn'] : '';
        $igroup = isset( $data['igroup'] ) ? $data['igroup'] : '';

        if ( ! empty($btn) ){
            return self::templateInputGroup('btn', $label, $p, $labeltitle, $desc, $btn);
        }elseif( ! empty($igroup) ){
            return self::templateInputGroup('group', $label, $p, $labeltitle, $desc, $igroup);
        }
        return self::template($label, $p, $labeltitle, $desc, $groupclass, $dstyle);
    }

    /**
     *  Form'a Number Input ekler...
     *
     *  @param string $name Name
     *  @param string $label Label
     *  @param string $value Varsayılan boştur. Value set edilir.
     *  @param integer $min Varsayılan 0'dır. Minimum değer set edilir.
     *  @param integer $max Varsayılan 9999'dır. Maximum değer set edilir.
     *  @param string $sep Binlik ayırıcı set edilir. Varsayılan (.) noktadır. Boş bırakmak için 'null' set edilir.
     *  @param array $data Varsayılan boştur. Keyler: <br>id<br>class<br>disabled<br>readonly<br>labeltitle<br>desc<br>ph<br>min-width=40<br>groupclass
     *  @return mixed Değer döndürmez.
     */
    public function number($name, $label, $value = '', $min = 0, $max = 9999, $sep = '.', $data = [])
    {
        $p=array();
        $p[]='<input type="text"';
        $p[]='set_autonumeric="1"';
        if ( !empty($data['id']) ){ $p[] = 'id="'.$data['id'].'"'; }
        if ( !empty($name) ){ $p[] = 'name="'.$name.'"'; }
        if ( !empty($value) ){ $p[] = 'value="'.$value.'"'; }
        $p[] = 'data-v-min="'.$min.'"';
        $p[] = 'data-v-max="'.$max.'"';
        $p[] = 'data-a-sep="'.$sep.'"';
        $p[] = 'data-a-dec="null"';
        if ( !empty($data['ph']) ){ $p[] = 'placeholder="'.$data['ph'].'"'; }
        if ( !empty($data['class']) ){ $p[] = 'class="form-control input-sm '.$data['class'].'"'; }else{ $p[] = 'class="form-control input-sm"'; }
        if ( !empty($data['disabled']) ){ $p[] = 'disabled="disabled"'; }
        if ( !empty($data['readonly']) ){ $p[] = 'readonly="readonly"'; }
        if ( !empty($data['min-width']) ){ $p[] = 'style="min-width:'.$data['min-width'].'px !important"'; }
        $p[]='autocomplete="off"';
        $p[]='/>';

        if ($this->_only || !empty($data['only'])){ return implode(' ', $p); }

        $labeltitle = isset( $data['labeltitle'] ) ? $data['labeltitle'] : '';
        $desc = isset( $data['desc'] ) ? $data['desc'] : '';
        $groupclass = isset( $data['groupclass'] ) ? $data['groupclass'] : '';

        $element = self::template($label, $p, $labeltitle, $desc, $groupclass);

        $this->_html .= $element;
        return $element;
    }

    /**
     *  Form'a Double Input ekler...
     *
     *  @param string $name Name
     *  @param string $label Label
     *  @param string $value Varsayılan boştur. Value set edilir.
     *  @param integer $min Varsayılan 0,00'dır. Minimum değer set edilir.
     *  @param integer $max Varsayılan 9999,99'dır. Maximum değer set edilir.
     *  @param string $sep Binlik ayırıcı set edilir. Varsayılan (.) noktadır. Boş bırakmak için 'null' set edilir.
     *  @param integer $dec Ondalık ayırıcı set edilir. Varsayılan (,) virgüldür.
     *  @param array $data Varsayılan boştur. Keyler: <br>id<br>class<br>disabled<br>readonly<br>labeltitle<br>desc<br>ph<br>min-width=40<br>groupclass
     *  @return none Değer döndürmez.
     */
    public function decimal($name, $label, $value = '', $min = '0.00', $max = '99999999.99', $sep='.', $dec = ',', $data = [])
    {
        $p=[];
        $p[] = '<input type="text"';
        $p[] = 'set_autonumeric="1"';
        if ( !empty($data['id']) ){ $p[] = 'id="'.$data['id'].'"'; }
        if ( !empty($name) ){ $p[] = 'name="'.$name.'"'; }
        if ( !empty($value) ){ $p[] = 'value="'.$value.'"'; }
        $p[] = 'data-v-min="'.$min.'"';
        $p[] = 'data-v-max="'.$max.'"';
        $p[] = 'data-a-sep="'.$sep.'"';
        $p[] = 'data-a-dec="'.$dec.'"';
        $p[] = 'data-a-pad="false"';
        if ( !empty($data['ph']) ){ $p[] = 'placeholder="'.$data['ph'].'"'; }
        if ( !empty($data['class']) ){ $p[] = 'class="form-control input-sm '.$data['class'].'"'; }else{ $p[] = 'class="form-control input-sm"'; }
        if ( !empty($data['disabled']) ){ $p[] = 'disabled="disabled"'; }
        if ( !empty($data['readonly']) ){ $p[] = 'readonly="readonly"'; }
        if ( !empty($data['min-width']) ){ $p[] = 'style="min-width:'.$data['min-width'].'px !important"'; }
        $p[] = 'autocomplete="off"';
        $p[] = '/>';

        if ( $this->_only  || !empty($data['only'])){ return implode(' ', $p); }

        $labeltitle = isset( $data['labeltitle'] ) ? $data['labeltitle'] : '';
        $desc = isset( $data['desc'] ) ? $data['desc'] : '';
        $groupclass = isset( $data['groupclass'] ) ? $data['groupclass'] : '';

        return self::template($label, $p, $labeltitle, $desc, $groupclass);
    }

    public function color($name, $label, $value = '', $maxlength = '', $data = [])
    {
        $p = [];
        $p[]='<input type="text"';
        $p[] = 'data-app-colorpicker="1"';
        if ( !empty($data['id']) ){ $p[]='id="'.$data['id'].'"'; }
        if ( !empty($name) ){ $p[]='name="'.$name.'"'; }
        if ( !empty($value) ){ $p[]='value="'.str_replace('"', '”', $value).'"'; }
        if ( !empty($data['class']) ){ $p[]='class="form-control input-sm '.$data['class'].'"'; }else{ $p[]='class="form-control input-sm"'; }
        if ( !empty($data['disabled']) ){ $p[]='disabled'; }
        if ( !empty($data['readonly']) ){ $p[]='readonly="readonly"'; }
        if ( !empty($data['ph']) ){ $p[]='placeholder="'.$data['ph'].'"'; }
        if ( !empty($maxlength) ){ $p[]='maxlength="'.$maxlength.'"'; }
        if ( !empty($data['min-width']) ){ $p[]='style="min-width:'.$data['min-width'].'px !important"'; }
        $p[]='autocomplete="off"';
        $p[]='/>';

        if ($this->_only || !empty($data['only'])){ return implode(' ', $p); }
        if ( empty($data['labeltitle']) && isset($data['ph']) ){ $data['labeltitle'] = $data['ph']; }

        $labeltitle = isset( $data['labeltitle'] ) ? $data['labeltitle'] : '';
        $desc = isset( $data['desc'] ) ? $data['desc'] : '';
        $groupclass = isset( $data['groupclass'] ) ? $data['groupclass'] : '';
        $dstyle = isset( $data['dstyle'] ) ? $data['dstyle'] : '';
        $btn = isset( $data['btn'] ) ? $data['btn'] : '';
        $igroup = isset( $data['igroup'] ) ? $data['igroup'] : '';

        if ( ! empty($btn) ){
            return self::templateInputGroup('btn', $label, $p, $labeltitle, $desc, $btn);
        }elseif( ! empty($igroup) ){
            return self::templateInputGroup('group', $label, $p, $labeltitle, $desc, $igroup);
        }
        return self::template($label, $p, $labeltitle, $desc, $groupclass, $dstyle);
    }

    /**
     *  Form'a Select2 AJAX ekler...
     *
     *  @param string $name Name
     *  @param string $label Label
     *  @param string $ajaxUrl Varsayılan boştur. İstek gönderilecek ajax url set edilir.
     *  @param string $defKey Varsayılan boştur. Option key set edilir.
     *  @param string $defValue Varsayılan boştur. Option value set edilir.
     *  @param array $data Varsayılan boştur. Keyler: <br>id<br>class<br>disabled<br>readonly<br>labeltitle<br>desc<br>ph<br>btn array('btext'=>'', 'url'=>'', 'bfid'=>'', 'msize'=>'lg', 'bclass'=>'')<br>oprows=true<br>fulllist=true<br>multiple<br>tags<br>required<br>groupclass<br>template
     *  @return mixed Değer döndürmez.
     */
    public function selectAsync($name, $label, $ajaxUrl, $defKey='', $defValue='', $data = [])
    {
        $p = [];
        $p[] = '<select style="display:none" set_select2Ajax="1"';
        $p[] = 'ajaxUrl="'.$ajaxUrl.'"';

        if ( !empty($data['id']) ){ $p[] = 'id="'.$data['id'].'"'; }
        if ( !empty($data['multiple']) ){ $p[] = 'multiple="multiple"'; }
        if ( !empty($data['class']) ){ $p[] = 'class="form-control input-sm '.$data['class'].'"'; }else{ $p[] = 'class="form-control input-sm"'; }
        if ( !empty($name) ){ $p[] = 'name="'.$name.'"'; }
        if ( !empty($data['disabled']) ){ $p[] = 'disabled="disabled"'; }
        if ( !empty($data['ph']) ){ $p[] = 'placeholder="'.$data['ph'].'"'; }else{ $p[] = 'placeholder="Seçiniz..."'; }
        if ( !empty($data['readonly']) ){ $p[] = 'readonly="readonly"'; }
        if ( !empty($data['oprows']) ){ $p[] = 'oprows="1"'; }
        if ( !empty($data['fulllist']) ){ $p[] = 'fulllist="1"'; }
        if ( !empty($data['tinymce_id']) ){ $p[] = 'tinymce_id="'.$data['tinymce_id'].'"'; }
        if ( !empty($data['tags']) ){ $p[] = 'app-tags="1"'; }
        if ( !empty($data['template']) ){ $p[] = 'app-template="'.$data['template'].'"'; }

        if (!empty($defKey)){
            if (!empty($data['multiple'])){ // Multi
                $options='';
                foreach ((array)$defKey as $keys) {
                    $options.='<option value="'.$keys['id'].'" selected="selected">'.$keys['value'].'</option>';
                }
            }else{
                $options='<option value="'.$defKey.'" selected="selected">'.$defValue.'</option>';
            }
        }else{
            $options='<option value="">'.( empty($data['ph']) ? 'Seçiniz...' : $data['ph'] ).'</option>';
        }

        $p[]='>'.$options.'</select>';

        if ( $this->_only  || !empty($data['only'])){ return implode(' ', $p); }
        if ( empty($data['labeltitle']) && isset($data['ph']) ){ $data['labeltitle'] = $data['ph']; }


        $labeltitle = isset( $data['labeltitle'] ) ? $data['labeltitle'] : '';
        $desc = isset( $data['desc'] ) ? $data['desc'] : '';
        $btn = isset( $data['btn'] ) ? $data['btn'] : '';
        $igroup = isset( $data['igroup'] ) ? $data['igroup'] : '';
        $groupclass = isset( $data['groupclass'] ) ? $data['groupclass'] : '';
        $extraLine = isset( $data['extraLine'] ) ? $data['extraLine'] : '';

        if ( !empty($data['btn']) ){
            return self::templateInputGroup('btn', $label, $p, $labeltitle, $desc, $btn, $extraLine);
        }elseif( !empty($data['igroup']) ){
            return self::templateInputGroup('group', $label, $p, $labeltitle, $desc, $igroup, $extraLine);
        }

        return self::template($label, $p, $labeltitle, $desc, $groupclass, '', $extraLine);
    }

    /**
     *  Form'a Select2 ekler...
     *
     *  @param string $name Name
     *  @param string $label Label
     *  @param string $value Varsayılan boştur. Value set edilir.
     *  @param array $list Varsayılan boştur. Option listesi set edilir. Örnek: array(array('id'=>1, 'value'=>'Ornek', 'icon'=>'fa fa-ok'))
     *  @param array $data Varsayılan boştur. Keyler: <br>lid -> array() option id keyi<br>lvalue -> array() option value keyi<br>select2 -> Select2 set edilir.<br>icon -> true|false<br>id<br>class<br>disabled<br>readonly<br>labeltitle<br>desc<br>ph<br>multiple
     *  @return none Değer döndürmez.
     */
    public function select($name, $label, $value='', $list=array(), $data=array())
    {
        $data['select2'] = true;
        return self::selectNative($name, $label, $value, $list, $data);
    }

    public function selectMulti($name, $label, $value='', $list=array(), $data=array())
    {
        $data['select2'] = true;
        $data['multiple'] = true;
        return self::selectNative($name, $label, $value, $list, $data);
    }

    /**
     *  Form'a Select ekler...
     *
     *  @param string $name Name
     *  @param string $label Label
     *  @param string $value Varsayılan boştur. Value set edilir.
     *  @param array $list Varsayılan boştur. Option listesi set edilir. Örnek: array(array('id'=>1, 'value'=>'Ornek', 'icon'=>'fa fa-ok', 'icon-color'=>'#000'))
     *  @param array $data Varsayılan boştur. Keyler: <br>lid -> array() option id keyi<br>lvalue -> array() option value keyi<br>select2 -> Select2 set edilir.<br>icon -> true|false<br>id<br>class<br>disabled<br>readonly<br>labeltitle<br>desc<br>ph<br>min-width=40<br>multiple<br>show-search
     *  @return none Değer döndürmez.
     */
    public function selectNative($name, $label, $value='', $list=array(), $data=array())
    {
        $p=array();
        $p[]='<select ';
        if ( !empty($data['id']) ){ $p[]='id="'.$data['id'].'"'; }
        if ( !empty($data['multiple']) ){ $p[]='multiple="multiple"'; }
        if ( !empty($name) ){ $p[]='name="'.$name.'"'; }
        if ( empty($data['multiple']) && !empty($value) ){ $p[]='value="'.$value.'"'; }
        if ( !empty($data['select2']) ){ $p[]='set_select2="1"'; }
        if ( !empty($data['icon']) ){ $p[]='data-icon="1"'; }
        if ( empty($data['class']) ){ $p[]='class="form-control input-sm"'; }else{ $p[]='class="form-control input-sm '.$data['class'].'"'; }
        if ( !empty($data['disabled']) ){ $p[]='disabled="disabled"'; }
        if ( !empty($data['ph']) ){ $p[]='placeholder="'.$data['ph'].'"'; }else{ $p[]='placeholder="Seçiniz..."'; }
        if ( !empty($data['readonly']) ){ $p[]='readonly="readonly"'; }
        if ( !empty($data['app-class']) ){ $p[]='app-class="'.$data['app-class'].'"'; }
        if ( empty($data['show-search']) ){ $p[]='app-hide-search="1"'; }
        if ( isset($data['allow-clear']) && $data['allow-clear'] === false ){ /* False */ }else{ $p[]='allow-clear="1"'; }
        if ( !empty($data['min-width']) ){ $p[]='style="min-width:'.$data['min-width'].'px !important"'; }

        // List ID
        if (empty($data['lid'])){ $lid='id'; }else{ $lid=$data['lid']; }
        if (empty($data['lvalue'])){ $lival='value'; }else{ $lival=$data['lvalue']; }

        $options='';

        if ( empty($data['multiple']) ){

            // Single
            foreach($list as $li){
                $options.='<option value="'.$li[$lid].'"';
                // Value varsa selected
                if ($value==$li[$lid]){ $options.=' selected="selected"'; }
                // Icon varsa ekle
                if (!empty($li['icon'])){ $options.=' data-icon="'.@$li['icon'].'" data-icon-color="'.@$li['icon-color'].'"'; }
                $options.='>'.$li[$lival].'</option>';
            }
            $p[]='><option value="">'.( empty($data['ph']) ? 'Seçiniz...' : $data['ph'] ).'</option>'.$options.'</select>';

        }else{

            // Multi
            foreach($list as $li){
                $options.='<option value="'.$li[$lid].'"';

                // Values varsa selected
                if (in_array($li[$lid], $value)){
                    $options.=' selected="selected"';
                }

                // Icon varsa ekle
                if ( isset($li['icon'], $li['icon-color']) ){
                    $options.=' data-icon="'.$li['icon'].'" data-icon-color="'.$li['icon-color'].'"';
                }

                $options.='>'.$li[$lival].'</option>';
            }
            $p[]='><option value="">'.( empty($data['ph']) ? 'Seçiniz...' : $data['ph'] ).'</option>'.$options.'</select>';
        }

        if ($this->_return || !empty($data['only'])){ return implode(' ', $p); }

        if (empty($data['labeltitle'])){ $data['labeltitle']=@$data['ph']; }

        if(!empty($data['btn'])){
            return self::templateInputGroup('btn', $label, $p, @$data['labeltitle'], @$data['desc'], @$data['btn']);
        }else{
            return self::template($label, $p, @$data['labeltitle'], @$data['desc']);
        }
    }

    /**
     *  Form'a Datepicker ekler...
     *
     *  @param string $name Name
     *  @param string $label Label
     *  @param string $value Varsayılan boştur. Value set edilir.
     *  @param array $data Varsayılan boştur. Keyler: <br>id<br>class<br>disabled<br>readonly<br>labeltitle<br>desc<br>picker
     *  @return none Değer döndürmez.
     */
    public function date($name, $label, $value='', $data = [])
    {
        $p = [];
        $p[] = '<input type="text"';
        if ( isset($data['picker']) && $data['picker'] === 'year' ){
            $p[] = 'data-app-yearpicker="1"';
            $p[] = 'maxlength="4"';
        }else{
            $p[] = 'set_datepicker="1"';
            $p[] = 'maxlength="10"';
        }
        if ( !empty($data['id']) ){ $p[] = 'id="'.$data['id'].'"'; }
        if ( !empty($name) ){ $p[] = 'name="'.$name.'"'; }
        if ( !empty($value) ){ $p[] = 'value="'.$value.'"'; }
        if ( !empty($data['class']) ){ $p[] = 'class="form-control input-sm '.$data['class'].'"'; }else{ $p[] = 'class="form-control input-sm"'; }
        if ( !empty($data['disabled']) ){ $p[] = 'disabled="disabled"'; }
        if ( Core::isMobile() ){
            $p[] = 'readonly="readonly"';
        }else{
            if ( !empty($data['readonly']) ){ $p[] = 'readonly="readonly"'; }
        }
        if ( !empty($data['ph']) ){ $p[] = 'placeholder="'.$data['ph'].'"'; }
        if ( !empty($data['startdate']) ){ $p[] = 'data-app-start-date="'.$data['startdate'].'"'; }
        if ( !empty($data['enddate']) ){ $p[] = 'data-app-end-date="'.$data['enddate'].'"'; }
        if ( !empty($data['format']) ){ $p[] = 'data-app-format="'.$data['format'].'"'; }
        $p[] = 'autocomplete="off"';
        $p[] = '/>';

        if ( $this->_only || !empty($data['only'])){ return implode(' ', $p); }
        if ( empty($data['labeltitle']) && isset($data['ph']) ){ $data['labeltitle'] = $data['ph']; }

        return self::template($label, $p, @$data['labeltitle'], @$data['desc']);
    }

    public function datetime($datename, $timename, $label, $datevalue='', $timevalue='', $datedata = [], $timedata = [], $data = [])
    {
        $datedata['only'] = true;
        $timedata['only'] = true;

        $p = [];

        $p[] = '<div class="row">';

        $p[] = '<div class="col-xs-6" style="padding-right: 5px">';
        $p[] = $this->date($datename, '', $datevalue, $datedata);
        $p[] = '</div>';

        $p[] = '<div class="col-xs-6" style="padding-left: 5px">';
        $p[] = $this->time($timename, '', $timevalue, $timedata);
        $p[] = '</div>';

        $p[] = '</div>';

        if ( empty($data['labeltitle']) ){ $data['labeltitle'] = $label; }
        if ( empty($data['desc']) ){ $data['desc'] = null; }


        return self::template($label, $p, $data['labeltitle'], $data['desc']);
    }

    public function dateDate($date1name, $date2name, $label, $date1value='', $date2value='', $date1data = [], $date2data = [], $data = [])
    {
        $date1data['only'] = true;
        $date2data['only'] = true;

        $p = [];

        $p[] = '<div class="row">';

        $p[] = '<div class="col-xs-6" style="padding-right: 5px">';
        $p[] = $this->date($date1name, '', $date1value, $date1data);
        $p[] = '</div>';

        $p[] = '<div class="col-xs-6" style="padding-left: 5px">';
        $p[] = $this->date($date2name, '', $date2value, $date2data);
        $p[] = '</div>';

        $p[] = '</div>';

        if ( empty($data['labeltitle']) ){ $data['labeltitle'] = $label; }
        if ( empty($data['desc']) ){ $data['desc'] = null; }


        return self::template($label, $p, $data['labeltitle'], $data['desc']);
    }

    public function decimalDecimal($dec1name, $dec2name, $label, $dec1value='', $dec2value='', $min = '0.00', $max = '99999999.99', $sep='.', $dec = ',', $dec1data = [], $dec2data = [], $data = [])
    {
        $dec1data['only'] = true;
        $dec2data['only'] = true;

        $p = [];

        $p[] = '<div class="row">';

        $p[] = '<div class="col-xs-6" style="padding-right: 5px">';
        $p[] = $this->decimal($dec1name, '', $dec1value, $min, $max, $sep, $dec, $dec1data);
        $p[] = '</div>';

        $p[] = '<div class="col-xs-6" style="padding-left: 5px">';
        $p[] = $this->decimal($dec2name, '', $dec2value, $min, $max, $sep, $dec, $dec2data);
        $p[] = '</div>';

        $p[] = '</div>';

        if ( empty($data['labeltitle']) ){ $data['labeltitle'] = $label; }
        if ( empty($data['desc']) ){ $data['desc'] = null; }


        return self::template($label, $p, $data['labeltitle'], $data['desc']);
    }

    public function numberNumber($dec1name, $dec2name, $label, $dec1value='', $dec2value='', $min = 0, $max = 9999, $sep='.', $dec1data = [], $dec2data = [], $data = [])
    {
        $dec1data['only'] = true;
        $dec2data['only'] = true;

        $p = [];

        $p[] = '<div class="row">';

        $p[] = '<div class="col-xs-6" style="padding-right: 5px">';
        $p[] = $this->number($dec1name, '', $dec1value, $min, $max, $sep, $dec1data);
        $p[] = '</div>';

        $p[] = '<div class="col-xs-6" style="padding-left: 5px">';
        $p[] = $this->number($dec2name, '', $dec2value, $min, $max, $sep, $dec2data);
        $p[] = '</div>';

        $p[] = '</div>';

        if ( empty($data['labeltitle']) ){ $data['labeltitle'] = $label; }
        if ( empty($data['desc']) ){ $data['desc'] = null; }


        return self::template($label, $p, $data['labeltitle'], $data['desc']);
    }

    public function textSelect($textname, $selectName, $label, $textvalue='', $selectvalue='', $textMaxlength='', $selectList=[], $textData = [], $selectData = [], $data = [])
    {
        $textData['only'] = true;
        $selectData['only'] = true;

        $p = [];

        $p[] = '<div class="row">';

        $p[] = '<div class="col-xs-6" style="padding-right: 5px">';
        $p[] = $this->text($textname, '', $textvalue, $textMaxlength, $textData);
        $p[] = '</div>';

        $p[] = '<div class="col-xs-6" style="padding-left: 5px">';
        $p[] = $this->select($selectName, '', $selectvalue, $selectList, $selectData);
        $p[] = '</div>';

        $p[] = '</div>';

        if ( empty($data['labeltitle']) ){ $data['labeltitle'] = $label; }
        if ( empty($data['desc']) ){ $data['desc'] = null; }


        return self::template($label, $p, $data['labeltitle'], $data['desc']);
    }

    public function textText($text1name, $text2name, $label, $text1value='', $text2value='', $text1data = [], $text2data = [], $data = [])
    {
        $text1data['only'] = true;
        $text2data['only'] = true;

        $p = [];

        $p[] = '<div class="row">';

        $p[] = '<div class="col-xs-6" style="padding-right: 5px">';
        $p[] = $this->text($text1name, '', $text1value, $text1data['max'], $text1data);
        $p[] = '</div>';

        $p[] = '<div class="col-xs-6" style="padding-left: 5px">';
        $p[] = $this->text($text2name, '', $text2value, $text2data['max'], $text2data);
        $p[] = '</div>';

        $p[] = '</div>';

        if ( empty($data['labeltitle']) ){ $data['labeltitle'] = $label; }
        if ( empty($data['desc']) ){ $data['desc'] = null; }


        return self::template($label, $p, $data['labeltitle'], $data['desc']);
    }

    public function decimalSelect($decimalname, $selectName, $label, $decimalValue='', $selectvalue='', $decimalMin='0.00', $decimalMax='', $decimalSep='.', $decimalDec=',', $selectList=[], $decimalData = [], $selectData = [], $data = [])
    {
        $decimalData['only'] = true;
        $selectData['only'] = true;

        $p = [];

        $p[] = '<div class="row">';

        $p[] = '<div class="col-xs-6" style="padding-right: 5px">';
        $p[] = $this->decimal($decimalname, '', $decimalValue, $decimalMin, $decimalMax, $decimalSep, $decimalDec, $decimalData);
        $p[] = '</div>';

        $p[] = '<div class="col-xs-6" style="padding-left: 5px">';
        $p[] = $this->select($selectName, '', $selectvalue, $selectList, $selectData);
        $p[] = '</div>';

        $p[] = '</div>';

        if ( empty($data['labeltitle']) ){ $data['labeltitle'] = $label; }
        if ( empty($data['desc']) ){ $data['desc'] = null; }


        return self::template($label, $p, $data['labeltitle'], $data['desc']);
    }

    /**
     *  Form'a Textarea ekler...
     *
     *  @param string $name Name
     *  @param string $label Label
     *  @param string $value Varsayılan boştur. Value set edilir.
     *  @param string $rows Varsayılan 3'tür. Satır sayısı set edilir.
     *  @param array $data Varsayılan boştur. Keyler: <br>maxlength<br>id<br>class<br>disabled<br>readonly<br>labeltitle<br>desc<br>ph<br>html<br>tinymce<br>groupclass<br>dstyle<br>attr[]
     *  @return none Değer döndürmez.
     */
    public function textArea($name, $label, $value='', $rows=3, $data=[])
    {
        $p=array();
        $p[]='<textarea style="resize: vertical"';
        if ( !empty($data['id']) ){ $p[]='id="'.$data['id'].'"'; }
        if ( !empty($name) ){ $p[]='name="'.$name.'"'; }
        $p[]='rows="'.$rows.'"';
        if ( !empty($data['maxlength']) ){ $p[]='maxlength="'.$data['maxlength'].'"'; }
        if ( empty($data['class']) ){ $p[]='class="form-control input-sm"'; }else{ $p[]='class="form-control input-sm '.$data['class'].'"'; }
        if ( !empty($data['disabled']) ){ $p[]='disabled'; }
        if ( !empty($data['ph']) ){ $p[]='placeholder="'.$data['ph'].'"'; }
        if ( !empty($data['readonly']) ){ $p[]='readonly="readonly"'; }
        if ( !empty($data['tinymce']) ){ $p[]='set_tinymce="1"'; }
        if ( !empty($data['attr'])){
            foreach ((array)$data['attr'] as $k => $v) {
                $p[]=$k.'="'.$v.'"';
            }
        }
        if ( !empty($data['html']) ){ $p[]='>'.$value.'</textarea>'; }else{ $p[]='>'.strip_tags($value).'</textarea>'; }
        if ($this->_only || !empty($data['only'])){ return implode(' ', $p); }

        if (empty($data['labeltitle'])){ $data['labeltitle']=@$data['ph']; }
        return self::template($label, $p, @$data['labeltitle'], @$data['desc'],@$data['groupclass'],@$data['dstyle']);
    }

    /**
     *  Form'a File Input ekler...
     *
     *  @param string $name Name
     *  @param string $label Label
     *  @param string $accept Varsayılan boştur. Seçilebilecek dosya türleri belirtilir. Örnek: image/*
     *  @param string $multiple Varsayılan boştur. Birden çok dosya seçilmesine izin vermek için true set edilir.
     *  @param array $data Varsayılan boştur. Keyler: <br>id<br>class<br>disabled<br>readonly<br>labeltitle<br>desc
     *  @return none Değer döndürmez.
     */
    public function file($name, $label, $accept='', $multiple='', $data=array())
    {
        $p = [];
        $p[]='<input type="file"';
        if ( !empty($data['id']) ){ $p[]='id="'.$data['id'].'"'; }
        if ( !empty($name) ){ $p[]='name="'.$name.'"'; }
        if ( !empty($accept) ){ $p[]='accept="'.$accept.'"'; }
        if ( !empty($multiple) ){ $p[]='multiple="multiple"'; }
        if ( !empty($data['class']) ){ $p[]='class="form-control input-sm '.$data['class'].'"'; }else{ $p[]='class="form-control input-sm"'; }
        if ( !empty($data['disabled']) ){ $p[]='disabled="disabled"'; }
        if ( !empty($data['readonly']) ){ $p[]='readonly="readonly"'; }
        $p[]='autocomplete="off"';
        $p[]='/>';
        if ($this->_only || !empty($data['only'])){ return implode(' ', $p); }

        $labeltitle = isset( $data['labeltitle'] ) ? $data['labeltitle'] : '';
        $desc = isset( $data['desc'] ) ? $data['desc'] : '';

        return self::template($label, $p, $labeltitle, $desc);
    }


    /**
     *  Form'a Submit Input ekler...
     *
     *  @param string $name Name
     *  @param string $label Label
     *  @param string $value Varsayılan Gönder'dir. Value set edilir.
     *  @param array $data Varsayılan boştur. Keyler: <br>id<br>class<br>ltext (Loading Text)<br>disabled<br>readonly<br>labeltitle<br>label
     *  @return none Değer döndürmez.
     */
    public function submit($value='SAVE', $data=[])
    {
        $p=array();
        $p[]='<button type="submit"';
        $p[]='name="'.empty($data['name']) ? 'submit' : $data['name'].'"';
        if ( !empty($value) ){ $p[]='value="'.$value.'"'; }
        if ( !empty($data['id']) ){ $p[]='id="'.$data['id'].'"'; }
        if ( !empty($data['disabled']) ){ $p[]='disabled="disabled"'; }
        if ( !empty($data['readonly']) ){ $p[]='readonly="readonly"'; }
        if ( empty($data['class']) ){
            $p[]='class="btn btn-success btn-block btn-sm'.($this->_type=='filter' ? ' filter-form-button':'').'"';
        }else{
            $p[]='class="'.$data['class'].'"';
        }
        if ($this->_ajaxform==true && $this->_type!='filter'){
            // Loading Text
            $p[]='data-loading-text="'.(empty($data['ltext']) ? 'Yükleniyor...' : $data['ltext'] ).'"';

            $p[]='data-normal-text="'.$value.'"';
        }
        $p[]='>';

        if ($this->_ajaxform==true && $this->_type!='filter'){
            $p[]='<span style="display:none;" class="fa fa-refresh fa-spin fa-fw"></span>';
        }
        $p[]='<span class="butonLabel">'.$value.'</span></button>';
        if ( $this->_only || ! empty($data['only']) ){ return implode(' ', $p); }

        if (isset($data['label']) && $data['label']===false){
            $label = false;
        }else{
            $label = true;
        }

        return
            '<div class="form-group">'.
            '<div class="'.($label===true ? 'col-sm-9 col-sm-offset-3' : 'col-sm-12').'">'.implode(' ', $p).'</div>'.
            '</div>';
    }

    public function htmlWithLabel($label, $html, $data=array())
    {
        if ($this->_only || ! empty($data['only'])){ return $html; }

        $labeltitle = isset( $data['labeltitle'] ) ? $data['labeltitle'] : '';
        $desc = isset( $data['desc'] ) ? $data['desc'] : '';

        return self::template($label, [$html], $labeltitle, $desc);
    }

    /**
     *  Form'a Timepicker ekler...
     *
     *  @param string $name Name
     *  @param string $label Label
     *  @param string $value Varsayılan boştur. Value set edilir.
     *  @param array $data Varsayılan boştur. Keyler: <br>id<br>class<br>disabled<br>readonly<br>labeltitle<br>desc<br>max
     *  @return none Değer döndürmez.
     */
    public function time($name, $label, $value='', $data=array())
    {
        $p=array();
        $p[] = '<input type="text"';
        $p[] = 'set_timepicker="1"';
        if ( !empty($data['id']) ){ $p[]='id="'.$data['id'].'"'; }
        if ( !empty($name) ){ $p[]='name="'.$name.'"'; }
        if ( !empty($value) ){ $p[]='value="'.substr($value, 0, 5).'"'; }
        if ( empty($data['class']) ){ $p[]='class="form-control input-sm"'; }else{ $p[]='class="form-control input-sm '.$data['class'].'"'; }
        if ( !empty($data['disabled']) ){ $p[]='disabled="disabled"'; }
        if ( !empty($data['readonly']) ){ $p[]='readonly="readonly"'; }
        if ( !empty($data['max']) ){ $p[]='maxlength="'.$data['max'].'"'; }else{ $p[]='maxlength="5"'; }
        if ( !empty($data['ph']) ){ $p[]='placeholder="'.$data['ph'].'"'; }
        $p[]='autocomplete="off"';
        $p[]='/>';

        if ($this->_only || ! empty($data['only'])){ return implode(' ', $p); }
        $labeltitle = isset( $data['labeltitle'] ) ? $data['labeltitle'] : '';
        $desc = isset( $data['desc'] ) ? $data['desc'] : '';

        return self::template($label, $p, $labeltitle, $desc);
    }

    public function templateCheckbox($label, $tag, $desc='')
    {
        return
            '<div class="form-group" style="margin-bottom:0px">
            <div class="col-sm-offset-3 col-sm-9">
                <div class="checkbox">
                    <label>'.implode(' ', $tag).' '.$label.'</label>
                </div>
            </div>'.
            ( empty($desc) ? '' : $desc).
            '</div>';
    }

    /**
     *  Form'a Input File bağlantısını ekler.
     *
     *  @param string $label Label
     *  @param string $url Dosya linki belirtilir.
     *  @param string $title Dosya adı, uzantısı ile birlikte berlitilir.
     *  @param array $data Varsayılan boştur. Keyler: <br>labeltitle<br>desc
     *  @return none Değer döndürmez.
     */
    public function addInputFileUrl($label, $url, $title, $data=array())
    {
        $p=array();
        $p[]='<a style="padding: 3px 0 0 0;" class="btn btn-link" target="_blank" href="'.$url.'">'.$title.'</a>';
        $this->_html.=self::template($label, $p, @$data['labeltitle'], @$data['desc']);
    }

    /**
     *  Form'a html kod ekler...
     *
     *  @param string $html Html kodlar set edilir.
     *  @return none Değer döndürmez.
     */
    public function addHtml($html)
    {
        $this->_html.=$html;
    }

    /**
     *  Form'a Checkbox Input ekler...
     *
     *  @param string $name Name
     *  @param string $label Label
     *  @param string $value Varsayılan boştur. Value set edilir.
     *  @param bool $checked Varsayılan false'tur. Seçili ise true set edilir.
     *  @param array $data Varsayılan boştur. Keyler: <br>id<br>class<br>disabled<br>readonly<br>desc
     *  @return none Değer döndürmez.
     */
    public function addInputCheckbox($name, $label, $value='', $checked=false, $data=array())
    {
        $p=array();
        $p[]='<input type="checkbox"';
        if ( !empty($data['id']) ){ $p[]='id="'.$data['id'].'"'; }
        if ( !empty($name) ){ $p[]='name="'.$name.'"'; }
        if ( !empty($value) ){ $p[]='value="'.$value.'"'; }
        if ( empty($data['class']) ){ $p[]='class="checkbox_margin"'; }else{ $p[]='class="'.$data['class'].'"'; }
        if ( !empty($checked) ){ $p[]='checked="checked"'; }
        if ( !empty($data['disabled']) ){ $p[]='disabled="disabled"'; }
        if ( !empty($data['readonly']) ){ $p[]='readonly="readonly"'; }
        $p[]='autocomplete="off"';
        $p[]='/>';
        if ($this->_return){ return implode(' ', $p); }

        $this->_html.=self::templateCheckbox($label, $p, @$data['desc']);
    }

    public function password($name, $label, $value = '', $maxlength = '', $data = [])
    {
        $p = [];
        $p[]='<input type="password"';
        if ( !empty($data['id']) ){ $p[]='id="'.$data['id'].'"'; }
        if ( !empty($name) ){ $p[]='name="'.$name.'"'; }
        if ( !empty($value) ){ $p[]='value="'.str_replace('"', '”', $value).'"'; }
        if ( !empty($data['class']) ){ $p[]='class="form-control input-sm '.$data['class'].'"'; }else{ $p[]='class="form-control input-sm"'; }
        if ( !empty($data['disabled']) ){ $p[]='disabled'; }
        if ( !empty($data['readonly']) ){ $p[]='readonly="readonly"'; }
        if ( !empty($data['ph']) ){ $p[]='placeholder="'.$data['ph'].'"'; }
        if ( !empty($maxlength) ){ $p[]='maxlength="'.$maxlength.'"'; }
        if ( !empty($data['min-width']) ){ $p[]='style="min-width:'.$data['min-width'].'px !important"'; }
        $p[]='autocomplete="new-password"';
        $p[]='/>';

        if ($this->_only || !empty($data['only'])){ return implode(' ', $p); }
        if ( empty($data['labeltitle']) && isset($data['ph']) ){ $data['labeltitle'] = $data['ph']; }

        $labeltitle = isset( $data['labeltitle'] ) ? $data['labeltitle'] : '';
        $desc = isset( $data['desc'] ) ? $data['desc'] : '';
        $groupclass = isset( $data['groupclass'] ) ? $data['groupclass'] : '';
        $dstyle = isset( $data['dstyle'] ) ? $data['dstyle'] : '';
        $btn = isset( $data['btn'] ) ? $data['btn'] : '';
        $igroup = isset( $data['igroup'] ) ? $data['igroup'] : '';

        if ( ! empty($btn) ){
            return self::templateInputGroup('btn', $label, $p, $labeltitle, $desc, $btn);
        }elseif( ! empty($igroup) ){
            return self::templateInputGroup('group', $label, $p, $labeltitle, $desc, $igroup);
        }
        return self::template($label, $p, $labeltitle, $desc, $groupclass, $dstyle);
    }
}