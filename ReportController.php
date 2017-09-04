<?php
namespace Modules\Users\Controllers;

use Box\Spout\Common\Type;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\Style\Color;

use Modules\Users\Models\ProductsMaster as ProductsMaster;
use Modules\Users\Models\SalesReport as SalesReport;
use Modules\Users\Models\Franchises as Franchises;
use Common\Plugins\CustomInsert as CustomInsert;

ini_set("display_errors", 1);
ini_set('max_execution_time', 0);
ini_set('memory_limit', '-1');

class ReportController extends ControllerBase
{
    private $access = '';
    public function initialize()
    {
        if (is_null($this->session->get('authUser'))) {
            $this->response->redirect('/member/index/index');
        } else {
            $sessionData              = $this->session->get("authUser");
            $this->access = $sessionData['access'];
            $access = $sessionData['access'];
            $this->view->loginStatus  = 1;
            $userName = $sessionData['user_name'];
            $checkMenuAccess = $this->CommonHelper->checkMenuAccess($userName, $this->config['access']['import']);
            $checkReportAccess = $this->CommonHelper->checkMenuAccess($userName, $this->config['access']['report']);
            $this->view->importAccess = $checkMenuAccess;
            $this->view->reportAccess = $checkReportAccess;
            $this->view->userName = $userName;
        }
    }


    public function indexAction()
    {
    }

    public function salesAction()
    {
        $params           = $this->dispatcher->getParams();
        $year             = isset($params[0]) ? $params[0] : '';
        $graphType        = isset($params[1]) ? $params[1] : '';

        if ($year == '') {
            $year = date('Y');
        }

        if ($graphType == '') {
            $graphType = 'consol';
        }

        $this->assets
      ->collection('footer')
      ->addJs('js/amcharts/amcharts.js')
      ->addJs('js/amcharts/xy.js')
      ->addJs('js/amcharts/serial.js')
      ->addJs('js/amcharts/themes/light.js')
      ->addJs('js/graphSales.js')
      ->addJs('js/custom.js');

        $this->assets
      ->collection('header')
      ->addCss('css/reportCenter.css')
      ->addCss('css/custom.css');
        $this->view->access = $this->access[0];
        $this->view->leftMenu  = 'Sales Trend';
        $sales_model = new SalesReport;
        $franchise_model = new Franchises;
        $get_franchise_total = $franchise_model->getTotalShops();
        $internal_commodity = $sales_model->internalSalesCommodity('', $year);
        $internal_ncommodity = $sales_model->internalSalesNonCommodity('', $year);
        $internal_fnv = $sales_model->internalSalesFNV('', $year);

        $month = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');

        $report_consolidated = array();
        for ($i=0;$i<12;$i++) {
            $report_consolidated[$i]['month'] = $month[$i];
            $nextIndex = $i + 1;
            if (isset($internal_commodity[$nextIndex])) {
                $report_consolidated[$i]['commodity'] = $internal_commodity[$nextIndex];
                $report_consolidated[$i]['commodityValue'] = $this->CommonHelper->inLakhs($internal_commodity[$nextIndex]);
            } else {
                $report_consolidated[$i]['commodity'] = 0;
                $report_consolidated[$i]['commodityValue'] = 0;
            }

            if (isset($internal_ncommodity[$nextIndex])) {
                $report_consolidated[$i]['ncommodity'] = $internal_ncommodity[$nextIndex];
                $report_consolidated[$i]['ncommodityValue'] = $this->CommonHelper->inLakhs($internal_ncommodity[$nextIndex]);
            } else {
                $report_consolidated[$i]['ncommodity'] = 0;
                $report_consolidated[$i]['ncommodityValue'] = 0;
            }

            if (isset($internal_fnv[$nextIndex])) {
                $report_consolidated[$i]['fnv'] = $internal_fnv[$nextIndex];
                $report_consolidated[$i]['fnvValue'] = $this->CommonHelper->inLakhs($internal_fnv[$nextIndex]);
            } else {
                $report_consolidated[$i]['fnv'] = 0;
                $report_consolidated[$i]['fnvValue'] = 0;
            }

            $saleMonthGraph  = json_encode($report_consolidated);
            $this->view->saleMonthGraphJson  = $saleMonthGraph;
            $this->view->reportConsolidated  = $report_consolidated;
            $this->view->month  = $month;
            $this->view->year  = $year;
            $this->view->graphType  = $graphType;
            $this->view->shopTotal  = $get_franchise_total;
        }
    }
    public function returnSalesAction()
    {
        $params           = $this->dispatcher->getParams();
        $year             = isset($params[0]) ? $params[0] : '';
        $graphType        = isset($params[1]) ? $params[1] : '';
        if ($year == '') {
            $year = date('Y');
        }

        if ($graphType == '') {
            $graphType = 'consol';
        }
        $this->assets
      ->collection('footer')
      ->addJs('js/amcharts/amcharts.js')
      ->addJs('js/amcharts/xy.js')
      ->addJs('js/amcharts/serial.js')
      ->addJs('js/amcharts/themes/light.js')
      ->addJs('js/graphSales.js')
      ->addJs('js/custom.js');

        $this->assets
      ->collection('header')
      ->addCss('css/reportCenter.css')
      ->addCss('css/custom.css');
        $this->view->access = $this->access[0];
        $this->view->leftMenu  = 'Return';
        $sales_model = new SalesReport;
        $franchise_model = new Franchises;
        $get_franchise_total = $franchise_model->getTotalShops();
        $internal_commodity = $sales_model->returnSalesCommodity('', $year);
        $internal_ncommodity = $sales_model->returnSalesNonCommodity('', $year);
        $internal_fnv = $sales_model->returnSalesFNV('', $year);

        $month = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');

        $report_consolidated = array();
        for ($i=0;$i<12;$i++) {
            $report_consolidated[$i]['month'] = $month[$i];
            $nextIndex = $i + 1;
            if (isset($internal_commodity[$nextIndex])) {
                $report_consolidated[$i]['commodity'] = $internal_commodity[$nextIndex];
                $report_consolidated[$i]['commodityValue'] =$this->CommonHelper->inLakhs($internal_commodity[$nextIndex]);
            } else {
                $report_consolidated[$i]['commodity'] = 0;
                $report_consolidated[$i]['commodityValue'] = 0;
            }

            if (isset($internal_ncommodity[$nextIndex])) {
                $report_consolidated[$i]['ncommodity'] = $internal_ncommodity[$nextIndex];
                $report_consolidated[$i]['ncommodityValue'] = $this->CommonHelper->inLakhs($internal_ncommodity[$nextIndex]);
            } else {
                $report_consolidated[$i]['ncommodity'] = 0;
                $report_consolidated[$i]['ncommodityValue'] = 0;
            }

            if (isset($internal_fnv[$nextIndex])) {
                $report_consolidated[$i]['fnv'] = $internal_fnv[$nextIndex];
                $report_consolidated[$i]['fnvValue'] = $this->CommonHelper->inLakhs($internal_fnv[$nextIndex]);
            } else {
                $report_consolidated[$i]['fnv'] = 0;
                $report_consolidated[$i]['fnvValue'] = 0;
            }
        }
        $saleMonthGraph  = json_encode($report_consolidated);
        $this->view->returnMonthGraphJson  = $saleMonthGraph;
        $this->view->reportConsolidated  = $report_consolidated;
        $this->view->month  = $month;
        $this->view->year  = $year;
        $this->view->graphType  = $graphType;
        $this->view->shopTotal  = $get_franchise_total;
    }

