<?
    session_start();
    
    $link = (empty($_SESSION['server_link'])) ? 'WebEngine.php?game=terminator_2|||quickfire' : $_SESSION['server_link'];
    
    $content = file_get_contents('Config.xml');
    $content = str_replace('SERVER_LINK', $link, $content);
    
    echo $content;
?>
