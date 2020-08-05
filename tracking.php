<?php
session_start();

//When page request comes from
if (isset($_GET['submitArtist'])) {

    //name of artist that has been selected
    $artist = $_GET['artistName'];

}

//$artist = "Kim Petras";


define('YOUTUBE_URL', 'https://www.googleapis.com/youtube/');

$GOOGLE = array(
    'client_id' => 'CLIENT ID',
    'client_secret' => 'CLIENT SECRET',
    'redirect_uri' => 'http://artistracker.andreavillegasma.com/main.php',
    'state' => 'ugbgfdmn'
);

$req = YOUTUBE_URL . 'v3/search';
$headers = array(
    'Content-Type:application/json'
);
$params = array(
    'part' => 'snippet',
    'maxResults' => '6',
    'order'=> 'relevance',
    'q' => $artist,
    'type'=> 'video',
    'key'=> 'KEY'


);
$opts = array(
    'http' => array(
        'header' => 'Content-Type:application/json',
        'method' => 'GET'
    )
);

$request = $req . '?' . http_build_query($params);
$str = stream_context_create($opts);
$result = file_get_contents($request, false, $str);
$results = json_decode($result);

//echo '<pre>';
//var_dump($results->items[0]);
//echo '<pre>';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artist Tracker</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>

<body>
<?php include_once 'header.php'?>
<main>
    <div class="container-fluid" id="grad" style="background-image: url('imgs/bg-01.png'); background-size: 102%;">
        <div class="">
            <a href="main.php" class="btn btn-light" id="back">Back to Artists</a>
            <div class="artists-title">
                <h1 class="title">Latest Videos of <?php echo $artist?> </h1>
                <p id="tag-line">Check out this tracked artist most relevant current videos</p>
            </div>
            <div class="flex">
                <?php foreach ($results->items as $i ){ ?>

                    <div class="video-container">
                        <iframe src="https://www.youtube.com/embed/<?php echo $i->id->videoId?>" height="220" width="400"></iframe>
                    </div>
                <?php } ?>
            </div>
        </div>

    </div>

</main>
<?php include_once 'footer.php'?>


</body>
