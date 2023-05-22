<?php
error_reporting(0);
ini_set('display_errors', '0');
if (session_id() == "") {
  session_start();
}
if (!isset($_SESSION["admin"])) {
  session_destroy();
  echo '<script>alert("You are not allowed here!");
    window.location = "Homepage.php";
    </script>';
}
error_reporting(0);
// Include config file
require __DIR__ . '/vendor/autoload.php';
include('connection.php');
if (isset($_GET['id'])) {
  $_SESSION['event_id_edit'] = $_GET['id'];
}
if (isset($_POST['submit'])) {
  $name = $_POST['title'];
  $description = $_POST['description'];
  $event_id = $_SESSION['event_id_edit'];
  $updateData = [
    'description' => $description,
    'name' => $name
  ];
  require __DIR__ . '/vendor/autoload.php';
  include('connection.php');
  $ref_table = 'event';
  //Update the event table
  $fetch_data = $database->getReference($ref_table)->orderByChild('event_id')->equalTo((int) $_SESSION['event_id_edit'])->getValue();
  foreach ($fetch_data as $key => $row) {
    $database->getReference("$ref_table/$key")->update($updateData);
  }
  //Update the event_pic table and upload the files to firebase storage

  if (isset($_FILES['files'])) {
    $fileNames = array_filter($_FILES['files']['name']);
    if (!empty($fileNames)) {
      foreach ($_FILES['files']['name'] as $key => $val) {
        $name = time() . $_FILES['files']['name'][$key];
        $bucket->upload(
          file_get_contents($_FILES['files']['tmp_name'][$key]),
          [
            'name' => $name
          ]
        );
        $url = "https://firebasestorage.googleapis.com/v0/b/safepetshaven.appspot.com/o/" . $name . "?alt=media";
        $postData = [
          'event_id' => (int) $event_id,
          'picture_link' => $url
        ]; //this is the schema
        $database->getReference("event_pic")->push($postData);
      }
    }
  }
  echo '<script>alert("Successful Edit!");
  window.location = "Announcements_admin.php";
  </script>';
}
?>

<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=, initial-scale=1.0" />
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;300;400;500;600;700;800;900&display=swap"
    rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.4/font/bootstrap-icons.css">
  <title>Edit Event</title>
  <link rel="stylesheet" href="style.css">
</head>

<style>
  @media (max-width: 576px) {
    .carousel-item img {
      width: auto !important;
      height: 200px;
      max-height: 200px;
    }
  }

  @media (min-width: 576px) and (max-width: 768px) {
    .carousel-item img {
      width: auto !important;
      height: 250px;
      max-height: 250px;
    }
  }

  @media (min-width: 992px) and (max-width: 1199px) {
    .carousel-item img {
      width: auto !important;
      height: 300px;
      max-height: 300px;
    }
  }

  .description {
    background-color: #fddc6a;
    border-radius: 30px;
  }

  .testibg {
    background-color: #ffeba1;
  }

  .announcement-img {
    max-height: 400px;
  }

  img {
    max-height: 150px;
  }
</style>

<body>
  <?php require_once('header.php'); ?>
  <div class="container mt-5">
    <div class='col-12 text-center'>
      <h2 class="mb-5">Edit Event</h2>
    </div>
    <div class="container-fluid description p-5">
      <div class="row justify-content-start mb-2">
        <div class="container mt-5">
          <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
              <div class="form-container">
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"
                  enctype="multipart/form-data">
                  <?php
                  require __DIR__ . '/vendor/autoload.php';
                  include('connection.php');
                  $ref_table = 'event';
                  $fetch_data = $database->getReference($ref_table)->orderByChild('event_id')->equalTo((int) $_SESSION['event_id_edit'])->getValue();
                  foreach ($fetch_data as $key => $row) {
                    ?>
                    <label for="title" class="form-label">Event Title</label>
                    <input class="form-control mb-3" type="text" id="title" name="title"
                      value="<?php echo $row['name']; ?>" required>
                    <div class="mb-3">
                      <label for="description" class="form-label">Description</label>
                      <div class="">
                        <textarea class="form-control" name="description" id="description" rows="5" style="resize: none;"
                          value="<?php echo $row['description']; ?>" required><?php echo $row['description']; ?></textarea>
                        <label class="form-label mt-4">Delete uploaded images</label>
                        <div class="text-center">
                          <!--Loop through event images to display thumbnails of picture_link connected to event_id-->
                          <?php
                          $get_data = $database->getReference('event_pic')->orderByChild('event_id')->equalTo((int) $_SESSION['event_id_edit'])->getValue();
                          if ($get_data > 0) {

                            foreach ($get_data as $key2 => $row2) {
                              ?>
                              <div class="mb-3">
                                <img src="<?php echo $row2['picture_link'] ?>" class="img-thumbnail">
                                <a class="btn btn-danger delete" type="button"
                                  href="delete_image_event.php?key=<?php echo $key2 ?>">Delete</a>
                              </div>
                              <?php
                            }
                          }
                          ?>
                        </div>
                        <div class="mt-3">
                          <label for="formFileMultiple" class="form-label">Upload images</label>
                          <input type="file" class="form-control" id="formFileMultiple" name="files[]"  accept="image/*" multiple>
                        </div>
                      </div>
                    </div>
                    <div class="text-center">
                      <button type="submit" class="btn-secondary" name="submit">Edit Event</button>
                    </div>
                  </form>
                <?php } ?>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
      <script>
$('.delete').click(function () { return confirm("Are you sure you want to delete this image? The action is not reversible."); });
    </script>
</body>

</html>