    public function salesFranchiseAction()
    {
        $params           = $this->dispatcher->getParams();
        $year             = isset($params[0]) ? $params[0] : '';
        $graphType        = isset($params[1]) ? $params[1] : '';
        $shopId           = isset($params[2]) ? $params[2] : '';
        if ($year == '') {
            $year = date('Y');
        }

        if ($graphType == '') {
            $graphType = 'consol';
        }

        if ($shopId == '') {
            $shopId = 1;
        }

        $this->assets
    ->collection('footer')
    ->addJs('js/amcharts/amcharts.js')
    ->addJs('js/amcharts/xy.js')
    ->addJs('js/amcharts/serial.js')
    ->addJs('js/amcharts/themes/light.js')
    ->addJs('js/graphSales.js')
    ->addJs('js/custom.js');

        $this->assets
    ->collection('header')
    ->addCss('css/reportCenter.css')
    ->addCss('css/custom.css');
        $this->view->access = $this->access[0];
        $this->view->leftMenu  = 'Sales Trend';
        $month = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
        $franchise_model = new Franchises;
        $get_franchise = $franchise_model->getFranchisesMap();
        $get_franchise = $franchise_model->getFranchisesDrop();

        $shopName = array();
        $reportId = array();

        foreach ($get_franchise as $key => $franchise) {
            $shop_id = $franchise['franchise_id'];
            $report_id = $franchise['franchise_report_id'];
            $shop_name = $franchise['franchise_name'];
            if ($shop_id != '-1') {
                $shopName[$shop_id] = $shop_name;
                $reportId[$shop_id] = $report_id;
            }
        }


        $sales_model = new SalesReport;
        $resultArray = array();

        foreach ($get_franchise as $key => $franchise) {
            $shop_id = $franchise['franchise_id'];
            $report_id = $franchise['franchise_report_id'];
            $shop_name = $franchise['franchise_name'];
            if ($shop_id != '-1') {
                $internal_commodity = $sales_model->internalSalesCommodity($reportId[$shop_id], $year);
                $internal_ncommodity = $sales_model->internalSalesNonCommodity($reportId[$shop_id], $year);
                $internal_fnv = $sales_model->internalSalesFNV($reportId[$shop_id], $year);

                $resultArrayCommodity[$shop_id] = $internal_commodity;
                $resultArrayNCommodity[$shop_id] = $internal_ncommodity;
                $resultArrayFNV[$shop_id] = $internal_fnv;
            }
        }


        $report_consolidated = array();
        foreach ($get_franchise as $key => $franchise) {
            $shop_id = $franchise['franchise_id'];
            if ($shop_id != '-1') {
                for ($i=0;$i<12;$i++) {
                    $report_consolidated[$shop_id][$i]['month'] = $month[$i];
                    $nextIndex = $i + 1;
                    if (isset($resultArrayCommodity[$shop_id][$nextIndex])) {
                        $report_consolidated[$shop_id][$i]['commodity'] = $resultArrayCommodity[$shop_id][$nextIndex];
                        $report_consolidated[$shop_id][$i]['commodityValue'] = $this->CommonHelper->inLakhs($resultArrayCommodity[$shop_id][$nextIndex]);
                    } else {
                        $report_consolidated[$shop_id][$i]['commodity'] = 0;
                        $report_consolidated[$shop_id][$i]['commodityValue'] = 0;
                    }

                    if (isset($resultArrayNCommodity[$shop_id][$nextIndex])) {
                        $report_consolidated[$shop_id][$i]['ncommodity'] = $resultArrayNCommodity[$shop_id][$nextIndex];
                        $report_consolidated[$shop_id][$i]['ncommodityValue'] = $this->CommonHelper->inLakhs($resultArrayNCommodity[$shop_id][$nextIndex]);
                    } else {
                        $report_consolidated[$shop_id][$i]['ncommodity'] = 0;
                        $report_consolidated[$shop_id][$i]['ncommodityValue'] = 0;
                    }

                    if (isset($resultArrayFNV[$shop_id][$nextIndex])) {
                        $report_consolidated[$shop_id][$i]['fnv'] = $resultArrayFNV[$shop_id][$nextIndex];
                        $report_consolidated[$shop_id][$i]['fnvValue'] = $this->CommonHelper->inLakhs($resultArrayFNV[$shop_id][$nextIndex]);
                    } else {
                        $report_consolidated[$shop_id][$i]['fnv'] = 0;
                        $report_consolidated[$shop_id][$i]['fnvValue'] = 0;
                    }
                }
            }
        }

        $saleMonthShopGraph  = json_encode($report_consolidated);
        $this->view->saleMonthShopGraph  = $saleMonthShopGraph;
        $this->view->shopName  = $shopName;
        $this->view->reportConsolidated  = $report_consolidated;
        $this->view->month  = $month;
        $this->view->year  = $year;
        $this->view->graphType  = $graphType;
        $this->view->shopId  = $shopId;
    }

    public function returnFranchiseAction()
    {
        $params           = $this->dispatcher->getParams();
        $year             = isset($params[0]) ? $params[0] : '';
        $graphType        = isset($params[1]) ? $params[1] : '';
        $shopId           = isset($params[2]) ? $params[2] : '';
        if ($year == '') {
            $year = date('Y');
        }

        if ($graphType == '') {
            $graphType = 'consol';
        }

        if ($shopId == '') {
            $shopId = 1;
        }

        $this->assets
    ->collection('footer')
    ->addJs('js/amcharts/amcharts.js')
    ->addJs('js/amcharts/xy.js')
    ->addJs('js/amcharts/serial.js')
    ->addJs('js/amcharts/themes/light.js')
    ->addJs('js/graphSales.js')
    ->addJs('js/custom.js');

        $this->assets
    ->collection('header')
    ->addCss('css/reportCenter.css')
    ->addCss('css/custom.css');
        $this->view->access = $this->access[0];
        $this->view->leftMenu  = 'Return';
        $month = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
        $franchise_model = new Franchises;
        $get_franchise = $franchise_model->getFranchisesMap();
        $get_franchise = $franchise_model->getFranchisesDrop();

        $shopName = array();
        $reportId = array();

        foreach ($get_franchise as $key => $franchise) {
            $shop_id = $franchise['franchise_id'];
            $report_id = $franchise['franchise_report_id'];
            $shop_name = $franchise['franchise_name'];
            if ($shop_id != '-1') {
                $shopName[$shop_id] = $shop_name;
                $reportId[$shop_id] = $report_id;
            }
        }


        $sales_model = new SalesReport;
        $resultArray = array();

        foreach ($get_franchise as $key => $franchise) {
            $shop_id = $franchise['franchise_id'];
            if ($shop_id != '-1') {
                $internal_commodity = $sales_model->returnSalesCommodity($reportId[$shop_id], $year);
                $internal_ncommodity = $sales_model->returnSalesNonCommodity($reportId[$shop_id], $year);
                $internal_fnv = $sales_model->returnSalesFNV($reportId[$shop_id], $year);

                $resultArrayCommodity[$shop_id] = $internal_commodity;
                $resultArrayNCommodity[$shop_id] = $internal_ncommodity;
                $resultArrayFNV[$shop_id] = $internal_fnv;
            }
        }
        $report_consolidated = array();
        foreach ($get_franchise as $key => $franchise) {
            $shop_id = $franchise['franchise_id'];
            if ($shop_id != '-1') {
                for ($i=0;$i<12;$i++) {
                    $report_consolidated[$shop_id][$i]['month'] = $month[$i];
                    $nextIndex = $i + 1;
                    if (isset($resultArrayCommodity[$shop_id][$nextIndex])) {
                        $report_consolidated[$shop_id][$i]['commodity'] = $resultArrayCommodity[$shop_id][$nextIndex];
                        $report_consolidated[$shop_id][$i]['commodityValue'] = $this->CommonHelper->inLakhs($resultArrayCommodity[$shop_id][$nextIndex]);
                    } else {
                        $report_consolidated[$shop_id][$i]['commodity'] = 0;
                        $report_consolidated[$shop_id][$i]['commodityValue'] = 0;
                    }

                    if (isset($resultArrayNCommodity[$shop_id][$nextIndex])) {
                        $report_consolidated[$shop_id][$i]['ncommodity'] = $resultArrayNCommodity[$shop_id][$nextIndex];
                        $report_consolidated[$shop_id][$i]['ncommodityValue'] = $this->CommonHelper->inLakhs($resultArrayNCommodity[$shop_id][$nextIndex]);
                    } else {
                        $report_consolidated[$shop_id][$i]['ncommodity'] = 0;
                        $report_consolidated[$shop_id][$i]['ncommodityValue'] = 0;
                    }

                    if (isset($resultArrayFNV[$shop_id][$nextIndex])) {
                        $report_consolidated[$shop_id][$i]['fnv'] = $resultArrayFNV[$shop_id][$nextIndex];
                        $report_consolidated[$shop_id][$i]['fnvValue'] = $this->CommonHelper->inLakhs($resultArrayFNV[$shop_id][$nextIndex]);
                    } else {
                        $report_consolidated[$shop_id][$i]['fnv'] = 0;
                        $report_consolidated[$shop_id][$i]['fnvValue'] = 0;
                    }
                }
            }
        }

        $saleMonthShopGraph  = json_encode($report_consolidated);
        $this->view->returnMonthShopGraph  = $saleMonthShopGraph;
        $this->view->shopName  = $shopName;
        $this->view->reportConsolidated  = $report_consolidated;
        $this->view->month  = $month;
        $this->view->year  = $year;
        $this->view->graphType  = $graphType;
        $this->view->shopId  = $shopId;
    }

