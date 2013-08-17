<?php

/*
 * Caldera Layout Engine
 * Used to build responsive grid layouts
 * Based on PHP Scaffold https://github.com/Desertsnowman/PHP-Scaffold (yes I built it :)
 * 2012 - David Cramer
 */

class calderaLayout {

    private $layoutString = array();
    private $debug = false;
    private $layoutType = false;
    private $config = array();
    private $nests = array();
    private $output = '';
    public  $grid = array();
    

    function __construct() {
        
        $this->config = json_decode(file_get_contents(plugin_dir_path(__FILE__) . '/engine-config.json'), true);
        if(empty($this->config)){
            echo 'Error loading engine config';
            die;
        }
    }
    public function debug(){
        $this->debug = true;
    }
    public function setLayout($str){        
        // find nests
        preg_match_all("/\[[0-9:\|]+\]/", $str, $matches);
        if(!empty($matches[0])){
            foreach($matches[0] as $key=>$nest){
                $port = uniqid('__');
                $this->nests[$port] = substr($nest, 1, strlen($nest)-2);
                $str = str_replace($nest, $port, $str);
            }
        }
        $this->grid = $this->splitString($str);
    }
    private function splitString($str){
        $rows = explode('|', $str);
        $grid = array();
        foreach($rows as $row=>$cols){            
            $cols = explode(':',$cols);
            foreach($cols as $col=>$span){                
                $nest = strpos($span, '__');
                if($nest !== false){
                    $grid[$row+1][$col+1] = $this->splitString($this->nests[substr($span,$nest)]);
                }
                $grid[$row+1][$col+1]['span'] = $span;
                $grid[$row+1][$col+1]['html'] = '';
            }
        }
        return $grid;
    }
    static function mergeArray($first, $second, $type = 'replace'){       
        foreach($second as $key => $value) {
            if(is_array($value)){
                if(!isset($first[$key])){
                    $first[$key] = array();
                }
                $first[$key] = self::mergeArray($first[$key], $value, $type);
            }else{
                switch ($type){
                    case 'replace':
                        $first[$key] = $value;
                        break;
                    case 'append':
                        if(empty($first[$key])){
                            $first[$key] = $value;
                        }else{
                            $first[$key] .= $value;
                        }
                        break;
                    case 'prepend':
                        if(empty($first[$key])){
                            $first[$key] = $value;
                        }else{
                            $first[$key] = $value.$first[$key];
                        }
                        $first[$key] = $value.$first[$key];
                        break;
                }
            }
        }
        return $first;
    }
    static function mapValue($type, $value, &$map){
        $out = '';$end = '';
        $map = explode(':', $map);
        foreach($map as $key=>$val){
            $out .= '{"'.$val.'":';
            $end .= "}";
        }
        $map = json_decode($out.json_encode(array($type=>$value)).$end, true);        
    }
    public function html($html, $map, $type = 'replace') {
        $this->mapValue('html', $html, $map);
        $this->grid = self::mergeArray($this->grid, $map, $type);
    }
    public function before($html, $map) {
        $this->mapValue('before', $html, $map);
        $this->grid = self::mergeArray($this->grid, $map);
    }
    public function after($html, $map) {
        $this->mapValue('after', $html, $map);
        $this->grid = self::mergeArray($this->grid, $map);
    }
    public function append($html, $map) {
        self::html($html, $map, 'append');
    }
    public function prepend($html, $map) {
        self::html($html, $map, 'prepend');
    }
    public function setClass($class, $map){
        $this->mapValue('class', $class, $map);
        $this->grid = self::mergeArray($this->grid, $map);
    }
    public function appendClass($class, $map){
        $this->mapValue('class', $class, $map);
        $this->grid = self::mergeArray($this->grid, $map, 'append');        
    }
    public function prependClass($class, $map){
        $this->mapValue('class', $class, $map);
        $this->grid = self::mergeArray($this->grid, $map, 'prepend');
    }    
    public function setRowID($ID, $row){
        if(!isset($this->grid[$row])){return;}
            $this->grid[$row]['id'] = $ID;
    }
    public function setID($ID, $map){
        $this->mapValue('id', $ID, $map);
        $this->grid = self::mergeArray($this->grid, $map);
    }
    public function renderLayout($grid = false) {
        $inner = true;
        if(empty($this->grid)){
                return 'ERROR: Layout string not set.';
        }
        if(empty($grid)){
            $inner = false;
            $grid = $this->grid;
        }
        
        foreach($grid as $row=>$cols){

            $rowID = '';
            $rowClass = '';
            $rowBefore = '';
            $rowAfter = '';
            
            if(isset($cols['id'])){
                $rowID = 'id="'.$cols['id'].'" ';
                unset($cols['id']);
            }
            
            if(isset($cols['class'])){
                $rowClass = $cols['class'];
                unset($cols['class']);
            }
            
            if(isset($grid['*']['class'])){
                $rowClass .= $grid['*']['class'];
            }
            
            if($row === 1 && $row !== count($grid)){
                $rowClass .= " ".$this->config['row']['first'];
            }elseif ($row === count($grid) && $row !== 1){
                $rowClass .= " ".$this->config['row']['last'];
            }elseif ($row === count($grid) && $row === 1){
                $rowClass .= " ".$this->config['row']['single'];
            }
            
            if(isset($cols['before'])){
                $this->output .= $cols['before'];
            }
                        
            $this->output .= sprintf($this->config['row']['before'], $rowID, $rowClass);//"<div ".$rowID."class=\"".$gridClass." ".$rowClass."\">\n";
            
            if(!is_array($cols)){
                echo $cols;
            }
                foreach($cols as $col=>$content){
                    if(!isset($content['span'])){continue;}
                    $colClass = '';
                    if(isset($content['class'])){
                        $colClass = $content['class'];
                        unset($content['class']);
                    }
                    if(isset($cols['*']['class'])){
                        $colClass .= $cols['*']['class'];
                    }
                    
                    if($col === 1 && $col !== count($cols)){
                        $colClass .= " ".$this->config['columns']['first'];
                    }elseif($col === count($cols) && $col !== 1){
                        $colClass .= " ".$this->config['columns']['last'];
                    }elseif($col === count($cols) && $col === 1){
                        $colClass .= " ".$this->config['columns']['single'];
                    }
                    $colID = '';
                    if(isset($content['id'])){
                        $colID = 'id="'.$content['id'].'"';
                        unset($content['id']);
                    }
                    if(isset($content['before'])){
                        $this->output .= $content['before'];
                        unset($content['before']);
                    }
                    $afterBuffer = '';
                    if(isset($content['after'])){
                        $afterBuffer = $content['after'];
                        unset($content['after']);
                    }
                    $span = ! empty($this->config['columns'][$content['span']]) ? $content['span'] : 'default';                    
                    $this->output .= sprintf($this->config['columns'][$span]['before'], $colID, $content['span'], $colClass);//"    <div class=\"span".$content['span']." ".$colClass."\">\n";
                    $this->output .= $content['html'];
                    unset($content['html']);
                    unset($content['span']);
                    if(!empty($content)){
                       $this->output = $this->renderLayout($content);                       
                    }
                    $this->output .= $this->config['columns'][$span]['after'];
                    $this->output .= $afterBuffer;                    
                }
            $this->output .= $this->config['row']['after'];//"</div>\n";            
            if(isset($cols['after'])){
                $this->output .= $cols['after'];
            }
            
        }

        //dump($grid);
        return $this->output;
    }

}

?>