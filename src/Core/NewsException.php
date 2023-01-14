<?php

declare(strict_types=1);
namespace Core;

/**
 * Description of NewsException
 *
 * @author ghost
 */
class NewsException extends \Exception {
    
    public function errorInfo(){
        return "{$this->getMessage()} on line {$this->getLine()} in file {$this->getFile()}";
    }
}
