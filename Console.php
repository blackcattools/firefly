<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @author Luciano Ferreora França
 *  
 * @link https://developer.mozilla.org/en-US/docs/Web/API/Console
 * @link https://console.spec.whatwg.org/
 *
 * use common\component\Console;
 *
 */

namespace blackcattools\firefly;

use Yii;
use yii\web\View;
use yii\web\JsExpression;
use yii\helpers\Json;


class Console
{


    public static $allowFunctions = [
        'assert','clear','count','dir','dirxml','error','group','groupCollapsed','groupEnd',
        'info','log','profile','profileEnd','table','time','timeEnd','timeStamp','trace','warn'
    ];


    public static function __callStatic($name, $arguments)
    {

        if (!in_array($name, self::$allowFunctions)){
            self::renderJSCommand('error',['invalid function console.$name() => redirect to console.error()']);
            $name = 'error';
        }
        self::renderJSCommand($name,$arguments);
    }


    private static function renderJSCommand($name, $arguments){
        Yii::$app->view->registerJs("//".rand().rand()."\n".
            "if (console !== undefined){".
                "if (console.".$name." !== undefined){".
                    "console.".$name."(".self::renderArguments($arguments).");".
                "}".
            "}");
    }


    private static function renderArguments($arguments){
        $result = [];
        foreach ($arguments as $item) {
            if ($item instanceof JsExpression){
                $result[]="". mb_convert_encoding($item, 'UTF-8', 'UTF-8');
            } elseif( $item instanceof ArrayObject) {
                array_walk_recursive($item, function (&$val) {
                    if (is_string($val)) {
                        $val = mb_convert_encoding($val, 'UTF-8', 'UTF-8');
                    }
                });                
                $result[]=Json::encode($item->getArrayCopy());
            } elseif( is_object($item) ) {
                $temp = (array) $item;
                array_walk_recursive($temp, function (&$val) {
                    if (is_string($val)) {
                        $val = mb_convert_encoding($val, 'UTF-8', 'UTF-8');
                    }
                });  
                $result[]=Json::encode($temp);
            } elseif( is_array($item) ) {

                array_walk_recursive($item, function (&$val) {
                    if (is_string($val)) {
                        $val = mb_convert_encoding($val, 'UTF-8', 'UTF-8');
                    }
                });  
                $result[]=Json::encode($item); ///JSON_FORCE_OBJECT
            } else {
                $result[]="'".$item."'";
            }
            
        }

        return implode(',',$result);
    }


}