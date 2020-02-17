<?php
namespace Core;

class View{


  public static function twigReturn($template, $arguments = []){
    $loader=new \Twig\Loader\FilesystemLoader(dirname(__DIR__).'/App/Views');
//    optional if cache is to be stored....
//    $twig=new \Twig\Environment($loader,['cache'=>'../App/cache']);

     $twig = new \Twig\Environment($loader);

     return $twig->render($template, $arguments);
  }


  public static function twigRender($template, $arguments = []){
    echo(self::twigReturn($template, $arguments));
  }
}
