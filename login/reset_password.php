<?php
session_start();

include('../settings.php');
include(CMS_DIR . '/login/session.php');
include(CMS_DIR . '/login/functions.php');

//password change form filled out from emailed link
if(isset($_POST['email']) && isset($_POST['code']) && isset($_POST['pass1']) && isset($_POST['pass2']))
{
   $email = $_POST['email'];
   $verification_hash = $_POST['code'];
   $pass1 = $_POST['pass1'];
   $pass2 = $_POST['pass2'];

   //check passwords match
   if($pass1 !== $pass2)
      push_error("Passwords do not match!");
   //don't use escaped email/code because then the url could have double escaped versions sent back out
   //in the url
   die_on_error(CMS_URL . "/reset_password.php?email=" . $_POST['email'] . "&code=" . $_POST['code']);

   //calculate new password hash
   if(CRYPT_BLOWFISH == 1)
   {
      $salt = substr(createSalt(),0,22);
      $hash = crypt($pass1, "$2a$12$" . $salt . "$");
   }
   else
      push_error("Cannot Change Password! CRYPT_BLOWFISH not installed in this version of php<br>");
   die_on_error("login.php");

   try {
      //change the password
      $sth = $dbh->prepare("UPDATE users SET password = ?, salt = ? WHERE email = ? AND verification_hash = ?");
      $sth->execute(array($hash, $salt, $email, $verification_hash));
   
      if($sth->rowCount() < 1)
      {
         push_error("Unable to reset password. Please try again.");
      }
      die_on_error(CMS_URL . "/reset_password.php?email=" . $_POST['email'] . "&code=" . $_POST['code']);
   
      //clear out verification hash
      $sth = $dbh->prepare("UPDATE users SET verification_hash = '' WHERE email = ?");
      $sth->execute(array($email));
   } catch (PDOException $e) {
      echo $e->getMessage();
   }

   push_status("Password successfully changed.");
   header('Location: login.php');
   die();
}
//reset form filled out via website, process reset request
else if(isset($_POST['email']))
{
   try{
      //make sure email exists
      $email = $_POST['email'];
      $sth = $dbh->prepare("SELECT email FROM users WHERE email = ?");
      $sth->execute(array($email));
      list($email) = $sth->fetch(PDO::FETCH_NUM);
      if($email !== '')
      {
         //the email is in the db, so let's create a verification key
         //and send it to their email address
         $verification_hash = substr(createSalt(), 0, 13);
   
         $link = CMS_URL . "/reset_password.php?email=$email&code=$verification_hash";
         $message = "Clicking the link below will take you to a page that will allow you to change your password.
         If you have received this email in error, please disregard it. Otherwise, please click on the link below to activate your account
         $link";
   
         //add the verification hash to the db
	 $sth = $dbh->prepare("UPDATE users SET verification_hash = ? WHERE email = ?");
	 $sth->execute(array($verification_hash, $email));

         //send them an email
         if($sth->rowCount())
         {
            if(mail($email, "Password Reset Verification", $message))
            {
               push_status("Please check your email for a verification link to change your password.");
               header("Location: login.php");
               die();
            }
            else
            {
               push_error("Could not send verification email to reset your password. Please contact your system adinistrator.");
               die_on_error("login.php");
            }
         }
         //email not found in db
         else
         {
            push_error("Cannot reset password.");
            die_on_error("login.php");
         }
      }
   } catch (PDOException $e) {
      echo $e->getMessage();
   }

   cleanup_session();
   die();
}
//reset link clicked in email, display password reset form with email already filled in
else if(isset($_GET['email']) && isset($_GET['code']))
{
   session_start();

   print_session_vars();

   $email = $_GET['email'];
   $verification_hash = $_GET['code'];

   //display password reset form
   $form = "<form name=\"reset\" action=\"reset_password.php\" method=\"post\">\n" .
           "   Email: $email<br>\n" .
	   "   <input type=\"hidden\" name=\"email\" value=\"$email\" />\n" .
	   "   <input type=\"hidden\" name=\"code\" value=\"$verification_hash\" />\n" .
           "   Password: <input type=\"text\" name=\"pass1\" />\n" .
           "   Password Again: <input type=\"text\" name=\"pass2\" />\n" .
           "   <input type=\"submit\" value=\"Submit\" />\n" .
           "</form>\n";
   echo $form;
   die();
}
//if no post or get vars, print the form
?>
Enter your email address that you signed up with and an email will be sent<br>
to you with a link to a page that will allow you to reset your password.<br>
<form name="reset" action="reset_password.php" method="post">
    Email: <input type="text" name="email" />
    <input type="submit" value="Reset" />
</form>

