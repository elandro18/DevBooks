<?php
namespace src\handlers;

use \src\models\Posts;
use \src\models\User;
use \src\models\UserRelation;

class PostHandler{

    public static function addPost($idUser, $type, $body){

        $body = trim($body);
        if(!empty($idUser) && !empty($body)){

            Posts::insert([
                'id_user' => $idUser,
                'type' => $type,
                'created_at' => date('Y-m-d H:i:s'),
                'body' => $body
            ])->execute();
        }
    }

        
        public static function getHomeFeed($idUser, $page){
            $perPage = 2;
            // 1 - pegar lista de usuários que eu sigo
            $userList = UserRelation::select()->where('user_from', $idUser)->get();
            $users = [];
            foreach($userList as $userItem){
                $users[] = $userItem['user_to'];
            }
            $users[] = $idUser;
            // 2 - pegar os posts dessa galera ordenando pela data
            $postList = Posts::select()
                ->where('id_user', 'in', $users)
                ->orderBy('created_at', 'desc')
                ->page($page, $perPage)
            ->get();
            $pageCount = Posts::select()
                ->where('id_user', 'in', $users)
            ->count();

            $pageCount = ceil($pageCount / $perPage);
            // 3 - transformar os resultados em objetos dos models
            $posts = [];
            foreach($postList as $postItem){
                $newPost = new Posts();
                $newPost->id = $postItem['id'];
                $newPost->type = $postItem['type'];
                $newPost->created_at = $postItem['created_at'];
                $newPost->body = $postItem['body'];
                $newPost->mine = false;
                
                if($postItem['id_user'] == $idUser){
                    $newPost->mine = true;
                }
                
            
                // 4 - preencher as informações adicionais no post
                $newUser = User::select()->where('id', $postItem['id_user'])->one();
                $newPost->user = new User();
                $newPost->user->id = $newUser['id'];
                $newPost->user->name = $newUser['name'];
                $newPost->user->avatar = $newUser['avatar'];
                // to do 4.1 preecher informações de like

                $newPost->likeCount = 0;
                $newPost->liked = false;
                // to do 4.2 preencher informações comentarios
                $newPost->comments = [];


                $posts[] = $newPost;
            }
            // 5 - retornar o resultado  
            return [
                'posts' => $posts,
                'pageCount' => $pageCount,
                'currentPage' => $page
            ];
        }


}