    /*public function ircAction()
    {
        $params           = $this->dispatcher->getParams();
        $year             = isset($params[0]) ? $params[0] : '';
        $graphType        = isset($params[1]) ? $params[1] : '';
        if ($year == '') {
            $year = date('Y');
        }

        if ($graphType == '') {
            $graphType = 'consol';
        }
        $this->assets
    ->collection('footer')
    ->addJs('js/amcharts/amcharts.js')
    ->addJs('js/amcharts/xy.js')
    ->addJs('js/amcharts/serial.js')
    ->addJs('js/amcharts/themes/light.js')
    ->addJs('js/graphSales.js')
    ->addJs('js/custom.js');

        $this->assets
    ->collection('header')
    ->addCss('css/reportCenter.css')
    ->addCss('css/custom.css');

        $this->view->leftMenu  = 'IRC';
        $this->view->access = $this->access[0];
        $sales_model = new SalesReport;
        $franchise_model = new Franchises;
        $get_franchise_total = $franchise_model->getTotalShops();
        $internal_consolidated = $sales_model->internalSalesConsolidated($year);
        $return_consolidated = $sales_model->returnSalesConsolidated($year);
        $month = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
        $shopName = array('1'=>'Devi','2'=>'Shobha','3'=>'Hybrid','4'=>'Ezey','5'=>'Kama','6'=>'VRS','7'=>'Hamana');

        $report_consolidated = array();
        for ($i=0;$i<12;$i++) {
            $report_consolidated[$i]['month'] = $month[$i];
            $nextIndex = $i + 1;
            if (isset($internal_consolidated[$nextIndex])) {
                $report_consolidated[$i]['saleAmount'] = $internal_consolidated[$nextIndex];
                $report_consolidated[$i]['saleAmountValue'] = $this->CommonHelper->inLakhs($internal_consolidated[$nextIndex]);
            } else {
                $report_consolidated[$i]['saleAmount'] = 0;
                $report_consolidated[$i]['saleAmountValue'] = 0;
            }
            if (isset($return_consolidated[$nextIndex])) {
                $report_consolidated[$i]['returnAmount'] = $return_consolidated[$nextIndex];
                $report_consolidated[$i]['returnAmountValue'] = $this->CommonHelper->inLakhs($return_consolidated[$nextIndex]);
            } else {
                $report_consolidated[$i]['returnAmount'] = 0;
                $report_consolidated[$i]['returnAmountValue'] = 0;
            }
        }
        $ircGraph  = json_encode($report_consolidated);
        $this->view->ircGraph  = $ircGraph;
        $this->view->reportConsolidated  = $report_consolidated;
        $this->view->shopName  = $shopName;
        $this->view->month  = $month;
        $this->view->shopTotal  = $get_franchise_total;
        $this->view->year  = $year;
        $this->view->graphType  = $graphType;
    }*/

    public function ircAction()
    {
        $params           = $this->dispatcher->getParams();
        $year             = isset($params[0]) ? $params[0] : '';
        $graphType        = isset($params[1]) ? $params[1] : '';
        if ($year == '') {
            $year = date('Y');
        }

        if ($graphType == '') {
            $graphType = 'consol';
        }
        $this->assets
    ->collection('footer')
    ->addJs('js/amcharts/amcharts.js')
    ->addJs('js/amcharts/xy.js')
    ->addJs('js/amcharts/serial.js')
    ->addJs('js/amcharts/themes/light.js')
    ->addJs('js/graphSales.js')
    ->addJs('js/custom.js');

        $this->assets
    ->collection('header')
    ->addCss('css/reportCenter.css')
    ->addCss('css/custom.css');

        $this->view->leftMenu  = 'IRC';
        $this->view->access = $this->access[0];
        $sales_model = new SalesReport;
        $franchise_model = new Franchises;
        $get_franchise_total = $franchise_model->getTotalShops();
        $internal_commodity = $sales_model->internalSalesCommodity('', $year);
        $internal_ncommodity = $sales_model->internalSalesNonCommodity('', $year);
        $internal_fnv = $sales_model->internalSalesFNV('', $year);


        $internal_return_commodity = $sales_model->returnSalesCommodity('', $year);
        $internal_return_ncommodity = $sales_model->returnSalesNonCommodity('', $year);
        $internal_return_fnv = $sales_model->returnSalesFNV('', $year);


        $month = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
        $shopName = array('1'=>'Devi','2'=>'Shobha','3'=>'Hybrid','4'=>'Ezey','5'=>'Kama','6'=>'VRS','7'=>'Hamana');

        $sales_commodity = 0;
        $sales_ncommodity = 0;
        $sales_fnv = 0;
        $sales_return_commodity = 0;
        $sales_return_ncommodity = 0;
        $sales_return_fnv = 0;

        $report_consolidated = array();
        for ($i=0;$i<12;$i++) {
            $report_consolidated[$i]['month'] = $month[$i];
            $nextIndex = $i + 1;
            $sales_commodity = isset($internal_commodity[$nextIndex]) ? $internal_commodity[$nextIndex] : 0;
            $sales_ncommodity = isset($internal_ncommodity[$nextIndex]) ? $internal_ncommodity[$nextIndex] : 0;
            $sales_fnv = isset($internal_fnv[$nextIndex]) ? $internal_fnv[$nextIndex] : 0;

            $sales_return_commodity = isset($internal_return_commodity[$nextIndex]) ? $internal_return_commodity[$nextIndex] : 0;
            $sales_return_ncommodity = isset($internal_return_ncommodity[$nextIndex]) ? $internal_return_ncommodity[$nextIndex] : 0;
            $sales_return_fnv = isset($internal_return_fnv[$nextIndex]) ? $internal_return_fnv[$nextIndex] : 0;

            $report_consolidated[$i]['saleAmount'] = $sales_commodity + $sales_ncommodity + $sales_fnv;
            $report_consolidated[$i]['saleAmountValue'] = $this->CommonHelper->inLakhs($report_consolidated[$i]['saleAmount']);

            $report_consolidated[$i]['returnAmount'] = $sales_return_commodity + $sales_return_ncommodity + $sales_return_fnv;
            $report_consolidated[$i]['returnAmountValue'] = $this->CommonHelper->inLakhs($report_consolidated[$i]['returnAmount']);
        }

        /*echo "<pre>";
        print_r($report_consolidated);
        echo "</pre>";
        exit();*/

        $ircGraph  = json_encode($report_consolidated);
        $this->view->ircGraph  = $ircGraph;
        $this->view->reportConsolidated  = $report_consolidated;
        $this->view->shopName  = $shopName;
        $this->view->month  = $month;
        $this->view->shopTotal  = $get_franchise_total;
        $this->view->year  = $year;
        $this->view->graphType  = $graphType;
    }

    /*public function irsAction()
    {
        $params           = $this->dispatcher->getParams();
        $year             = isset($params[0]) ? $params[0] : '';
        $graphType        = isset($params[1]) ? $params[1] : '';
        if ($year == '') {
            $year = date('Y');
        }

        if ($graphType == '') {
            $graphType = 'consol';
        }
        $this->assets
    ->collection('footer')
    ->addJs('js/amcharts/amcharts.js')
    ->addJs('js/amcharts/xy.js')
    ->addJs('js/amcharts/serial.js')
    ->addJs('js/amcharts/themes/light.js')
    ->addJs('js/graphSales.js')
    ->addJs('js/custom.js');

        $this->assets
    ->collection('header')
    ->addCss('css/reportCenter.css')
    ->addCss('css/custom.css');
        $this->view->access = $this->access[0];
        $this->view->leftMenu  = 'IRC';
        $month = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
        $franchise_model = new Franchises;
        $get_franchise = $franchise_model->getFranchisesMap();
        $get_franchise_total = $franchise_model->getTotalShops();

        $get_franchise = $franchise_model->getFranchisesDrop();

        $shopName = array();
        $reportId = array();

        foreach ($get_franchise as $key => $franchise) {
            $shop_id = $franchise['franchise_id'];
            $report_id = $franchise['franchise_report_id'];
            $shop_name = $franchise['franchise_name'];
            if ($shop_id != '-1') {
                $shopName[$shop_id] = $shop_name;
                $reportId[$shop_id] = $report_id;
            }
        }

        $sales_model = new SalesReport;
        $resultArraySales = array();
        $resultArrayReturns = array();

        foreach ($get_franchise as $key => $franchise) {
            $shop_id = $franchise['franchise_id'];
            if ($shop_id != '-1') {
                $internal_sales_shop = $sales_model->internalSalesShop($reportId[$shop_id]);
                $internal_return_shop = $sales_model->returnSalesShop($reportId[$shop_id]);
                $resultArraySales[$shop_id] = $internal_sales_shop;
                $resultArrayReturns[$shop_id] = $internal_return_shop;
            }
        }

        $report_consolidated = array();
        foreach ($get_franchise as $key => $franchise) {
            $shop_id = $franchise['franchise_id'];
            if ($shop_id != '-1') {
                for ($i=0;$i<12;$i++) {
                    $report_consolidated[$shop_id][$i]['month'] = $month[$i];
                    $nextIndex = $i + 1;
                    if (isset($resultArraySales[$shop_id][$nextIndex])) {
                        $report_consolidated[$shop_id][$i]['saleAmount'] = $resultArraySales[$shop_id][$nextIndex];
                        $report_consolidated[$shop_id][$i]['saleAmountValue'] = $this->CommonHelper->inLakhs($resultArraySales[$shop_id][$nextIndex]);
                    } else {
                        $report_consolidated[$shop_id][$i]['saleAmount'] = 0;
                        $report_consolidated[$shop_id][$i]['saleAmountValue'] = 0;
                    }
                    if (isset($resultArrayReturns[$shop_id][$nextIndex])) {
                        $report_consolidated[$shop_id][$i]['returnAmount'] = $resultArrayReturns[$shop_id][$nextIndex];
                        $report_consolidated[$shop_id][$i]['returnAmountValue'] = $this->CommonHelper->inLakhs($resultArrayReturns[$shop_id][$nextIndex]);
                    } else {
                        $report_consolidated[$shop_id][$i]['returnAmount'] = 0;
                        $report_consolidated[$shop_id][$i]['returnAmountValue'] = 0;
                    }
                }
            }
        }
        $ircShopGraph  = json_encode($report_consolidated);
        $this->view->ircShopGraph  = $ircShopGraph;
        $this->view->reportConsolidated  = $report_consolidated;
        $this->view->shopName  = $shopName;
        $this->view->month  = $month;
        $this->view->shopTotal  = $get_franchise_total;
    }*/

