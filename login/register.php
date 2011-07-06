<?php
session_start();

include('../settings.php');
include(CMS_DIR . '/login/functions.php');
include(CMS_DIR . '/login/session.php');

//===============================
//MAIN
//===============================
//retrieve our data from POST
$email = $_POST['email'];
$pass1 = $_POST['pass1'];
$pass2 = $_POST['pass2'];

$err = false;
$errors = array();
$statuses = array();

//collect registration information
if(isset($_POST['email']) && isset($_POST['pass1']) && isset($_POST['pass2']))
{
   try {
      $sth = $dbh->prepare("SELECT email FROM users WHERE email = ?");
      $sth->execute(array($email));
      if($sth->fetch(PDO::FETCH_ASSOC))
         push_error("You have already registered an account.");
      die_on_error("login.php");
   } catch (PDOException $e) {
      print $e->getMessage();
   }
   
   if($pass1 !== $pass2)
      push_error("Passwords do not match!");
   //validate email
   if(!filter_var($email, FILTER_VALIDATE_EMAIL))
      push_error("Your email address is invalid.");
   die_on_error("register.php");
   
   if(CRYPT_BLOWFISH == 1)
   {
      $salt = substr(createSalt(),0,22);
      $hash = crypt($pass1, "$2a$12$" . $salt . "$");
   }
   else
      push_error("Cannot Register! CRYPT_BLOWFISH not installed in this version of php<br>");
   die_on_error("register.php");
   
   //sanitize email
   $verification_hash = substr(createSalt(), 0, 13);
   try{
      $sth =$dbh->prepare("INSERT INTO users (email, password, salt, verified, verification_hash)
                        VALUES(?, ?, ?, FALSE, ?)");
      $sth->execute(array($email, $hash, $salt, $verification_hash));
   } catch(PDOException $e) {
      echo $e->getMessage();
      die();
   }
   
   //send email with verification hash
   $link = CMS_URL . "/verify.php?email=$email&code=$verification_hash";
   $message = "Thank you for signing up for access to simple-cms.
   If you have received this email in error, please disregard it. Otherwise, please click on the link below to activate your account
   $link";
   if(!mail($email, "Signup Verification", $message))
      push_error("Could not send email.  Please contact the website administrator for support.");
   die_on_error("register.php");
   
   //successfully registered - clear out any previous errors
   unset($_SESSION['errors']);
   push_status("Thank you for registering. Please check your email and click on the validation link to complete the registration process.");
   header('Location: login.php');
   die();
}

//show the registration form
print_session_vars();
?>
<form name="register" action="register.php" method="post">
    Email: <input type="text" name="email" maxlength="64" />
    Password: <input type="password" name="pass1" />
    Password Again: <input type="password" name="pass2" />
    <input type="submit" value="Register" />
</form>
