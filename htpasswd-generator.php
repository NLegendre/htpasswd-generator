<?php
/**
 * Generate htaccess and htpasswd protection
 *
 * Upload this file in the directory you want to protect, launch the script
 * configure, and it's protected.
 * The script is automatically deleted.
 *
 * @author : Nicolas Legendre | http://www.nicolaslegendre.com/
 */

//variables
$login = '';
$pass = '';
$message = '';
$success_htaccess = false;
$success_htpasswd = false;
$errors = array();
$dir_path = realpath(dirname(__FILE__));
$htaccess_path = $dir_path.'/.htaccess';
$htpasswd_path = $dir_path.'/.htpasswd';
if (file_exists($htaccess_path)) {
    $htaccess = file_get_contents($htaccess_path);
}
if (file_exists($htpasswd_path)) {
    $htpasswd = file_get_contents($htpasswd_path);
}

//if form is submited
if(isset($_POST['sub'])) {

    if(isset($_POST['login']) and !empty($_POST['login'])) {
        $login = $_POST['login'];
    } else {
        $errors['login'] = "Login field is a mandatory";
    }
    if(isset($_POST['pass']) and !empty($_POST['pass'])) {
        $pass = $_POST['pass'];
    } else {
        $errors['pass'] = "Password field is a mandatory";
    }
    if(isset($_POST['message']) and !empty($_POST['message'])) {
        $message = $_POST['message'];
    }

    //if no errors
    if(count($errors) == 0) {
        //create files if needed
        if(!isset($htaccess)) {
            //create .htaccess
            if(fopen(".htaccess", "w")) {
                $htaccess = file_get_contents($htaccess_path);
            } else {
                $errors['htaccess'] = "Htaccess file cannot be created";
            }
        }
        if(!isset($htpasswd)) {
            //create .htpasswd
            if(fopen(".htpasswd", "w")) {
                $htpasswd = file_get_contents($htpasswd_path);
            } else {
                $errors['htpasswd'] = "Htpasswd file cannot be created";
            }
        }

        //if htaccess already generated
        if(strpos($htaccess, "# HTPASS GEN start") !== false) {
            //remove old generated content
            $pos_start = strpos($htaccess, "# HTPASS GEN start");
            $pos_end = strpos($htaccess, "# HTPASS GEN end");
            $pos_end = $pos_end + 16;
            $to_remove = substr($htaccess, $pos_start, $pos_end);
            $htaccess_clean = str_replace($to_remove, '', $htaccess);
        } else {
            $htaccess_clean = $htaccess;
        }

        //htaccess generation
        $htaccess_generated = "# HTPASS GEN start";
        $htaccess_generated .= "\r\nAuthName \"$message\"";
        $htaccess_generated .= "\r\nAuthType Basic";
        $htaccess_generated .= "\r\nAuthUserFile \"$htpasswd_path\"";
        $htaccess_generated .= "\r\nRequire valid-user";
        $htaccess_generated .= "\r\n# HTPASS GEN end\r\n";

        //add the rest of htaccess file at the end
        $htaccess_generated .= $htaccess_clean;

        //write in htaccess file
        if(file_put_contents($htaccess_path, $htaccess_generated)) {
            $success_htaccess = true;
        }

        //htpasswd generation
        $htpasswd_generated = $login.':'.crypt($pass, base64_encode($pass));
        if(file_put_contents($htpasswd_path, $htpasswd_generated)) {
            $success_htpasswd = true;
        }
    }
}
?>


<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Htpasswd Generator</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
</head>
<body>

<nav class="navbar navbar-default">
    <div class="container">
        <div class="navbar-header">
            <a class="navbar-brand" href="#">Htpasswd Generator</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    The htaccess and htpasswd files will protect the folder : <strong><?php echo $dir_path; ?>/</strong>
                </div>
            </div>
        </div>
    </div>
    <?php
    if($success_htaccess or $success_htpasswd) {
        ?>
        <div class="row">
            <div class="col-sm-12">
                <div class="alert alert-success">
                    <?php
                    if($success_htaccess) {
                        ?>
                        Htaccess file generated!
                        <?php
                    }
                    if($success_htpasswd) {
                        ?>
                        <br>Htpasswd file generated!
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
    }

    if(count($errors) > 0) {
        ?>
        <div class="row">
            <div class="col-sm-12">
                <div class="alert alert-danger">
                    <?php
                    foreach ($errors as $err) {
                        echo $err . '<br>';
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
    <div class="row">
        <div class="col-sm-12">
            <form action="" method="post">
                <div class="form-group">
                    <label for="login">Login</label>
                    <input type="text" name="login" id="login" class="form-control" value="<?php echo $login; ?>" required>
                </div>
                <div class="form-group">
                    <label for="pass">Password</label>
                    <input type="password" name="pass" id="pass" class="form-control" value="<?php echo $pass; ?>" required>
                </div>
                <div class="form-group">
                    <label for="message">Message</label>
                    <input type="text" name="message" id="message" class="form-control" value="<?php echo $message; ?>" required>
                </div>
                <div class="form-group">
                    <input type="submit" name="sub" id="sub" class="btn btn-primary">
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>

<?php
//if folder is protected
if($success_htaccess and $success_htpasswd) {
    //delete the script
    unlink(__FILE__);
}
?>
