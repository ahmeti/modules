<?php

namespace Ahmeti\Modules\Core\Services;

use App\Core;

class ResponseService {

    private $_status;
    private $_message;
    private $_errorName;
    private $_goUrl;
    private $_setData;
    private $_data;
    private $_targetId;
    private $_output;
    private $_baseId;
    private $_hideModal;

    private $_title;
    private $_breadcrumb;
    private $_body;
    private $_jsCode;
    private $_jsFiles;
    private $_jsCallbacks;


    public function status($status)
    {
        $this->_status = $status;
        return $this;
    }

    public function message($message)
    {
        $this->_message = $message;
        return $this;
    }

    public function errorName($errorName)
    {
        $this->_errorName = $errorName;
        return $this;
    }

    public function goUrl($goUrl)
    {
        $this->_goUrl = $goUrl;
        return $this;
    }

    public function setData($setData)
    {
        $this->_setData = $setData;
        return $this;
    }

    public function data($data)
    {
        $this->_data = $data;
        return $this;
    }

    public function targetId($targetId)
    {
        $this->_targetId = $targetId;
        return $this;
    }

    public function output($output)
    {
        $this->_output = $output;
        return $this;
    }

    public function baseid($baseId)
    {
        $this->_baseId = $baseId;
        return $this;
    }

    public function hideModal($hideModal)
    {
        $this->_hideModal = $hideModal;
        return $this;
    }

    # ------------------------------


    public function title($title)
    {
        $this->_title = $title;
        return $this;
    }

    public function breadcrumbs($breadcrumb)
    {
        $this->_breadcrumb = $breadcrumb;
        return $this;
    }

    public function body($body)
    {
        $this->_body = $body;
        return $this;
    }

    public function jsCode($jsCode)
    {
        $this->_jsCode = $jsCode;
        return $this;
    }

    public function jsFiles(array $jsFiles)
    {
        $this->_jsFiles = $jsFiles;
        return $this;
    }

    public function jsCallbacks(array $jsCallbacks)
    {
        $this->_jsCallbacks = $jsCallbacks;
        return $this;
    }

    // hedef => targetid
    // jsfile => jsfiles
    // callback => jscallbacks

    public function form()
    {
        return response()->json([
            'status'      => (bool)$this->_status,
            'message'     => $this->_message,
            'errorname'   => $this->_errorName,

            'baseid'      => $this->_baseId ? $this->_baseId : request('baseid'),
            'targetid'    => $this->_targetId ? $this->_targetId : request('targetid'),
            'output'      => $this->_output ? $this->_output : request('output'),

            'gourl'       => $this->_goUrl,
            'setdata'     => $this->_setData,
            'hidemodal'   => $this->_hideModal ? $this->_hideModal : request('hidemodal'),

            'jscode'      => $this->_jsCode,
        ]);

    }

    public function page()
    {
        return response()->json([
            'title'       => $this->_title,

            'breadcrumb'  => Core::getBreadcrumbs(),
            'body'        => $this->_body,

            'gourl'       => $this->_goUrl,

            'jscode'      => $this->_jsCode,
            'jsfiles'     => $this->_jsFiles,
            'jscallbacks' => $this->_jsCallbacks,
        ]);
    }

    public function simple()
    {
        return response()->json([
            'status'    => (bool)$this->_status === true,
            'message'   => $this->_message,
            'data'      => $this->_data,
        ]);
    }
}