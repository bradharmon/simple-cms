<?php
function validateUser($email)
{
    session_regenerate_id (); //this is a security measure
    $_SESSION['valid'] = 1;
    $_SESSION['email'] = $email;
}

function isLoggedIn()
{
    if($_SESSION['valid'])
        return true;
    return false;
}

function logout()
{
    $_SESSION = array(); //destroy all of the session variables
    session_destroy();
}
function cleanup_session()
{
   unset($_SESSION['errors']);
   unset($_SESSION['statuses']);
}
function push_error($error)
{
   global $errors, $err;
   $errors[] = $error;
   $_SESSION['errors'] = $errors;
   $err = true;
}
function push_status($status)
{
   global $statuses;
   $statuses[] = $status;
   $_SESSION['statuses'] = $statuses;
}
function die_on_error($forward_page)
{
   global $err;
   if($err)
   {
      #echo "$forward_page";
      header('Location: ' . $forward_page);
      die();
   }
}
function print_session_vars()
{
if(isset($_SESSION['errors']) && is_array($_SESSION['errors']))
{
   echo "errors:<br>";
   foreach($_SESSION['errors'] as $error)
   {
      echo "$error<br>";
   }
}
if(isset($_SESSION['statuses']) && is_array($_SESSION['statuses']))
{
   echo "status:<br>";
   foreach($_SESSION['statuses'] as $status)
   {
      echo "$status<br>";
   }
}
cleanup_session();
}
?>
