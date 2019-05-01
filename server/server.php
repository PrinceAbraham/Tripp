<?php
// Do not change the following two lines.
$teamURL = dirname($_SERVER['PHP_SELF']) . DIRECTORY_SEPARATOR;
$server_root = dirname($_SERVER['PHP_SELF']);
$domain = "http://localhost:8080";
$authURL = $domain."/views/auth.html";
$homeURL = $domain."/views/home.php";
$errorPage = $domain."/views/error.php";

include_once('db.php');

session_start();

// REGISTER USER
if (isset($_POST['register'])) {
  // receive all input values from the form
  $name = mysqli_real_escape_string($db, $_POST['Name']);
  $username = mysqli_real_escape_string($db, $_POST['Username']);
  $email = mysqli_real_escape_string($db, $_POST['Email']);
  $phone = mysqli_real_escape_string($db, $_POST['Phone']);
  $pass = mysqli_real_escape_string($db, $_POST['Password']);

  $query = "Select * From `User` Where email = '$email' OR username = '$username'";

  $r = mysqli_query($db, $query);

  if(mysqli_num_rows($r) > 0){
    $_SESSION['Error'] = "User Exists!";
    header('Location: '.$errorPage);
  }else{
    // register user if there are no errors in the form
    $pass = md5($pass);//encrypt the password before saving in the database
    $query = "INSERT INTO `User`(`username`, `Name`, `Email`, `Phone`, `Password`) VALUES ('$username','$name','$email','$phone','$pass')";

    mysqli_query($db, $query);

    $_SESSION['username'] = $username;

    $_SESSION['success'] = "You are now logged in";

    header('Location: '.$authURL);

  }
}

//Login
if(isset($_POST['login'])){
  // receive all input values from the form
  $username = mysqli_real_escape_string($db, $_POST['Username']);
  $pass = mysqli_real_escape_string($db, $_POST['Password']);

  //Hash
  $pass = md5($pass);

  $query = "Select * From User Where `username` = '$username' AND `password` = '$pass'";

  $r = mysqli_query($db, $query);

  if(mysqli_num_rows($r) > 0){
    $_SESSION['username'] = $username;

    $_SESSION['success'] = "You are now logged in";
    //echo "Logged IN";
    header("Location:".$homeURL);
  }else{
    $_SESSION['Error'] = "Wrong Credential! Please Try again.";
    header('Location: '.$errorPage);
  }
}

//Logout
if(isset($_POST['logout'])){
  ini_set('session.gc_max_lifetime', 0);
  ini_set('session.gc_probability', 1);
  ini_set('session.gc_divisor', 1);
  header("Location:".$authURL);
}


//Create Plan
if(isset($_POST['create_plan'])){
  //Username is logged in
  if(isset($_SESSION['username'])){

    $creator = mysqli_real_escape_string($db, $_SESSION['username']);
    $name = mysqli_real_escape_string($db, $_POST['Name']);
    $location = mysqli_real_escape_string($db, $_POST['Location']);
    $date = mysqli_real_escape_string($db, $_POST['Date']);

    $query = "INSERT INTO `Plans`(`Creator`, `Name`, `Location`, `Date`) VALUES ('$creator','$name','$location','$date')";

    mysqli_query($db, $query);

    header("Location:".$homeURL);
  }
}

//Join Plan
if(isset($_POST['join_plan'])){
  if(isset($_SESSION['username'])){
    $username = mysqli_real_escape_string($db, $_SESSION['username']);
    $plan = mysqli_real_escape_string($db, $_POST['Plan']);

    //Check if user is already going
    $query = "Select * From `Is_Going` Where `username` = '$username' AND `plan` = '$plan'";

    $r = mysqli_query($db, $query);
    if(mysqli_num_rows($r) > 0){
      $_SESSION['Error'] = "Oops, Something went wrong!";
      header('Location: '.$errorPage);
    }else{
      $query = "INSERT INTO `Is_Going`(`username`, `plan`) VALUES ('$username','$plan')";
      mysqli_query($db, $query);

      header("Location:".$homeURL);
    }
  }
}

