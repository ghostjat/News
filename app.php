<?php

require 'vendor/autoload.php';

use Core\News;
use Core\Sentiments\Analyzer;

$table = new Console_Table();
$sentiment = new Analyzer();
$sentiment->updateLexicon([
    'rubbish'=> '-1.5',
    'mediocre' => '-1.0',
    'agressive' => '-0.5',
    'green' => '1.0',
    'red' => '-1.0',
    'up' => '1.1',
    'down' => '-1.2',
    'percent' => '0.0'
]);

$news = new News();
$data = $news->getTopHeadLines(country: 'in');
$analyisData = [];
foreach ($data->articles as $key => $value) {
    $res = $sentiment->getSentiment($value->title);
    if($res['res'] > 0.55 ) {
        $analyisData[] = [
            $news->date($value->publishedAt),
            $table->green($news->limitedString($value->title)),
            $table->green($table->bold($news->icon('up'))),
            $table->green($res['res'])
        ];
    }
    
    if($res['res'] > 0 && $res['res'] < 0.55 ) {
        $analyisData[] = [
            $news->date($value->publishedAt),
            $table->blue($news->limitedString($value->title)),
            $table->blue($table->bold($news->icon('nu'))),
            $table->blue($res['res'])
        ];
    }
    
    if($res['res'] < 0 ) {
       $analyisData[] = [
            $news->date($value->publishedAt),
            $table->red($news->limitedString($value->title)),
            $table->red($table->bold($news->icon('dw'))),
            $table->red($res['res'])
        ];
    }
}
echo $table->fromArray(['Date','Title','View','Score'], $analyisData);