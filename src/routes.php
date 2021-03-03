<?php
use core\Router;

$router = new Router();

$router->get('/', 'HomeController@index');
$router->get('/cadastro', 'LoginController@signup');
$router->post('/cadastro', 'LoginController@signupAction');
$router->get('/login', 'LoginController@signin');
$router->post('/login', 'LoginController@signinAction');

$router->post('/post/new', 'PostController@new');
$router->get('/post/{id}/delete', 'PostController@delete');

$router->get('/perfil/{id}/fotos', 'ProfileController@photos');
$router->get('/perfil/{id}/amigos', 'ProfileController@friends');
$router->get('/perfil/{id}/follow', 'ProfileController@follow');
$router->get('/perfil/{id}','ProfileController@index' );
$router->get('/perfil', 'ProfileController@index'); 
$router->get('/amigos', 'ProfileController@friends');
$router->get('/fotos', 'ProfileController@photos');

$router->get('/sair', 'LoginController@logout');
$router->get('/pesquisa', 'SearchController@index');
$router->get('/config', 'ConfigController@index');
$router->post('/config', 'ConfigController@save');

$router->get('/ajax/like/{id}', 'AjaxController@like');
$router->post('/ajax/comment', 'AjaxController@comment');
$router->post('/ajax/upload', 'AjaxController@upload');







//$router->get('/pesquisar');
//$router->get('/perfil');
//$router->get('/sair');
//$router->get('/amigos');
//$router->get('fotos');
//$router->get('/config');