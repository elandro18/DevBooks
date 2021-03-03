<?=$render('header', ['loggedUser'=> $loggedUser]);?>

<section class="container main">
    <?=$render('sidebar',['activeMenu' =>'config']);?>
    <section class="feed mt-10">
        
            <div class="column">
                <form class = 'config-form' enctype="multipart/form-data" method="POST" action="<?=$base;?>/config">
                    <?php
                        if(!empty($flash)):?>
                        <div class="flash"><?php echo $flash; ?></div>
                    <?php endif;?>
                    <label><b>Configurações</b></label>

                    <label>Novo Avatar:<br>
                        <input type='file' name="avatar" ><br>
                        <img class="image-edit" src="<?=$base;?>/media/avatars/<?=$user->avatar;?>" />
                    </label><br>

                    <label>Nova Capa:<br>
                        <input type="file" name="cover"><br>
                        <img class="image-edit" src="<?=$base;?>/media/covers/<?=$user->cover;?>" />
                    </label><br><br>

                    <hr><br>

                    <label>Nome Completo:<br>
                        <input class="input" type="text" name="name" require value="<?=$user->name;?>" /><br>
                    </label><br>

                    <label>Data de Nascimento:<br>
                        <input class="input" type="text" name="birthdate" id="birthdate" value="<?=$user->birthdate;?>" ><br> 
                    </label><br>

                    <label>E-mail:<br>
                        <input class="input" type="email" name="email" require value="<?=$loggedUser->email;?>" /><br>
                    </label><br>

                    <label>Cidade:<br>
                        <input class="input" type="text" name="city" value="<?=$user->city;?>" /><br>
                    </label><br>
                    <label>Tabalho:<br>
                        <input type="text" name="work" value="<?=$user->work;?>" ><br><br>
                    </label><br>

                    <hr><br>
                    
                    <label>Nova Senha:<br>
                        <input type="password" name="password"><br>
                    </label><br>
                    <label>Confirmar Nova Senha:<br>
                        <input type="password" name="confirm_password"><br><br>
                    </label><br>

                    <input class="button" type="submit" value="Salvar" /><br>
                </form>
            </div>  
       
    </section> 
</section>
<?=$render('footer');?>

<script src="https://unpkg.com/imask"></script>
    <script>
        IMask(
            document.getElementById('birthdate'),
            {
                mask:'00/00/0000'
            }
        );
</script>
