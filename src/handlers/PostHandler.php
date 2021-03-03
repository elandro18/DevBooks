<?php
namespace src\handlers;

use \src\models\Posts;
use \src\models\User;
use \src\models\PostLike;
use \src\models\PostComment;
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

    public static function _postListToObject($postList, $loggedUserId){
        // 3 - transformar os resultados em objetos dos models
        $posts = [];
        foreach($postList as $postItem){
            $newPost = new Posts();
            $newPost->id = $postItem['id'];
            $newPost->type = $postItem['type'];
            $newPost->created_at = $postItem['created_at'];
            $newPost->body = $postItem['body'];
            $newPost->mine = false;
            
            if($postItem['id_user'] == $loggedUserId){
                $newPost->mine = true;
            }            
        
            // 4 - preencher as informações adicionais no post
            $newUser = User::select()->where('id', $postItem['id_user'])->one();
            $newPost->user = new User();
            $newPost->user->id = $newUser['id'];
            $newPost->user->name = $newUser['name'];
            $newPost->user->avatar = $newUser['avatar'];
            // to do 4.1 preecher informações de like
            $likes = PostLike::select()->where('id_post', $postItem['id'])->get();
            $newPost->likeCount = count($likes);
            $newPost->liked = self::isLiked($postItem['id'], $loggedUserId);
            // to do 4.2 preencher informações comentarios
            $newPost->comments = PostComment::select()->where('id_post', $postItem['id'])->get();
            foreach($newPost->comments as $key => $comment){
                $newPost->comments[$key]['user'] = User::select()->where('id', $comment['id_user'])->one();
            }


            $posts[] = $newPost;
        }
        return $posts;
    }

    public static function isLiked($id, $loggedUserId){
        $mylike = PostLike::select()
            ->where('id_post', $id)
            ->where('id_user', $loggedUserId)
        ->get();
        if(count($mylike) > 0){
            return true;
        }else{
            return false;
        }

    }
    public static function deleteLike($id, $loggedUserId){
        PostLike::delete()
            ->where('id_post', $id)
            ->where('id_user', $loggedUserId)
        ->execute();
    }
    public static function addLike($id, $loggedUserId){
        PostLike::insert([
            'id_post' => $id,
            'id_user' => $loggedUserId,
            'created_at' => date('Y-m-d H:i:s')
        ])
        ->execute();
    }

    public static function addComment($id, $txt, $loggedUserId){
        PostComment::insert([
            'id_post' => $id,
            'id_user' => $loggedUserId,
            'created_at' => date('Y-m-d H:i:s'),
            'body' => $txt
        ])->execute();
    }

    public static function getUserFeed($idUser, $page, $loggedUserId){
        $perPage = 2;
        $postList = Posts::select()
            ->where('id_user', $idUser)
            ->orderBy('created_at', 'desc')
            ->page($page, $perPage)
        ->get();
        $pageCount = Posts::select()
            ->where('id_user', $idUser)
        ->count();

        $pageCount = ceil($pageCount / $perPage);
        // 3 - transformar os resultados em objetos dos models
        $posts = self::_postListToObject($postList, $loggedUserId);

        // 5 - retornar o resultado  
        return [
            'posts' => $posts,
            'pageCount' => $pageCount,
            'currentPage' => $page
        ];

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
        $posts = self::_postListToObject($postList, $idUser);
        // 5 - retornar o resultado  
        return [
            'posts' => $posts,
            'pageCount' => $pageCount,
            'currentPage' => $page
        ];
    }
    public static function getPhotosFrom($idUser){
        $photosData = Posts::select()
            ->where('id_user', $idUser)
            ->where('type', 'photo')
        ->get();
        $photos = [];
       
        foreach($photosData as $photo){
            $newPost = new Posts();
            $newPost->id = $photo['id'];
            $newPost->type = $photo['type'];
            $newPost->created_at = $photo['created_at'];
            $newPost->body = $photo['body']; 
            $photos[] = $newPost;

        }
       
        return $photos;

    }

    public static function delete($id, $loggedUserId){
        //1 . verificar se o post existe e se eh seu usuario logado 

        $post = Posts::select()
                ->where('id', $id)
                ->where('id_user', $loggedUserId)
           ->get();
        if(count($post) > 0){
            //2. deletar os likes e comments
            $post = $post[0];
            PostLike::delete()->where('id_post', $id)->execute();
            PostComment::delete()->where('id_post', $id)->execute();
            //3. se a foto for type== photo, deletar o arquivo 
            if($post['type'] === 'photo'){

                $img = __DIR__.'/../../public/media/uploads/'.$post['body'];
                if(file_exists($img)){
                    unlink($img);
                }
            }
            //4. deletar o post
            Posts::delete()->where('id', $id)->execute();
        }
        
        
    }
        


}