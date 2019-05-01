<?php
include_once('../server/db.php');
  session_start();

  function GetNewTrips($db){
    if(isset($_SESSION['username'])){
      $username = $_SESSION['username'];
      $query = "Select P.*, GROUP_CONCAT(I.username) as Joiners From `Plans` AS P Left Join `Is_Going` as I On P.id = I.plan Where P.creator != '$username' AND P.id not in (Select plan From `Is_Going` Where username = '$username') group by P.id;";
      $r = mysqli_query($db, $query) or NULL;
      if(mysqli_num_rows($r) > 0){
        return $r;
      }else{
        return mysqli_error($db);
      }
    }
  }


  function GetJoinedTrips($db){
    if(isset($_SESSION['username'])){
      $username = $_SESSION['username'];
      $query = "Select P.*, GROUP_CONCAT(I.username) as Joiners From `Plans` AS P Inner Join `Is_Going` as I On P.id = I.plan Where I.plan in (Select plan From `Is_Going` Where username = '$username') group by P.id;";
      $r = mysqli_query($db, $query) or NULL;
      if(mysqli_num_rows($r) > 0){
        return $r;
      }else{
        return mysqli_error($db);
      }
    }
  }

  function GetOrganizedTrips($db){
    if(isset($_SESSION['username'])){
      $username = $_SESSION['username'];
      $query = "Select P.*, GROUP_CONCAT(I.username) as Joiners From `Plans` AS P Left Join `Is_Going` as I On P.id = I.plan Where P.creator = '$username' group by P.id";
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
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Tripp - Home</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  <style>
  body{
    background-color: #FFC966;
  }
  h1{
    color: #FFC966;
  }
  h3{
    color: #ff7d66;
  }
  kbd{
    font-size:18px;
  }
  nav{
    background-color: #ff7d66;
  }
  #floating-create-button{
    position: fixed;
    width: 80px;
    height:80px;
    border-radius: 100%;
    bottom: 10px;
    right:10px;
    background: #669cff;
  }
  </style>
</head>
<body>
  <header>
    <!-- Just an image -->
    <nav class="navbar">
      <a class="navbar-brand" href="#">
        <h1>Tripp</h1>
      </a>
      <form class="col-md-12 text-right" action="../server/server.php" method="post">
        <kbd class="text-right"><?php echo $_SESSION['username']; ?></kbd>
        <button class="small btn-sm" type="submit" name="logout">Logout</button>
      </form>
    </nav>
  </header>
  <div class="container">
    <div class="row">
      <div class="col-md-4">
        <div class="text-center col-md-12 mt-5"><h3>New Trips</h3></div>
        <?php
        $text = GetNewTrips($db);
        if($text != NULL){
          while($row = mysqli_fetch_array($text)) {
            $html = "<form class='mt-5' action='../server/server.php' method='post'>";
            $html .= '<div class="card w-100"><div class="card-body"><h5 class="card-title">'.$row['Name'].'</h5>';
            $html .= '<h6 class="card-subtitle mb-2 text-muted">Organized by '.$row['Creator'].'</h6>';
            $html .= '<p class="card-text"> Meeting will be in '.$row['Location'].' on '.$row['Date'].'</p>';
            $joiners = preg_split ("/\,/", $row['Joiners']);
            $joiners = $joiners[0] == NULL ? [] : $joiners;
            $html .= count($joiners) == 1 ? '<p class="card-text">'.$joiners[0].' is going.</p>': '<p class="card-text">'.count($joiners).' people are going.</p>';
            $html .= "<input name='Plan' value='".$row['id']."' type='text' style='display: none' />";
            $html .= '<button type="submit" name="join_plan" class="card-link">Join</button></div></div>';
            $html .= "</form>";
            echo $html;
          }
        }
        ?>
      </div>
      <div class="col-md-4">
        <div class="text-center col-md-12 mt-5"><h3>Organized Trips</h3></div>
        <?php
        $text = GetOrganizedTrips($db);
        if($text != NULL){
          while($row = mysqli_fetch_array($text)) {
            $html = "<form class='mt-5' action='../server/server.php' method='post'>";
            $html .= '<div class="card w-100" ><div class="card-body"><h5 class="card-title">'.$row['Name'].'</h5>';
            $html .= '<h6 class="card-subtitle mb-2 text-muted">Organized by You</h6>';
            $html .= '<p class="card-text"> Meeting will be in '.$row['Location'].' on '.$row['Date'].'</p>';
            $joiners = preg_split ("/\,/", $row['Joiners']);
            $joiners = $joiners[0] == NULL ? [] : $joiners;
            $html .= count($joiners) == 1 ? '<p class="card-text">'.$joiners[0].' is coming.</p>': '<p class="card-text">'.count($joiners).' people are coming.</p>';
            $html .= "<input name='Plan' value='".$row['id']."' type='text' style='display: none' />";
            $html .= '<button type="submit" name="delete_plan" class="card-link">Delete</button></div></div>';
            $html .= "</form>";
            echo $html;
          }
        }
        ?>
      </div>
      <div class="col-md-4">
        <div class="text-center col-md-12 mt-5"><h3>Joined Trips</h3></div>
        <?php
        $text = GetJoinedTrips($db);
        if($text != NULL){
          while($row = mysqli_fetch_array($text)) {
            $html = "<form class='mt-5' action='../server/server.php' method='post'>";
            $html .= '<div class="card w-100" ><div class="card-body"><h5 class="card-title">'.$row['Name'].'</h5>';
            $html .= '<h6 class="card-subtitle mb-2 text-muted">Organized by '.$row['Creator'].'</h6>';
            $html .= '<p class="card-text"> Meeting will be in '.$row['Location'].' on '.$row['Date'].'</p>';
            $joiners = preg_split ("/\,/", $row['Joiners']);
            $html .= count($joiners) == 1 ? '<p class="card-text">You are going.</p>': '<p class="card-text">'.count($joiners).' people are going.</p>';
            $html .= "<input name='Plan' value='".$row['id']."' type='text' style='display: none' />";
            $html .= '<button type="submit" name="leave_plan" class="card-link">Cancel</button></div></div>';
            $html .= "</form>";
            echo $html;
          }
        }
        $db->close();
        ?>
      </div>
    </div>
  </div>
  <!-- Button trigger modal -->
<button id="floating-create-button" type="button" class="btn btn-primary" data-toggle="modal" data-target="#createNewTrip">
  <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
  width="50" height="50"
  viewBox="0 0 192 192"
  style=" fill:#000000;"><g fill="none" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none" font-size="none" text-anchor="none" style="mix-blend-mode: normal"><path d="M0,192v-192h192v192z" fill="none"></path><g fill="#ffffff"><g id="surface1"><path d="M165.6,7.68c-4.785,0 -9.555,1.875 -13.2,5.52l-3.12,3.12l26.4,26.4c-0.015,0.015 3.12,-3.12 3.12,-3.12c7.305,-7.305 7.29,-19.11 0,-26.4c-3.66,-3.645 -8.415,-5.52 -13.2,-5.52zM143.4,23.16c-0.87,0.12 -1.68,0.555 -2.28,1.2l-124.56,124.68c-0.495,0.45 -0.87,1.035 -1.08,1.68l-7.68,28.8c-0.345,1.32 0.045,2.715 1.005,3.675c0.96,0.96 2.355,1.35 3.675,1.005l28.8,-7.68c0.645,-0.21 1.23,-0.585 1.68,-1.08l124.68,-124.56c1.53,-1.485 1.545,-3.93 0.06,-5.46c-1.485,-1.53 -3.93,-1.545 -5.46,-0.06l-123.96,123.96l-15.6,-15.6l123.96,-123.96c1.155,-1.11 1.5,-2.835 0.855,-4.305c-0.645,-1.47 -2.13,-2.385 -3.735,-2.295c-0.12,0 -0.24,0 -0.36,0z"></path></g></g></g></svg>
</button>

<div class="modal fade" id="createNewTrip" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Create a Trip</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="../server/server.php" method="post">
          <div class="form-group">
            <label for="Name">Name</label>
            <input name="Name" type="text" class="form-control" id="Name" placeholder="Enter Name" required>
          </div>
          <div class="form-group">
            <label for="Location">Location</label>
            <input name="Location" type="text" class="form-control" id="Location" placeholder="Enter Location"  required>
          </div>
          <div class="form-group">
            <label for="Date">Date</label>
            <input name="Date" type="date" class="form-control" id="Date" placeholder="Enter Date"  required>
          </div>
          <button name="create_plan" type="submit" class="btn btn-primary">Submit</button>
        </form>
      </div>
    </div>
  </div>
</div>
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>
</html>