//Leave Plan
if(isset($_POST['leave_plan'])){
  if(isset($_SESSION['username'])){
    $username = mysqli_real_escape_string($db, $_SESSION['username']);
    $plan = mysqli_real_escape_string($db, $_POST['Plan']);

    //Check if user is already going
    $query = "Select * From `Is_Going` Where `username` = '$username' AND `plan` = '$plan'";

    $r = mysqli_query($db, $query);
    if(mysqli_num_rows($r) > 0){
      $query = "DELETE FROM `Is_Going` WHERE username = '$username' and plan = $plan";
      mysqli_query($db, $query);

      header("Location:".$homeURL);
    }else{
      $_SESSION['Error'] = "Oops, Something went wrong!";
      header('Location: '.$errorPage);
    }
  }
}

//Delete Plans
if(isset($_POST['delete_plan'])){
  if(isset($_SESSION['username'])){
    $username = mysqli_real_escape_string($db, $_SESSION['username']);
    $plan = mysqli_real_escape_string($db, $_POST['Plan']);

    //Check if user owns the trip
    $query = "Select * From `Plans` Where `creator` = '$username' AND `id` = '$plan'";

    $r = mysqli_query($db, $query);
    if(mysqli_num_rows($r) > 0){
      $query = "DELETE FROM `Plans` WHERE creator = '$username' and id = $plan";
      header("Location:".$homeURL);
    }else{
      //User wasn't found to the plan
      $_SESSION['Error'] = "Oops, Something went wrong!";
      header('Location: '.$errorPage);
    }
  }
}

//Get ALL Plans
if(isset($_GET['All_Plans'])){
  if(isset($_SESSION['username'])){
    $query = "Select P.*, (P.creator = '$username') AS is_Owner, GROUP_CONCAT(I.username) as Joiners From `Plans` AS P LEFT Join `Is_Going` as I On P.id = I.plan Group by P.id;";
  }
}

function GetAllPlans($db){
  if(isset($_SESSION['username'])){
    $username = $_SESSION['username'];
    $query = "Select P.*, (P.creator = '$username') AS is_Owner, GROUP_CONCAT(I.username) as Joiners From `Plans` AS P LEFT Join `Is_Going` as I On P.id = I.plan Group by P.id;";
    $r = mysqli_query($db, $query) or NULL;
    if(mysqli_num_rows($r) > 0){
      return $r;
    }else{
      return mysqli_error($db);
    }
  }
}

?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title></title>
  </head>
  <body>
    <div class="">
      <form action="index.php" method="post">
        <h1>Register</h1>
        Name: <input type="text" name="Name"><br>
        E-mail: <input type="email" name="Email"><br>
        Username: <input type="text" name="Username"><br>
        Phone: <input type="text" name="Phone"><br>
        Password: <input type="password" name="Password"><br>
        <input name="register" type="submit">
      </form>
    </div>
    <div class="">
      <form action="index.php" method="post">
        <h1>Login</h1>
        Username: <input type="text" name="Username"><br>
        Password: <input type="password" name="Password"><br>
        <input name="login" type="submit">
      </form>
    </div>
    <div class="">
      <form action="server.php" method="post">
        <h1>Create Plan</h1>
        Name: <input type="text" name="Name"><br>
        Location: <input type="text" name="Location"><br>
        Date: <input type="date" name="Date"><br>
        <input name="create_plan" type="submit">
      </form>
    </div>
    <div class="">
      <?php
      $text = GetAllPlans($db);
      if($text != NULL){
        $html = "<form action='server.php' method='post'>";
        while($row = mysqli_fetch_array($text)) {
          // do something with the $row
          $html .= !$row['is_Owner'] ? "<div>Organized by " .$row['Creator'] ."</div><br>" : "<div>Organized by You</div><br>";
          $html .= "<div>".$row['Name']."</div><br>";
          $html .= "<div>At ".$row['Location']."</div><br>";
          $html .= "<label>On ".$row['Date']."</label><br><br>";
          $html .= "<label>" .$row['is_Owner'] ? $row['Joiners']." and You are going.</label><br><br>" : $row['Joiners'] ." others are going."."</label><br><br>";
          $html .= "<input name='Plan' value='".$row['id']."' type='text' style='display: none' />";
          $html .= !$row['is_Owner'] ? "<input value='Join' type='submit' name='join_plan'/><br><br>": "";
        }
        $html .= "</form>";
        echo $html;
      }
      $db->close();
      ?>
    </div>
  </body>
</html>
