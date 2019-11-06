<?php
use CuxFramework\utils\Cux;
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $pageTitle ?></title>
    <link rel="stylesheet" href="/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="/bootstrap/css/bootstrap-theme.css" />
    <link rel="stylesheet" href="/font-awesome/css/font-awesome.css" />
    <link rel="stylesheet" href="/css/main.css" />
    <script src="/js/jquery-3.1.1.js"></script>
    <script src="/bootstrap/js/bootstrap.js"></script>
    <script src="/js/main.js"></script>
</head>
<body>
    
    <div class="wrap">
        <?php echo Cux::getInstance()->layout->renderPartial("//header"); ?>
       
        <div class="container">
        <?php
        if (Cux::getInstance()->user->hasFlashMessages()){
            $flashes = Cux::getInstance()->user->getFlashMessages();
            foreach ($flashes as $key => $flashMessage){
            ?>
            <div class="alert alert-<?php echo $key; ?>"><?php echo $flashMessage; ?></div>
            <?php
            }
        }
        ?>
        <?php echo $content; ?>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
        <p class="pull-left">&copy; <?php echo Cux::getInstance()->copyright(); ?></p>
        <p class="pull-right"><?php echo Cux::getInstance()->poweredBy(); ?></p>
        </div>
    </footer>

</body>
</html>