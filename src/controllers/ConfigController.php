<?php
namespace src\controllers;

use \core\Controller;
use \src\handlers\UserHandler;
use \src\handlers\PostHandler;

class ConfigController extends Controller {
    private $loggedUser;
    private $user;

    public function __construct(){

        $this->loggedUser = UserHandler::checkLogin();
        if($this->loggedUser === false){
             $this->redirect('/login');
        }
    }
    
    public function index() {       

        //Pegando informações do usuario
        $user = UserHandler::getUser($this->loggedUser->id);
        
        $flash = '';
        if(!empty($_SESSION['flash'])){
            $flash = $_SESSION['flash'];
            $_SESSION['flash'] = '';
        }
        $dateFrom = $user->birthdate;
        $date = explode('-', $dateFrom);
        $date = $date[2].'-'.$date[1].'-'.$date[0];
        $user->birthdate = $date;

        

        $this->render('config',[
            'loggedUser' => $this->loggedUser,
            'user' => $user,
            'flash' => $flash
        ]);

    }

    public function save(){

        $name = filter_input(INPUT_POST, 'name');
        $birthdate = filter_input(INPUT_POST, 'birthdate');
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);        
        $city = filter_input(INPUT_POST, 'city');
        $work = filter_input(INPUT_POST, 'work');
        $password = filter_input(INPUT_POST, 'password');
        $confirm_password = filter_input(INPUT_POST, 'confirm_password');
        $user = UserHandler::getUser($this->loggedUser->id);
        

        //verifica se os dados foram preenchidos ok 
        //verifica se exist um email igual ok 
        //verifica se a data eh válida ok
        //tratar a data antes de enviar para o banco ok 
        //verificar se as senhas são iguais
        if ($name && $email) {
            $updateDatas = [];
            $updateDatas['id'] = $this->loggedUser->id;
            $updateDatas['name'] = $name;
            if($city && $work){
                $updateDatas['city'] = $city;
                $updateDatas['work'] = $work;
            }else{
                $_SESSION['flash'] = 'Necessário preencher Cidade e Trabalho';
                $this->redirect('/config');
            }
            if($email != $this->loggedUser->email){
                if(UserHandler::emailExist($email) === false){
                    $updateDatas['email'] = $email;
                }else{
                    $_SESSION['flash'] = 'E-mail já cadastro em nosso sistema';
                    $this->redirect('/config');
                }
            }else{
                $updateDatas['email'] = $this->loggedUser->email;
            }

            $birthdate = explode('/', $birthdate);
            if (count($birthdate) != 3) {
                $_SESSION['flash'] = 'Data inválida 1';
                $this->redirect('/config');
            }
            $birthdate = $birthdate[2] . '-' . $birthdate[1] .'-'. $birthdate[0];
            if (strtotime($birthdate) === false) {
                $_SESSION['flash'] = 'Data de nascimento inválida 2';
                $this->redirect('/config');
            }
            $updateDatas['birthdate'] = $birthdate;


            //Cover
            if(isset($_FILES['cover']) && !empty($_FILES['cover']['tmp_name'])){
                $newCover = $_FILES['cover'];
                if(in_array($newCover['type'],['image/jpeg', 'image/jpg', 'image/png'])){
                    $coverName = $this->cutImage($newCover, 850,310,'media/covers');
                    $updateDatas['cover'] = $coverName;
                } 
            }else{
                $updateDatas['cover'] = $user->cover;
            }

            //Avatar
            if(isset($_FILES['avatar']) && !empty($_FILES['avatar']['tmp_name'])){
                $newAvatar = $_FILES['avatar'];
                $updateDatas['echo'] = 'entrou aki';
                if(in_array($newAvatar['type'],['image/jpeg', 'image/jpg', 'image/png'])){
                    $avatarName = $this->cutImage($newAvatar, 200,200,'media/avatars');
                    $updateDatas['avatar'] = $avatarName;
                } 
            }else{
                $updateDatas['avatar'] = $user->avatar;
            }
            
            /*echo 'LoggedUser =>  ';
            var_dump($this->loggedUser);
            echo "<hr>";
            echo '$updateDatas =>  ';
            var_dump($user);
            exit;*/
            if(UserHandler::updateUser($updateDatas)){
                if($password && $confirm_password){
                    if(strcmp($password, $confirm_password) == 0){
                        UserHandler::updatePassword($password, $this->loggedUser->id);
                        $_SESSION['flash'] = 'Senha alterada com sucesso';
                        $this->redirect('/config');
                    }else{
                        $_SESSION['flash'] = 'Senhas não conferem';
                        $this->redirect('/config');
                    }
                }else if($password || $confirm_password){
                    $_SESSION['flash'] = 'Necessário preencher os campos "Nova Senha " e  "Confirmar Nova Senha" ';
                    $this->redirect('/config');
                }
                $_SESSION['flash'] = 'Dados alterados com sucesso';
                $this->redirect('/config');
            }
           
            
        } else {
            $_SESSION['flash'] = 'Nome ou e-mail não preenchidos';
            $this->redirect('/config');
        }
    }
    private function cutImage($file, $w, $h, $folder){
        list($widthOrig, $heightOrig) = getimagesize($file['tmp_name']);
        $ratio = $widthOrig / $heightOrig;

        $newWidth = $w;
        $newHeight = $newWidth / $ratio;

        if($newHeight < $h){
            $newHeight = $h;
            $newWidth = $newHeight * $ratio;
        }

        $x = $w - $newWidth;
        $y = $h - $newHeight;
        $x = $x < 0? $x / 2 : $x;
        $y = $y < 0? $y / 2 : $y;

        $finalImage = imagecreatetruecolor($w, $h);
        switch($file['type']){
            case 'image/jpeg':
            case 'image/jpg':
                $image = imagecreatefromjpeg($file['tmp_name']);
            break;
            case 'image/png':
                $image = imagecreatefrompng($file['tmp_name']);
            break;
        }
        imagecopyresampled(
            $finalImage, $image,
            $x, $y, 0,0,
            $newWidth, $newHeight, $widthOrig, $heightOrig
        );
        $fileName = md5(time().rand(0,9999)).'.jpg';
        imagejpeg($finalImage, $folder.'/'.$fileName);
        return $fileName;

    }

    
}