    public function irsAction()
    {
        $params           = $this->dispatcher->getParams();
        $year             = isset($params[0]) ? $params[0] : '';
        $graphType        = isset($params[1]) ? $params[1] : '';
        $shopId           = isset($params[2]) ? $params[2] : '';
        if ($year == '') {
            $year = date('Y');
        }

        if ($graphType == '') {
            $graphType = 'consol';
        }

        if ($shopId == '') {
            $shopId = 1;
        }
        $this->assets
    ->collection('footer')
    ->addJs('js/amcharts/amcharts.js')
    ->addJs('js/amcharts/xy.js')
    ->addJs('js/amcharts/serial.js')
    ->addJs('js/amcharts/themes/light.js')
    ->addJs('js/graphSales.js')
    ->addJs('js/custom.js');

        $this->assets
    ->collection('header')
    ->addCss('css/reportCenter.css')
    ->addCss('css/custom.css');
        $this->view->access = $this->access[0];
        $this->view->leftMenu  = 'IRC';
        $month = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
        $franchise_model = new Franchises;
        $get_franchise = $franchise_model->getFranchisesMap();
        $get_franchise_total = $franchise_model->getTotalShops();

        $get_franchise = $franchise_model->getFranchisesDrop();

        $shopName = array();
        $reportId = array();

        foreach ($get_franchise as $key => $franchise) {
            $shop_id = $franchise['franchise_id'];
            $report_id = $franchise['franchise_report_id'];
            $shop_name = $franchise['franchise_name'];
            if ($shop_id != '-1') {
                $shopName[$shop_id] = $shop_name;
                $reportId[$shop_id] = $report_id;
            }
        }

        $sales_model = new SalesReport;
        $resultArraySales = array();
        $resultArrayReturns = array();
        $internal_sales = array();
        $internal_sales_return = array();

        foreach ($get_franchise as $key => $franchise) {
            $shop_id = $franchise['franchise_id'];
            if ($shop_id != '-1') {
                $internal_commodity[$shop_id] = $sales_model->internalSalesCommodity($reportId[$shop_id], $year);
                $internal_ncommodity[$shop_id] = $sales_model->internalSalesNonCommodity($reportId[$shop_id], $year);
                $internal_fnv[$shop_id] = $sales_model->internalSalesFNV($reportId[$shop_id], $year);

                $internal_return_commodity[$shop_id] = $sales_model->returnSalesCommodity($reportId[$shop_id], $year);
                $internal_return_ncommodity[$shop_id] = $sales_model->returnSalesNonCommodity($reportId[$shop_id], $year);
                $internal_return_fnv[$shop_id] = $sales_model->returnSalesFNV($reportId[$shop_id], $year);
            }
        }

        $report_consolidated = array();
        foreach ($get_franchise as $key => $franchise) {
            $shop_id = $franchise['franchise_id'];
            if ($shop_id != '-1') {
                for ($i=0;$i<12;$i++) {
                    $report_consolidated[$shop_id][$i]['month'] = $month[$i];
                    $nextIndex = $i + 1;

                    $sales_commodity = isset($internal_commodity[$shop_id][$nextIndex]) ? $internal_commodity[$shop_id][$nextIndex] : 0;
                    $sales_ncommodity = isset($internal_ncommodity[$shop_id][$nextIndex]) ? $internal_ncommodity[$shop_id][$nextIndex] : 0;
                    $sales_fnv = isset($internal_fnv[$shop_id][$nextIndex]) ? $internal_fnv[$shop_id][$nextIndex] : 0;

                    $sales_return_commodity = isset($internal_return_commodity[$shop_id][$nextIndex]) ? $internal_return_commodity[$shop_id][$nextIndex] : 0;
                    $sales_return_ncommodity = isset($internal_return_ncommodity[$shop_id][$nextIndex]) ? $internal_return_ncommodity[$shop_id][$nextIndex] : 0;
                    $sales_return_fnv = isset($internal_return_fnv[$shop_id][$nextIndex]) ? $internal_return_fnv[$shop_id][$nextIndex] : 0;

                    $report_consolidated[$shop_id][$i]['saleAmount'] = $sales_commodity + $sales_ncommodity + $sales_fnv;
                    $report_consolidated[$shop_id][$i]['saleAmountValue'] = $this->CommonHelper->inLakhs($report_consolidated[$shop_id][$i]['saleAmount']);

                    $report_consolidated[$shop_id][$i]['returnAmount'] = $sales_return_commodity + $sales_return_ncommodity + $sales_return_fnv;
                    $report_consolidated[$shop_id][$i]['returnAmountValue'] = $this->CommonHelper->inLakhs($report_consolidated[$shop_id][$i]['returnAmount']);
                }
            }
        }

        $ircShopGraph  = json_encode($report_consolidated);
        $this->view->ircShopGraph  = $ircShopGraph;
        $this->view->reportConsolidated  = $report_consolidated;
        $this->view->shopName  = $shopName;
        $this->view->month  = $month;
        $this->view->year  = $year;
        $this->view->graphType  = $graphType;
        $this->view->shopId  = $shopId;
        $this->view->shopTotal  = $get_franchise_total;
    }

