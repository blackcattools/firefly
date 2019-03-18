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
//use common\component\ConsoleRemoteJson as Console;

use Yii;
use yii\web\View;
use yii\web\JsExpression;
use yii\helpers\Json;
use ArrayObject;


// yii\helpers\BaseJson

//// http://www.nicholassolutions.com/tutorials/php/headers.html

class ConsoleRemoteJson
{


    public static $allowFunctions = [
        'assert','clear','count','dir','dirxml','error',
        'group','groupCollapsed','groupEnd',
        'info','log','profile','profileEnd','table','time','timeEnd','timeStamp','trace','warn'
    ];

    public static $count = 1;

    //public static $active = false;
    public static $active = true;

    public static $fileData = NULL;

    public static $data=[];

    public static $jsonLimitFiles=10;

    // /home/lucianofranca/Área\ de\ Trabalho/backup\ notebook/var/www/html/ldapUserControl/frontend/web/logjson/
    // /home/lucianofranca/Área de Trabalho/backup notebook/var/www/html/ldapUserControl/frontend/web
    public static $folder = '@frontend/web/logjson';
    public static $webFolder = '/logjson';


    public static function __callStatic($name, $arguments)
    {


        if (self::$fileData===NULL){


            // verificar se existe a pasta onde o arquivo será salvo.
            if(!is_dir(Yii::getAlias(self::$folder))){
                mkdir(Yii::getAlias(self::$folder),0777);
            }


            // ajusta para excluir arquivos antigos
            //http://www.mauricioprogramador.com.br/posts/listar-arquivos-ordenado-por-data-com-php
            $logsFileName=[];
            if(is_dir(Yii::getAlias(self::$folder)))
            {
                    $diretorio = dir(Yii::getAlias(self::$folder));
                    while($arquivo = $diretorio->read())
                    {
                            if($arquivo != '..' && $arquivo != '.')
                            {
                            // Cria um Arrquivo com todos os Arquivos encontrados
                                    $logsFileName[date('Y/m/d H:i:s', filemtime(Yii::getAlias(self::$folder).'/'.$arquivo)).$arquivo] = Yii::getAlias(self::$folder).'/'.$arquivo;
                            }
                    }
                    $diretorio->close();
            }
            // Classificar os arquivos para a Ordem Crescente
            krsort($logsFileName, SORT_STRING);

            /// $manter=array_slice($arrayArquivos,0,self::$jsonLimitFiles);
            /// print_r($manter);

            $deleteFiles=array_slice($logsFileName,(self::$jsonLimitFiles-1));

            // Mostra a listagem dos Arquivos
            foreach($deleteFiles as $valorArquivos)
            {
                    //echo "excluir: $valorArquivos\n";
                    unlink($valorArquivos);
            }


            //generate
            //self::$fileData = Yii::getAlias(md5(time().rand()));
            self::$fileData = md5(time().rand()).'.json';


            //Yii::$app->view->registerJs("console.error('".self::$fileData."');");


            $__serveProtocol = (isset($_SERVER['HTTPS']))?'https':'http';
            $__serverTemp = $__serveProtocol.'://'.$_SERVER['HTTP_HOST'];

            header('X-FireBug-File: '.$__serverTemp.self::$webFolder.'/'.self::$fileData);
        }

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
                self::$data[]=[$name,base64_encode(self::renderArguments($arguments))];

                //self::$folder.'/'.

                //Yii::$app->view->registerJs("console.error('".self::$fileData."');");

                file_put_contents(Yii::getAlias(self::$folder).'/'.self::$fileData, json_encode(self::$data)); // ,JSON_PRETTY_PRINT
                chmod (Yii::getAlias(self::$folder).'/'.self::$fileData, 0777);  
                //Yii::$app->view->registerJs("console.error('teste');");
                //Yii::$app->view->registerJs("console.error('".Yii::getAlias(self::$folder)."');");

                //Yii::error(Yii::getAlias(self::$folder));

                //file_put_contents('../../frontend/web/logjson/'.self::$fileData, json_encode(self::$data));

            }

        }
        
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