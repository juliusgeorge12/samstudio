<?php

use app\http\request\Request;
use app\http\request\RequestHandler;
 use app\http\Response\response;
use app\http\Response\responseInterface;
use Facades\redirect;
use Facades\Router;
use Facades\view;

Router::get('test' , 'userController@index');
   Router::get('admin/new' , function(RequestHandler $req , response $res){
        $res->setContentType('text/html');
        $res->output(view::render('new'));
          });
    Router::get('admin' , 'userController@admin');
    Router::get('admin/users', 'userController@users');
    Router::get('admin/user/add', 'userController@add_user');
    Router::get('admin/user/revoke/([0-9]+)*' , 'userController@revoke_user_form');
    Router::get('admin/user/undo_revoke/([0-9]+)*' , 'userController@undo_revoke_form');
    Router::get('admin/user/delete/([0-9]+)*' , 'userController@delete_user_form');
    Router::get('admin/users/validate', 'userController@verify_slot');
    Router::get('admin/dashboard', 'userController@home');
    Router::get('admin/recent', 'userController@recent');
    Router::post('admin/recent', 'userController@add_photo');
    Router::get('admin/login', 'userController@login');
    Router::get('artist/about' , 'userController@about');
    Router::post('admin/login', 'userController@login_validate');
    Router::get('admin/register', 'userController@signup');
    Router::get('admin/logout' , 'userController@logout');
    Router::get('admin/user/password/change' , 'userController@change_password_form');
    Router::get('admin/user/email/change' , 'userController@change_email_form');
    Router::get('admin/user/name/change' , 'userController@change_name_form');
    Router::post('admin/create', 'userController@create');
    Router::get('admin/manage/profile', 'userController@manage_about');
    Router::post('admin/manage/profile', 'userController@update_profile');
    Router::post('admin/user/new', 'userController@new_user');
    Router::post('admin/user/revoke/([0-9]+)*' , 'userController@revoke_user');
    Router::post('admin/user/undo_revoke/([0-9]+)*' , 'userController@undo_revoke');
    Router::post('admin/user/delete/([0-9]+)*' , 'userController@delete_user');
  
    Router::get('/' , 'customer@home');
    Router::get('service' , 'customer@service');
    Router::get('contact' , 'customer@contact');
    Router::post('contact' , 'customer@contact_form');
    Router::get('book' , 'customer@book');
    Router::get('admin/password/reset' , 'userController@password_reset');
    Router::post('admin/password/reset' , 'userController@password_reset_email');
    Router::post('admin/user/password/change' , 'userController@change_password');
    Router::post('admin/user/email/change' , 'userController@change_email');
    Router::post('admin/user/name/change' , 'userController@change_name');
    Router::post('hidden', function(RequestHandler $req , response $res){
           $x = $req->post();
           echo json_encode($x);
    } );
?>