    public function computeAction()
    {
        $this->view->disable();
        $sessionData              = $this->session->get("authUser");
        $user_id = $sessionData['user_id'];

        $product_model = new ProductsMaster;
        $get_temp_data = $product_model->getSalesTemp();

        $iCount = 0;
        $rCount = 0;
        $insertInternalCount = 0;
        $insertReturnCount = 0;
        $insertInternalUpdateCount = 0;
        $insertReturnUpdateCount = 0;
        $resultArrayInternal = array();
        $resultArrayReturn = array();

        foreach ($get_temp_data as $key => $val) {
            $bill_number = $val['BillNumber'];
            $bill_date = $val['BillDate'];
            $bill_time = $val['BillTime'];
            $bill_cust_code = $val['CustomerCode'];
            $bill_alt_code = $val['AltCode'];
            $bill_quantity = $val['Quantity'];
            $bill_gross = $val['Gross'];
            $bill_vat = $val['VAT'];
            $bill_tot_vat = $val['TotalVAT'];
            $bill_tot_amount = $val['TotalAmount'];
            $bill_product_id = $val['ProductCode'];
            $bill_mrp = $val['MRP'];
            $bill_type = $val['BillSeries'];
            $bill_brand = $val['BrandName'];
            $bill_department = $val['Department'];


            if ($bill_number != '') {
                if ($bill_type == 'IS') {
                    $iDate = str_replace('/', '-', $bill_date);
                    $iDate = date('Y-m-d', strtotime($iDate));
                    $duplicate_check = $product_model->salesInternalDuplicate($iDate, $bill_time, $bill_number, $bill_cust_code, $bill_alt_code, $bill_gross, $bill_vat, $bill_tot_vat, $bill_tot_amount, $bill_product_id, $bill_quantity, $bill_mrp);
                    if ($duplicate_check) {
                        $resultArrayInternal['update']['data'][$iCount]['bill_date'] = $iDate;
                        $resultArrayInternal['update']['data'][$iCount]['bill_time'] = trim($bill_time);
                        $resultArrayInternal['update']['data'][$iCount]['bill_number'] = trim($bill_number);
                        $resultArrayInternal['update']['data'][$iCount]['shop_name'] = trim($bill_cust_code);
                        $resultArrayInternal['update']['data'][$iCount]['shop_alt_name'] = trim($bill_alt_code);
                        $resultArrayInternal['update']['data'][$iCount]['bill_gross'] = trim($bill_gross);
                        $resultArrayInternal['update']['data'][$iCount]['bill_vat'] = trim($bill_vat);
                        $resultArrayInternal['update']['data'][$iCount]['bill_tot_vat'] = trim($bill_tot_vat);
                        $resultArrayInternal['update']['data'][$iCount]['bill_tot_amt'] = trim($bill_tot_amount);
                        $resultArrayInternal['update']['data'][$iCount]['product_id'] = trim($bill_product_id);
                        $resultArrayInternal['update']['data'][$iCount]['bill_quantity'] = trim($bill_quantity);
                        $resultArrayInternal['update']['data'][$iCount]['bill_mrp'] =  trim($bill_mrp);
                        $resultArrayInternal['update']['data'][$iCount]['bill_brand'] =  trim($bill_brand);
                        $resultArrayInternal['update']['data'][$iCount]['bill_department'] =  trim($bill_department);
                        $resultArrayInternal['update']['data'][$iCount]['upload_time'] =  date('Y-m-d H:i:s');
                        $resultArrayInternal['update']['where'][$iCount]['id'] = $duplicate_check;
                    } else {
                        $resultArrayInternal['insert']['data'][$iCount]['bill_date'] = $iDate;
                        $resultArrayInternal['insert']['data'][$iCount]['bill_time'] = trim($bill_time);
                        $resultArrayInternal['insert']['data'][$iCount]['bill_number'] = trim($bill_number);
                        $resultArrayInternal['insert']['data'][$iCount]['shop_name'] = trim($bill_cust_code);
                        $resultArrayInternal['insert']['data'][$iCount]['shop_alt_name'] = trim($bill_alt_code);
                        $resultArrayInternal['insert']['data'][$iCount]['bill_gross'] = trim($bill_gross);
                        $resultArrayInternal['insert']['data'][$iCount]['bill_vat'] = trim($bill_vat);
                        $resultArrayInternal['insert']['data'][$iCount]['bill_tot_vat'] = trim($bill_tot_vat);
                        $resultArrayInternal['insert']['data'][$iCount]['bill_tot_amt'] = trim($bill_tot_amount);
                        $resultArrayInternal['insert']['data'][$iCount]['product_id'] = trim($bill_product_id);
                        $resultArrayInternal['insert']['data'][$iCount]['bill_quantity'] = trim($bill_quantity);
                        $resultArrayInternal['insert']['data'][$iCount]['bill_mrp'] =  trim($bill_mrp);
                        $resultArrayInternal['insert']['data'][$iCount]['bill_brand'] =  trim($bill_brand);
                        $resultArrayInternal['insert']['data'][$iCount]['bill_department'] =  trim($bill_department);
                        $resultArrayInternal['insert']['data'][$iCount]['upload_time'] =  date('Y-m-d H:i:s');
                    }
                    $iCount++;
                } elseif ($bill_type == 'ISR') {
                    $iDate = str_replace('/', '-', $bill_date);
                    $iDate = date('Y-m-d', strtotime($iDate));
                    $duplicate_check = $product_model->salesReturnDuplicate($iDate, $bill_time, $bill_number, $bill_cust_code, $bill_alt_code, $bill_gross, $bill_vat, $bill_tot_vat, $bill_tot_amount, $bill_product_id, $bill_quantity, $bill_mrp);
                    if ($duplicate_check) {
                        $resultArrayReturn['update']['data'][$rCount]['bill_date'] = $iDate;
                        $resultArrayReturn['update']['data'][$rCount]['bill_time'] = trim($bill_time);
                        $resultArrayReturn['update']['data'][$rCount]['bill_number'] = trim($bill_number);
                        $resultArrayReturn['update']['data'][$rCount]['shop_name'] = trim($bill_cust_code);
                        $resultArrayReturn['update']['data'][$rCount]['shop_alt_name'] = trim($bill_alt_code);
                        $resultArrayReturn['update']['data'][$rCount]['bill_gross'] = trim($bill_gross);
                        $resultArrayReturn['update']['data'][$rCount]['bill_vat'] = trim($bill_vat);
                        $resultArrayReturn['update']['data'][$rCount]['bill_tot_vat'] = trim($bill_tot_vat);
                        $resultArrayReturn['update']['data'][$rCount]['bill_tot_amt'] = trim($bill_tot_amount);
                        $resultArrayReturn['update']['data'][$rCount]['product_id'] = trim($bill_product_id);
                        $resultArrayReturn['update']['data'][$rCount]['bill_quantity'] = trim($bill_quantity);
                        $resultArrayReturn['update']['data'][$rCount]['bill_mrp'] =  trim($bill_mrp);
                        $resultArrayReturn['update']['data'][$rCount]['bill_brand'] =  trim($bill_brand);
                        $resultArrayReturn['update']['data'][$rCount]['bill_department'] =  trim($bill_department);
                        $resultArrayReturn['update']['data'][$rCount]['upload_time'] =  date('Y-m-d H:i:s');
                        $resultArrayReturn['update']['where'][$rCount]['id'] = $duplicate_check;
                    } else {
                        $resultArrayReturn['insert']['data'][$rCount]['bill_date'] = $iDate;
                        $resultArrayReturn['insert']['data'][$rCount]['bill_time'] = trim($bill_time);
                        $resultArrayReturn['insert']['data'][$rCount]['bill_number'] = trim($bill_number);
                        $resultArrayReturn['insert']['data'][$rCount]['shop_name'] = trim($bill_cust_code);
                        $resultArrayReturn['insert']['data'][$rCount]['shop_alt_name'] = trim($bill_alt_code);
                        $resultArrayReturn['insert']['data'][$rCount]['bill_gross'] = trim($bill_gross);
                        $resultArrayReturn['insert']['data'][$rCount]['bill_vat'] = trim($bill_vat);
                        $resultArrayReturn['insert']['data'][$rCount]['bill_tot_vat'] = trim($bill_tot_vat);
                        $resultArrayReturn['insert']['data'][$rCount]['bill_tot_amt'] = trim($bill_tot_amount);
                        $resultArrayReturn['insert']['data'][$rCount]['product_id'] = trim($bill_product_id);
                        $resultArrayReturn['insert']['data'][$rCount]['bill_quantity'] = trim($bill_quantity);
                        $resultArrayReturn['insert']['data'][$rCount]['bill_mrp'] =  trim($bill_mrp);
                        $resultArrayReturn['insert']['data'][$rCount]['bill_brand'] =  trim($bill_brand);
                        $resultArrayReturn['insert']['data'][$rCount]['bill_department'] =  trim($bill_department);
                        $resultArrayReturn['insert']['data'][$rCount]['upload_time'] =  date('Y-m-d H:i:s');
                    }
                    $rCount++;
                }
            }
        }


        if (isset($resultArrayInternal['insert']['data'])) {
            $insertInternalCount = count($resultArrayInternal['insert']['data']);
        }

        if (isset($resultArrayReturn['insert'])) {
            $insertReturnCount = count($resultArrayReturn['insert']['data']);
        }

        if (isset($resultArrayInternal['update'])) {
            $insertInternalUpdateCount = count($resultArrayInternal['update']['data']);
        }

        if (isset($resultArrayReturn['update'])) {
            $insertReturnUpdateCount = count($resultArrayReturn['update']['data']);
        }



        if ($insertInternalCount > 0) {
            $batch = new CustomInsert('nxtk_internal_sales');
            $batch->columns = array('bill_date','bill_time','bill_number','shop_name','shop_alt_name','bill_gross','bill_vat','bill_tot_vat','bill_tot_amt','product_id','bill_quantity','bill_mrp','bill_brand','bill_department','upload_time');
            $batch->data = $resultArrayInternal['insert']['data'];
            $batch->insert();
        }
        if ($insertReturnCount > 0) {
            $batch = new CustomInsert('nxtk_internal_sales_return');
            $batch->columns = array('bill_date','bill_time','bill_number','shop_name','shop_alt_name','bill_gross','bill_vat','bill_tot_vat','bill_tot_amt','product_id','bill_quantity','bill_mrp','bill_brand','bill_department','upload_time');
            $batch->data = $resultArrayReturn['insert']['data'];
            $batch->insert();
        }
        if ($insertInternalUpdateCount > 0) {
            //$update_data = $product_model->updateSalesData('nxtk_internal_sales', $resultArrayInternal['update']['data'], $resultArrayInternal['update']['where']);
        }

        if ($insertReturnUpdateCount > 0) {
            //$update_data = $product_model->updateSalesData('nxtk_internal_sales_return', $resultArrayReturn['update']['data'], $resultArrayReturn['update']['where']);
        }
        $truncate = $product_model->truncateTable('nxtk_internal_temp');

        $insertLog = array();
        $insertLog['user_id'] = $user_id;
        $insertLog['activity'] = 'Transfer Data From Temp';
        $insertLog['data'] = 'Compute';
        $insertLog['status'] = 'Success';
        $insertLog['date'] = date('Y-m-d H:i:s');
        $insert_log = $product_model->insertLog($insertLog);
        echo 'Success';
    }


