<?php
session_start();
 //echo $_SESSION['spotify']['progress'];
 //echo $_SESSION['spotify']['token'];


define('SPOTIFY_URL', 'https://accounts.spotify.com');

$SPOTIFY = array(
    'client_id' => 'CLIENT ID',
    'client_secret' => 'CLIENT SECRET',
    'redirect_uri' => 'http://artistracker.andreavillegasma.com/main.php',
    'state' => 'ugbgfdmn'
);

//OAuth flow if it is the first time asking access
if (empty($_SESSION['spotify']['progress']) && !isset($_SESSION['spotify']['token'])) {
    authorize($SPOTIFY);
}
//Exchanging the code for a token
if (isset($_GET['code']) && $_SESSION['spotify']['progress'] == 'authorizing') {
    if ($_GET['state'] == $SPOTIFY['state']) {
        get_token($_GET['code'], $SPOTIFY);
    }
}


//API functions
//Authorizing the application to access their information
function authorize($config)
{
    $url = SPOTIFY_URL . '/authorize';
    $params = array(
        'response_type' => 'code',
        'client_id' => $config['client_id'],
        'redirect_uri' => $config['redirect_uri'],
        'state' => $config['state'],
        'scope'=>  'user-follow-read'
    );
    $request = $url . '?' . http_build_query($params);
    $_SESSION['spotify']['progress'] = 'authorizing';
    header("Location: $request");
}

//HELPER FUNCTIONS
//Exchanging the code for an access token and storing the access token on the session
function get_Token($code, $config) {
    $req = SPOTIFY_URL.'/api/token';
    //the "-H" flag in cURL command-line indicates header values
    $header = array(
        'Accept: application/json', //response is in JSON format
        'Content-Type: application/x-www-form-urlencoded' //the data will be passed in query string format
    );
    $userpwd = $config['client_id'].':'.$config['client_secret'];
    $data = array(
        'code' => $code,
        'redirect_uri' => $config['redirect_uri'],
        'grant_type' => 'authorization_code'
    );

    $c = curl_init(); //create cURL object
    //set some cURL options for the request

    curl_setopt($c, CURLOPT_URL, $req); //set the request url
    curl_setopt($c, CURLOPT_HTTPHEADER, $header); //set header values
    curl_setopt($c, CURLOPT_USERPWD, $userpwd); //pass the user/pwd value
    curl_setopt($c, CURLOPT_POST, true); //some examples use "1"
    curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query($data));

    curl_setopt($c, CURLOPT_RETURNTRANSFER, true); //THIS LINE MUST EXIST
    $result = json_decode(curl_exec($c)); //the response is JSON so just decode to a PHP object
    //print_r($result);
    if (isset($result->access_token)) {
        $_SESSION['spotify']['token'] = $result->access_token;
        $_SESSION['spotify']['progress'] = 'token';

        //echo $_SESSION['spotify']['token'];
    }
    curl_close($c); //always close your cURL connection
}

//IF WE HAVE AUTHORIZATION AND AN ACCESS TOKEN GET A LIST OF ALL THE ARTIST THAT PERSON FOLLOWS

//the urls are different so we declare another
define('SPOTIFY_URL_ARTISTS', 'https://api.spotify.com/');


$req = SPOTIFY_URL_ARTISTS . 'v1/me/following?type=artist';

//We put the authorization token on the header
$headers = array(
    'Content-Type:application/json',
    'Authorization: Bearer '.$_SESSION['spotify']['token']
);

//Using curl
$c = curl_init();
curl_setopt($c, CURLOPT_URL, $req);
curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
//curl_setopt($c, CURLOPT_POST, true); //true for POST request
//curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($artists)); //the request body in JSON format
curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
$results = json_decode(curl_exec($c));
curl_close($c);

//Checking if results are being printed
//print_r($results);
//echo '<pre>';
//var_dump($results->artists);
//echo '</pre>';



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
<!-- Nav Bar -->
<?php include_once 'header.php'?>
<main>
    <div class="container-fluid" id="grad" style="background-image: url('imgs/bg-01.png'); background-size: 102%;">
        <div class="">
            <div class="artists-title">
                <h1 class="title">Welcome to Artist Tracker!</h1>
                <p id="tag-line">See the artists you follow on Spotify below and view their latest content!</p>
            </div>
            <div class="flex">
                <?php foreach ($results->artists->items as $r ){ ?>

                    <div class="artist-container">
                        <img style="border-radius: 50%;" id="artist-pic" src="<?php echo $r->images[2]->url?>" alt="artist profile">
                        <div class="artist-name"><a style="color: white" href="<?php echo $r->uri?>"><?php echo $r->name ?></a></div>
                        <form method="get" action="tracking.php" class="buttons">
                            <input type="hidden" name="artistName" value="<?php echo $r->name ?>">
                            <button type="submit" name="submitArtist" class="btn btn-light">View Content</button>
                        </form>
                    </div>
                <?php } ?>
            </div>
        </div>

    </div>

</main>
<?php include_once 'footer.php'?>


</body>
