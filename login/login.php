<?php
session_start();

include('../settings.php');
include(CMS_DIR . '/login/session.php');
include(CMS_DIR . '/login/functions.php');

$email = $_POST['email'];
$password = $_POST['password'];
$resend = $_GET['resend'];
$resend_email = $_GET['email'];

//login
if(isset($_POST['email']) && isset($_POST['password']))
{
   //verify password
   try {
      $sth = $dbh->prepare("SELECT password, salt FROM users WHERE email = ?");
      $sth->execute(array($email));
      
      if($sth->rowCount() < 1)
         push_error("Invalid Email address or Password.");
      die_on_error("login.php");
   
      $userData = $sth->fetch(PDO::FETCH_ASSOC);
   } catch(PDOException $e) {
      echo $e->getMessage();
      die();
   }
   
   $hash = crypt($password, "$2a$12$" . $userData['salt'] . "$");
   if($hash != $userData['password']) //incorrect password
   {
      push_error("Invalid Email address or Password.");
      die_on_error("login.php");
   }
   else
   {
      validateUser($email);
   }
   
   //Check if user credentials have been verified by clicking on link in email
   try {
      $sth = $dbh->prepare("SELECT verified FROM users WHERE email = ?");
      $sth->execute(array($email));
      list($verified) = $sth->fetch(PDO::FETCH_NUM);
   } catch (PDOException $e) {
      echo $e->getMessage();
   }
   if($verified === '0')
   {
      $link = "<a href=\"". CMS_URL ."/login.php?email=$email&resend=true\">here</a>";
      $msg = "Your registration has not yet been verified. Please check your email and click the verification link<br>";
      $msg .= "Click $link to resend link<br>";
      push_error($msg);
   }
   die_on_error("login.php");
   
   //redirect to main web page
   header('Location: '. WEBSITE_URL . '/index.php');
   die();
}
//resend verification link if needed
else if($resend === 'true' && $resend_email !== '')
{
   //lookup email and verification hash
   try {
      $sth = $dbh->prepare("SELECT email, verification_hash FROM users WHERE email = ?");
      $sth->execute(array($resend_email));
   list($resend_email, $verification_hash) = $sth->fetch(PDO::FETCH_NUM);
   } catch (PDOException $e){
      echo $e->getMessage();
      die();
   }

   //resend email
   $link = CMS_URL . "/verify.php?email=$resend_email&code=$verification_hash";
   $message = "Thank you for signing up for access to simple-cms.
   If you have received this email in error, please disregard it. Otherwise, please click on the link below to activate your account
   $link";
   if(!mail($resend_email, "Signup Verification", $message))
      push_error("Could not send email.  Please contact the website administrator for support.");
   die_on_error("register.php");

   push_status("Verification email resent.");
   header('Location: login.php');
   die();
}

//if we aren't checking login credentials, or resending verification link
//display the login form
print_session_vars();
?>

<form name="login" action="login.php" method="post">
    Email: <input type="text" name="email" />
    Password: <input type="password" name="password" />
    <input type="submit" value="Login" />
</form>

<?php
echo "<a href=\"". CMS_URL ."/register.php\">Register</a> ";
echo "<a href=\"". CMS_URL ."/reset_password.php\">Forgot Password?</a>";
?>