    public function importAction()
    {
        $this->assets
    ->collection('footer')
    ->addJs('js/jquery.filer/js/jquery.filer.min.js')
    ->addJs('js/custom.js');

        $this->assets
    ->collection('header')
    ->addCss('js/jquery.filer/css/jquery.filer.css')
    ->addCss('css/reportCenter.css')
    ->addCss('css/custom.css');
        $sessionData              = $this->session->get("authUser");
        $user_id = $sessionData['user_id'];
        $product_model = new ProductsMaster;
        $this->view->access = $this->access[0];
        $this->view->leftMenu  = 'Import';
        $formCode = \Phalcon\Text::random(\Phalcon\Text::RANDOM_NUMERIC);
        $this->view->formCode = $formCode;
        $this->view->uploadStatus = 0;
        $params             = $this->dispatcher->getParams();
        $status             = isset($params[0]) ? $params[0] : '';
        $uploadStatus = 0;
        if ($status == 3) {
            $this->view->computeStatus = 1;
        } else {
            $this->view->computeStatus = 0;
        }
        if ($this->request->isPost()) {
            $sessionData = $this->session->get("authUser");
            $userId     = $sessionData['user_id'];
            $formCode = $this->request->getPost('formCode');

            if ($this->request->hasFiles() == true) {
                $tempDirectoryAssigned = 'temp_' . $formCode. '/';
                $tempDirectory = 'uploads/sales/' . $tempDirectoryAssigned;
                $createProductDirectory = $this->CommonHelper->createDirectory($tempDirectory);
                $newFile = '';
                foreach ($this->request->getUploadedFiles() as $file) {
                    if ($file->getExtension() == 'xlsx') {
                        $fileUploadStatus = $file->moveTo($tempDirectory . $file->getName());
                        $newFile = $file->getName();
                        $uploadStatus = 1;
                    } else {
                        $uploadStatus = 2;
                        $this->view->uploadStatus = 2;
                    }
                }
                $logStatus = '';
                if ($uploadStatus == 1) {
                    $filePath = $tempDirectory.$newFile;
                    $importPath = $tempDirectory.$newFile;

                    $truncate = $product_model->truncateTable('nxtk_internal_temp');
                    $baseDir = $this->config['application']['baseDir'];
                    $importPath = $baseDir.$importPath;
                    /*$checkFormat = $this->salesExcelFormat($importPath);
                    if ($checkFormat) {*/
                        //$insert_data = $product_model->localInsert('nxtk_internal_temp', $importPath);
                        $insert_data = $this->readSalesExcel($importPath);
                    if ($insert_data) {
                        $deleteDirectory = $this->CommonHelper->deleteTempDirectoryPath($tempDirectory);
                        $this->view->uploadStatus = 1;
                        $logStatus = 'Uploaded';
                    } else {
                        $this->view->uploadStatus = 3;
                        $logStatus = 'Data Mismatch';
                    }

                    /*} else {

                    }*/

                    $insertLog = array();
                    $insertLog['user_id'] = $user_id;
                    $insertLog['activity'] = 'File Upload';
                    $insertLog['data'] = $newFile;
                    $insertLog['status'] = $logStatus;
                    $insertLog['date'] = date('Y-m-d H:i:s');
                    $insert_log = $product_model->insertLog($insertLog);
                }
            }
        }
        $get_temp_data = $product_model->getSalesTemp();
        $countTemp = count($get_temp_data);
        //exit();
        if ($countTemp > 0) {
            $this->view->yetStatus = 1;
        } else {
            $this->view->yetStatus = 0;
        }
    }

    public function salesExcelFormat($importPath)
    {
        $csv = array_map('str_getcsv', file($importPath));
        $count = count($csv[0]);

        if ($count == 98) {
            return true;
        } else {
            return false;
        }
    }

    public function importPaymentsAction()
    {
        $this->assets
    ->collection('footer')
    ->addJs('js/jquery.filer/js/jquery.filer.min.js')
    ->addJs('js/custom.js');

        $this->assets
    ->collection('header')
    ->addCss('js/jquery.filer/css/jquery.filer.css')
    ->addCss('css/reportCenter.css')
    ->addCss('css/custom.css');
        $this->view->access = $this->access[0];
        $this->view->leftMenu  = 'Payment';
        $formCode = \Phalcon\Text::random(\Phalcon\Text::RANDOM_NUMERIC);
        $this->view->formCode = $formCode;
        $this->view->uploadStatus = 0;
        $params             = $this->dispatcher->getParams();
        $status             = isset($params[0]) ? $params[0] : '';
        $uploadStatus = 0;
        if ($status == 3) {
            $this->view->computeStatus = 1;
        } else {
            $this->view->computeStatus = 0;
        }
        if ($this->request->isPost()) {
            $sessionData = $this->session->get("authUser");
            $userId     = $sessionData['user_id'];
            $formCode = $this->request->getPost('formCode');

            if ($this->request->hasFiles() == true) {
                $tempDirectoryAssigned = 'temp_' . $formCode. '/';
                $tempDirectory = 'uploads/sales/' . $tempDirectoryAssigned;
                $createProductDirectory = $this->CommonHelper->createDirectory($tempDirectory);
                $newFile = '';
                foreach ($this->request->getUploadedFiles() as $file) {
                    if ($file->getExtension() == 'xlsx') {
                        $fileUploadStatus = $file->moveTo($tempDirectory . $file->getName());
                        $newFile = $file->getName();
                        $uploadStatus = 1;
                    } else {
                        $uploadStatus = 2;
                        $this->view->uploadStatus = 2;
                    }
                }
                $logStatus = '';
                if ($uploadStatus == 1) {
                    $filePath = $tempDirectory.$newFile;
                    $baseDir = $this->config['application']['baseDir'];
                    $importPath = $baseDir.$tempDirectory.$newFile;

                    $excelData = $this->readPaymentExcel($importPath);
                    $countData = count($excelData);
                    $product_model = new ProductsMaster;
                    if ($countData > 0) {
                        /*$batch = new CustomInsert('nxtk_internal_payment');
                        $batch->columns = array('pay_date','shop_name','shop_id','trans_type','trans_code','trans_date','trans_amt');
                        $batch->data = $excelData;
                        $batch->insert();*/

                        $insert_payment = $product_model->insertPayment($excelData);
                        $this->view->uploadStatus = 1;
                        $logStatus = 'Success';
                    } else {
                        $this->view->uploadStatus = 3;
                        $logStatus = 'Mismatch Data';
                    }

                    $insertLog = array();
                    $insertLog['user_id'] = $user_id;
                    $insertLog['activity'] = 'Payment Import';
                    $insertLog['data'] = $newFile;
                    $insertLog['status'] = $logStatus;
                    $insertLog['date'] = date('Y-m-d H:i:s');
                    $insert_log = $product_model->insertLog($insertLog);
                }
            }
        }
    }

