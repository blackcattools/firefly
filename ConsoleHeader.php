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
use ArrayObject;


// yii\helpers\BaseJson

//// http://www.nicholassolutions.com/tutorials/php/headers.html

class ConsoleHeader
{


    public static $allowFunctions = [
        'assert','clear','count','dir','dirxml','error',
        'group','groupCollapsed','groupEnd',
        'info','log','profile','profileEnd','table','time','timeEnd','timeStamp','trace','warn'
    ];

    public static $count = 1;

    //public static $active = false;
    public static $active = true;


    public static function __callStatic($name, $arguments)
    {

        if (! self::$active ){
            foreach (getallheaders() as $name => $value) {
                if ($name==='User-Agent'){
                    if (preg_match('/FirePHP/', $value)){
                        self::$active=TRUE;
                    }
                }
            }
        }

        if (self::$active) {

            if (in_array($name, self::$allowFunctions)){
                //self::registerHeader($name,$arguments);
                header('X-FireBug-'.$name.'-'.self::$count.': '.base64_encode(self::renderArguments($arguments)));
                self::$count++;

            }

        }
        
    }


    public static function readFile($filename){
        header('X-FireBug-File: '.$filename);
    }


    private static function renderArguments($arguments){
        $result = [];
        foreach ($arguments as $item) {
            if ($item instanceof JsExpression){
                $result[]="". mb_convert_encoding($item, 'UTF-8', 'UTF-8');
            } elseif( $item instanceof ArrayObject) {
                $tempitem = clone $item;

                array_walk_recursive($tempitem, function (&$val) {
                    if (is_string($val)) {
                        $val = mb_convert_encoding($val, 'UTF-8', 'UTF-8');
                    }
                });        
                $result[]=Json::encode($item->getArrayCopy());
            } elseif( is_object($item) ) {
                $tempitem = clone $item;
                $temp = (array) $tempitem;
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
                //$result[]='"'.Json::encode($item).'"';
                $result[]="'".Json::encode($item)."'";
            }
            
        }

        return implode(',',$result);
    }

 

}