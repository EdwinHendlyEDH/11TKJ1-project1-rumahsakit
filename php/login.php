<?php 
$GLOBALS['title'] = 'EHealt | Login';
require_once "./functions.php";

EnsureUserAuth($conn, 'login');


if($_SERVER['REQUEST_METHOD'] === "POST"){
    if(isset($_POST['login'])){
        $res = login($_POST);
        $old = $_POST;

        if($res instanceof W_ErrorValidator){
            $errorCredentials = $res->getErrors();
        }else if ($res instanceof W_Message){
            if($res->status == 'failed' || $res->status == "need-verify"){
                $error = $res->message;
            }else{
                header('Location: ../index.php');
                exit;
            }
        }
    }
}

?>

 
<?php require_once "./partials/header.php";?>

<div class="page" style="min-height: 100vh; display: grid; place-items: center;">
    <div class="row" style="width: 100%;">
        <div class="col-10 col-md-5 col-lg-4 col-xl-3 mx-auto border rounded" style="padding: 2rem;">
            <h2>Login Page</h2>

            <?php if(isset($error)): ?>
                <div class="alert alert-danger">
                    <?= $error?>
                    <?= $res->status == "need-verify" ? "<a href='./verify.php'>Verifikasi disini!</a>" : ''?>
                </div>
            <?php endif; ?>

            <form action="" method="post">

                <div class="form-group my-3">
                    <label for="email" class="mb-1">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?= $old['email'] ?? ''?>"/>
                    <?php if(isset($errorCredentials['email'])): ?>
                        <div class="text-danger" style="font-size: .9rem">
                            <?php foreach($errorCredentials['email'] as $err): ?>
                                <p class="mb-0"><?= $err?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group my-3">
                    <label for="password" class="mb-1">Password</label>
                    <input type="password" id="password" name="password" class="form-control"/>
                    <?php if(isset($errorCredentials['password'])): ?>
                        <div class="text-danger" style="font-size: .9rem">
                            <?php foreach($errorCredentials['password'] as $err): ?>
                                <p class="mb-0"><?= $err?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-check form-switch">
                    <input type="checkbox" id="remember-me" class="form-check-input" name="remember-me">
                    <label for="remember-me" class="form-check-label">Remember Me</label>
                </div>


                <div class="mt-4">
                    <button type="submit" class="btn btn-primary" name="login">Login</button>
                </div>
                <a href="./register.php" class="d-block mt-2">Dont have account? Register here!</a>
            </form>
        </div>
    </div>
</div>

<?php require_once "./partials/footer.php";?>