    public function readPaymentExcel($filePath)
    {
        if (class_exists('PHPExcel')) {
            $reader = ReaderFactory::create(Type::XLSX); // for XLSX files
            $reader->open($filePath);
            $resultArray = array();
            $franchise_model = new Franchises;
            $get_franchise = $franchise_model->getFranchisesDrop();

            $shopName = array();
            $reportId = array();
            $location = array();

            foreach ($get_franchise as $key => $franchise) {
                $shop_id = $franchise['franchise_id'];
                $report_id = $franchise['franchise_report_id'];
                $shop_name = $franchise['franchise_name'];
                $shop_location = $franchise['franchise_ws_location'];
                if ($shop_id != '-1') {
                    $shopName[$shop_id] = $shop_name;
                    $reportId[$shop_id] = $report_id;
                    $location[$shop_location] = $shop_id;
                }
            }

            $count = 0;
            $ecount = 0;
            $formatStatus = true;
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    if ($count >= 1) {
                        $countRow = count($row);

                        if ($countRow < 7 && $countRow > 7) {
                            $formatStatus = false;
                            break;
                        }
                        if (is_object($row[5])) {
                            $dateTemp = $row[5];
                            $dateTrans =  $dateTemp->format('Y-m-d');
                        } else {
                            $dateTrans = $row[5];
                        }

                        if (is_object($row[0])) {
                            $dateTemp = $row[0];
                            $dateInput =  $dateTemp->format('Y-m-d');
                        } else {
                            $dateInput = $row[0];
                        }
                        $shop = $row[1];
                        $shop_loc = $row[3];
                        if (isset($location[$shop_loc])) {
                            $resultArray[$ecount]['date'] = $dateInput;
                            $resultArray[$ecount]['shop'] = $row[1];
                            $resultArray[$ecount]['shop_id'] = $location[$shop_loc];
                            $resultArray[$ecount]['trans_type'] = $row[2];
                            $resultArray[$ecount]['trans_code'] = $row[4];
                            $resultArray[$ecount]['trans_date'] = $dateTrans;
                            $resultArray[$ecount]['trans_amt'] = $row[6];
                            $ecount++;
                        }
                    }
                    $count++;
                }
            }
            if (!$formatStatus) {
                $resultArray = array();
                return $resultArray;
            } else {
                return $resultArray;
            }
        }
    }

    /*public function readSalesExcel($filePath)
    {
        $product_model = new ProductsMaster;

        $csv = array_map('str_getcsv', file($filePath));
        $dbFields = array('Region Name','Store Name','Alternate Store Code','StoreGST Number','Bill Date','Bill Time','Bill Number','Customer Name',
        'Customer Code','Customer Mobile','Quantity','Free Qty','FreeMRP Value','Tax Description','Base Value','Other Tax','Tax','TNGST Amount',
        'CustomerGST Number','Amount','Surcharge','CST','SGST','CGST','IGST','UTGST','Is Tax Inclusive','Tax Transaction Number','HSN Code',
        'Product Name','Brand Name','Supplier Ref Code','Category Code','Department','Sub Category','Product Code','EAN Code','Alternate Product Codes',
        'Batch','MRP','Product Level Disc','Product Level Disc Amount','Bill Level Disc Amount','Bill Level Disc','Total Disc Amount','Total Disc',
        'Other Discount Percentage','Expiry Date','Salesman Code','Salesman Name','Classification','Cenvat','Educess','Sheducess','Excise Duty Code',
        'Abatement','Reason','MRP Total','User Name','Serial Number Updated By','Serial Number Updated On','Unit Description','Other Discount',
        'Service Tax Amt','ST Cess Amount','ST Edu Cess Amount','Color','Gender','Size','Business Segment Name','Article Number','Division Name',
        'Marketing Division Name','Quarter Season Name','Price Point Name','Product Category Name','Sports Name','Patient Name','Class','Sub Class',
        'Cess Amt','UnitMRP','Toon Label','Bill Series','Pivot Product Code','Discount Budget Name','Supplier Name','Manufacturer Code','Marketer Code',
        'Rate','Order Date','Order Number','Ins Approval Code','Co Payer Percentage','Co Payer Amount','CA Address1','CA Address2','CA Address3');
        foreach ($csv as $key => $row) {
            if ($key > 0) {
                foreach ($dbFields as $key => $field) {
                    $insertArray[$field] = $row[$key];
                }
                $insert_temp = $product_model->insertTemp($insertArray);
            }
        }
    }*/

    public function readSalesExcel($filePath)
    {
        $product_model = new ProductsMaster;
        /*$dbFields = array('Region Name','Store Name','Alternate Store Code','StoreGST Number','Bill Date','Bill Time','Bill Number','Customer Name',
        'Customer Code','Customer Mobile','Quantity','Free Qty','FreeMRP Value','Tax Description','Base Value','Other Tax','Tax','TNGST Amount',
        'CustomerGST Number','Amount','Surcharge','CST','SGST','CGST','IGST','UTGST','Is Tax Inclusive','Tax Transaction Number','HSN Code',
        'Product Name','Brand Name','Supplier Ref Code','Category Code','Department','Sub Category','Product Code','EAN Code','Alternate Product Codes',
        'Batch','MRP','Product Level Disc','Product Level Disc Amount','Bill Level Disc Amount','Bill Level Disc','Total Disc Amount','Total Disc',
        'Other Discount Percentage','Expiry Date','Salesman Code','Salesman Name','Classification','Cenvat','Educess','Sheducess','Excise Duty Code',
        'Abatement','Reason','MRP Total','User Name','Serial Number Updated By','Serial Number Updated On','Unit Description','Other Discount',
        'Service Tax Amt','ST Cess Amount','ST Edu Cess Amount','Color','Gender','Size','Business Segment Name','Article Number','Division Name',
        'Marketing Division Name','Quarter Season Name','Price Point Name','Product Category Name','Sports Name','Patient Name','Class','Sub Class',
        'Cess Amt','UnitMRP','Toon Label','Bill Series','Pivot Product Code','Discount Budget Name','Supplier Name','Manufacturer Code','Marketer Code',
        'Rate','Order Date','Order Number','Ins Approval Code','Co Payer Percentage','Co Payer Amount','CA Address1','CA Address2','CA Address3');*/

        $dbFields = array(
          'Region Name','Store Name','Alternate Store Code','To Store Code','To Store Name','Alternate To Store Code','Bill Date','Bill Time','Bill Number',
          'Terminal Number','Customer Code','Customer Name','Customer Mobile','Brand Name','Supplier Ref Code','Category Code','Department','Sub Category',
          'Product Code','Product Name','EAN Code','Alternate Product Codes','Batch','MRP','Product Level Disc Amount','Bill Level Disc Amount','Bill Level Disc',
          'Total Disc Amount','Total Disc','Other Discount Percentage','Quantity','Unit Quantity','Free Qty','Tax','Other Tax','Gross','VAT','TotalVAT',
          'Total Amount','Expiry Date','Salesman Code','Salesman Name','Classification','Excise Duty Code','Abatement','Reason','MRP Total','User Name',
          'Unit Description','Other Discount','Service Tax Amt','ST Cess Amount','ST Edu Cess Amount','Color','Gender','Size','Business Segment Name',
          'Article Number','Division Name','Marketing Division Name','Quarter Season Name','Price Point Name','Product Category Name','Sports Name',
          'Patient Name','Attribute1','Attribute2','Attribute3','Attribute4','Attribute5','Sub Class','Surcharge','TNGST','CST','Cess Amt','UnitMRP',
          'Toon Label','Bill Series','Pivot Product Code','Discount Budget Name','Supplier Name','Manufacturer Code','Marketer Code','Rate','Order Date',
          'Order Number');

        if (class_exists('PHPExcel')) {
            $reader = ReaderFactory::create(Type::XLSX); // for XLSX files
          $reader->open($filePath);

            $resultArray = array();
            $count = 0;
            $ecount = 0;
            $formatStatus = true;
            $header = array();
            $result = array();
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    if ($count >= 2) {
                        $countRow = count($row);

                        if ($countRow < 86 || $countRow > 86) {
                            $formatStatus = false;
                            break;
                        }
                        if (is_object($row[4])) {
                            $dateTemp = $row[4];
                            $row[4] =  $dateTemp->format('Y-m-d');
                        }

                        if (is_object($row[39])) {
                            $dateTemp = $row[39];
                            $row[39] =  $dateTemp->format('Y-m-d');
                        }

                        if ($row[8] != '' && $row[35] != '0' && $row[38] != '0') {
                            foreach ($dbFields as $key => $field) {
                                $insertArray[$field] = $row[$key];
                            }
                            $insert_temp = $product_model->insertTemp($insertArray);
                        }
                    }
                    $count++;
                }
            }
            return $formatStatus;
        }
    }

    public function paymentAction()
    {
        $params           = $this->dispatcher->getParams();
        $year             = isset($params[0]) ? $params[0] : '';
        $graphType        = isset($params[1]) ? $params[1] : '';
        if ($year == '') {
            $year = date('Y');
        }

        if ($graphType == '') {
            $graphType = 'consol';
        }

        $this->assets
    ->collection('footer')
    ->addJs('js/amcharts/amcharts.js')
    ->addJs('js/amcharts/serial.js')
    ->addJs('js/amcharts/amstock.js')

    ->addJs('js/amcharts/themes/light.js')
    ->addJs('js/bootstrap-datepicker/js/bootstrap-datepicker.min.js')
    ->addJs('js/graphSales.js')

    ->addJs('js/custom.js');

        $this->assets
    ->collection('header')
    ->addCss('css/reportCenter.css')
    ->addCss('js/bootstrap-datepicker/css/bootstrap-datepicker.min.css')
    ->addCss('css/custom.css');
        $this->view->access = $this->access[0];
        $this->view->leftMenu  = 'PaymentMode';
        $sales_model = new SalesReport;
        $franchise_model = new Franchises;
        $get_payment_sales_info = $sales_model->salesPaymentDaily($year);

        $get_payment_return_info = $sales_model->salesReturnPaymentDaily($year);

        $get_payment_info = $sales_model->paymentDaily($year);

        $graph_info = array();
        $month = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
        $monthFull = array('January','February','March','April','May','June','July','August','September','October','November','December');

        $shopName = array('1'=>'Devi','2'=>'Shobha','3'=>'Hybrid','4'=>'Ezey','5'=>'Kama','6'=>'VRS','7'=>'Hamana');
        $tempAmount = 0;
        //$year = '2017';

        $count = 0;

        $currentMonth = date('n');
        $currentYear = date('Y');
        if ($year < $currentYear) {
            $currentMonth = 12;
        }
        foreach ($get_payment_sales_info as $mon => $monValue) {
            foreach ($monValue as $day => $dayValue) {
                $monMinus = $mon - 1;
                $dayMinus = $day - 1;
                $dateRaw = $day.'-'.$mon.'-'.$year;
                $date = date('Y-m-d', strtotime($dateRaw));
                /*if (isset($graph_info[$monMinus][$dayMinus]['amount'])) {
                    $graph_info[$monMinus][$dayMinus]['amount'] = 0;
                }*/
                if ($mon <= $currentMonth) {
                    $graph_info[$count]['amount'] = abs($get_payment_sales_info[$mon][$day] - ($get_payment_return_info[$mon][$day] + $get_payment_info[$mon][$day]) + $tempAmount);
                    $graph_info[$count]['Date'] = $date;
                    $graph_info[$count]['Day'] = $day;
                    if ($graph_info[$count]['amount'] == 0) {
                        $graph_info[$count]['amount'] = $tempAmount;
                        $graph_info[$count]['amountValue'] = $this->CommonHelper->inLakhs($tempAmount);
                    } else {
                        $graph_info[$count]['amountValue'] = $this->CommonHelper->inLakhs($graph_info[$count]['amount']);
                    }

                    $tempAmount = $graph_info[$count]['amount'];
                    $count++;
                }
            }
        }
        $tempAmount = 0;
        $graph_info_old = array();


        foreach ($get_payment_sales_info as $mon => $monValue) {
            foreach ($monValue as $day => $dayValue) {
                $monMinus = $mon - 1;
                $dayMinus = $day - 1;
                $graph_info_old[$monMinus][$dayMinus]['amount'] = abs($get_payment_sales_info[$mon][$day] - ($get_payment_return_info[$mon][$day] + $get_payment_info[$mon][$day]) + $tempAmount);
                if ($graph_info_old[$monMinus][$dayMinus]['amount'] == 0) {
                    $graph_info_old[$monMinus][$dayMinus]['amount'] = $tempAmount;
                    $graph_info_old[$monMinus][$dayMinus]['amountValue'] = $this->CommonHelper->inLakhs($tempAmount);
                } else {
                    $graph_info_old[$monMinus][$dayMinus]['amountValue'] = $this->CommonHelper->inLakhs($graph_info_old[$monMinus][$dayMinus]['amount']);
                }
                $graph_info_old[$monMinus][$dayMinus]['Day'] = $day;
                $tempAmount = $graph_info_old[$monMinus][$dayMinus]['amount'];
            }
        }

        $paymentDailyGraph  = json_encode($graph_info);
        $paymentDailyGraphOld  = json_encode($graph_info_old);
        $this->view->paymentDailyGraphJson  = $paymentDailyGraph;
        $this->view->paymentDailyGraphOldJson  = $paymentDailyGraphOld;
        $this->view->paymentDaily  = $graph_info;
        $this->view->month  = $month;
        $this->view->monthFull  = $monthFull;
        $this->view->shopName  = $shopName;
        $this->view->currentMonth  = date('m')-1;
        $this->view->year  = $year;
        $this->view->graphType  = $graphType;
        $this->view->shopTotal  = $get_franchise_total;
    }

    public function paymentShopAction()
    {
        $params           = $this->dispatcher->getParams();
        $year             = isset($params[0]) ? $params[0] : '';
        $graphType        = isset($params[1]) ? $params[1] : '';
        $shopId           = isset($params[2]) ? $params[2] : '';

        if ($year == '') {
            $year = date('Y');
        }

        if ($graphType == '') {
            $graphType = 'consol';
        }

        if ($shopId == '') {
            $shopId = 1;
        }

        $this->assets
  ->collection('footer')
  ->addJs('js/amcharts/amcharts.js')
  ->addJs('js/amcharts/serial.js')
  ->addJs('js/amcharts/amstock.js')

  ->addJs('js/amcharts/themes/light.js')
  ->addJs('js/graphSales.js')
  ->addJs('js/custom.js');

        $this->assets
  ->collection('header')
  ->addCss('css/reportCenter.css')
  ->addCss('css/custom.css');
        $this->view->access = $this->access[0];
        $this->view->leftMenu  = 'PaymentMode';
        $sales_model = new SalesReport;
        $franchise_model = new Franchises;

        $get_franchise = $franchise_model->getFranchisesDrop();

        //exit();
        //$shopName = array('1'=>'Devi','2'=>'Shobha','3'=>'Hybrid','4'=>'Ezey','5'=>'Kama','6'=>'VRS','7'=>'Hamana');
        //$reportId = array('1'=>'9','2'=>'8','3'=>'6','4'=>'10','5'=>'11','6'=>'12','7'=>'13');
        $shopName = array();
        $reportId = array();

        foreach ($get_franchise as $key => $franchise) {
            $shop_id = $franchise['franchise_id'];
            $report_id = $franchise['franchise_report_id'];
            $shop_name = $franchise['franchise_name'];
            if ($shop_id != '-1') {
                $shopName[$shop_id] = $shop_name;
                $reportId[$shop_id] = $report_id;
            }
        }


        $get_payment_sales_info = $sales_model->salesPaymentShopDaily($year);

        $get_payment_return_info = $sales_model->salesReturnPaymentShopDaily($year);

        $get_payment_info = $sales_model->paymentShop($year);

        $graph_info = array();
        $month = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
        $monthFull = array('January','February','March','April','May','June','July','August','September','October','November','December');


        $tempAmount = 0;

        $currentMonth = date('n');
        $currentYear = date('Y');
        if ($year < $currentYear) {
            $currentMonth = 12;
        }
        foreach ($shopName as $tempKey => $val) {
            $key = $reportId[$tempKey];
            $tempAmount = 0;
            $previousAmount = 0;
            $count = 0;
            for ($i=1;$i<=12;$i++) {
                $month = $i;
                //$year = date('Y');
                $days = date('t', strtotime('01-'.$month.'-'.$year));

                for ($da=1;$da<=$days;$da++) {
                    $mon = $month - 1;
                    $day = $da - 1;
                    $date = date('Y-m-d', strtotime($da.'-'.$month.'-'.$year));
                    if ($month <= $currentMonth) {
                        if (isset($get_payment_sales_info[$key][$i][$da]) || isset($get_payment_return_info[$key][$i][$da]) || isset($get_payment_info[$tempKey][$i][$da])) {
                            $sales_amount = isset($get_payment_sales_info[$key][$i][$da]) ? $get_payment_sales_info[$key][$i][$da] : 0;
                            $return_amount = isset($get_payment_return_info[$key][$i][$da]) ? $get_payment_return_info[$key][$i][$da] : 0;
                            $payment_amount = isset($get_payment_info[$tempKey][$i][$da]) ? $get_payment_info[$tempKey][$i][$da] : 0;
                            $graph_info[$tempKey][$count]['amount'] = abs($sales_amount - ($return_amount + $payment_amount) + $tempAmount);
                            if ($graph_info[$tempKey][$count]['amount'] == 0) {
                                $graph_info[$tempKey][$count]['amount'] = $tempAmount;
                                $graph_info[$tempKey][$count]['amountValue'] = $this->CommonHelper->inLakhs($tempAmount);
                            } else {
                                $graph_info[$tempKey][$count]['amountValue'] = $this->CommonHelper->inLakhs($graph_info[$tempKey][$count]['amount']);
                            }
                        } else {
                            $graph_info[$tempKey][$count]['amount'] = 0;
                            $graph_info[$tempKey][$count]['amountValue'] = 0;
                        }
                        $tempAmount = $graph_info[$tempKey][$count]['amount'];
                        $graph_info[$tempKey][$count]['Day'] = $da;
                        $graph_info[$tempKey][$count]['Date'] = $date;
                        $count++;
                    }
                }
            }
        }

        $paymentShopDailyGraph  = json_encode($graph_info);
        $this->view->paymentShopDailyGraphJson  = $paymentShopDailyGraph;
        $this->view->paymentShopDaily  = $graph_info;
        $this->view->shopId  = $shopId;
        $this->view->month  = $month;
        $this->view->monthFull  = $monthFull;
        $this->view->shopName  = $shopName;
        $this->view->currentMonth  = date('m')-1;
        $this->view->year  = $year;
        $this->view->graphType  = $graphType;
    }

    public function checkSalesCountAction()
    {
        $this->view->disable();
        $sales_model = new SalesReport;
        $checkCount = $sales_model->checkSalesTempCount();
        echo $checkCount;
    }
}
