<?php


$app_id = "";
$consumer_key = "";
$consumer_secret = "";
$agent = '';



// require Facebook lib
require_once "FacebookStream.php";
require_once "Services/Facebook.php";

/* Sessions are used to keep track of tokens */
session_start();

if (empty($_SESSION['fb_state']))
  $_SESSION['fb_state'] = 'start';
  
$state = $_SESSION['fb_state'];

if ($_REQUEST['test'] === 'clear') {
  session_destroy();
  session_start();
}

if ($_SESSION['fb_request_token'] != NULL && $_SESSION['fb_state'] === 'start') {
  $_SESSION['fb_state'] = $state = 'returned';
}

/*
 * Switch based on where in the process you are
 *
 * 'default': Get a request token from Facebook for new user
 * 'returned': The user has authorize the app on Facebook
 */
switch ($state) {
  
  default:
    
    $fs = new FacebookStream($consumer_key,$consumer_secret,$agent);

    $token = $fs->getAccessToken();

    /* Save token for later */
    $_SESSION['fb_request_token'] = $token;
    
    $url  = 'http://www.facebook.com/login.php?api_key=';
    $url .= $fs->getApiKey();
    $url .= '&v=1.0&auth_token=';
    $url .= $token;
    
    header('Location:'.$url);
    exit;
    
  case 'returned':
    
    $fs = new FacebookStream($consumer_key,$consumer_secret,$agent);
    
    /* If the access tokens are already set skip to the API call */
    if ($_SESSION['fb_session'] === NULL && $_SESSION['fb_userid'] === NULL) {
      
      /* Create session */
      $session = $fs->getSession($_SESSION['fb_request_token']);
      
      /* Save the session data */
      $_SESSION['fb_session'] = (string)$session->session_key;
      $_SESSION['fb_userid'] = (string)$session->uid;
      
    }
    
    //$fs->setStatus("updating my status with my new php library called Facebook Streams",$_SESSION['fb_userid']);
    $fs->StreamRequest( $app_id, $_SESSION['fb_session'], $_SESSION['fb_userid'] );
    
}

?>

<html>
  <head>
    <title>Facebook Stream in PHP</title>
  </head>
  <body>
    <h2>Welcome to a Facebook Stream PHP example.</h2>
    <p>This site is a basic showcase of Facebook's new Stream.get method. Everything is saved in sessions. If you want to start over <a href='<?php echo $_SERVER['PHP_SELF']; ?>?test=clear'>clear sessions</a>.</p>

    <p>
      Get the code powering this at <a href='http://github.com/voittos/facebookstream'>http://github.com/voittos/facebookstream</a>
      <br />
      Read the documentation at <a href='https://docs.google.com/View?docID='>https://docs.google.com/View?docID=</a> 
    </p>

    <p><pre><?php print_r($content); ?><pre></p>

  </body>
</html>