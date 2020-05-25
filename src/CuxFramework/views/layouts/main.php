<?php

use CuxFramework\utils\Cux;
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="<?php echo Cux::getInstance()->charset; ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="">
        <meta name="author" content="">
        <link rel="icon" href="/docs/4.0/assets/img/favicons/favicon.ico">

        <title><?php echo $pageTitle ?></title>

        <!-- Bootstrap core CSS -->
        <link href="/cux-framework/assets/bootstrap-4.3.1/css/bootstrap.min.css" rel="stylesheet" />

        <!-- jQuery UI CSS -->
        <link href="/cux-framework/assets/jquery-ui/jquery-ui.min.css" rel="stylesheet" />

        <!-- FontAwesome CSS -->
        <link rel="stylesheet" href="/cux-framework/assets/font-awesome/css/all.css" />

        <!-- Toastr CSS -->
        <link rel="stylesheet" href="/cux-framework/assets/jquery-toast/jquery.toast.min.css" />

        <!-- Custom styles for this template -->
        <link href="/cux-framework/assets/css/main.css" rel="stylesheet" />

        <!-- Animate CSS -->
        <link href="/cux-framework/assets/animate-css-3.7.2/animate.min.css" rel="stylesheet" />

        <!-- jQuery -->
        <script src="/cux-framework/assets/js/jquery-3.1.1.js"></script>
        <script src="/cux-framework/assets/jquery-ui/jquery-ui.min.js"></script>
        <script src="/cux-framework/assets/js/jquery.blockUI.js"></script>

        <script src="/cux-framework/assets/js/main.js"></script>
    </head>

    <body>

        <?php echo Cux::getInstance()->layout->renderPartial("//header"); ?>

        <main role="main" class="container-fluid">

            <?php echo Cux::getInstance()->layout->renderPartial("//_flashes"); ?>

            <?php
            if (Cux::getInstance()->user->hasFlashMessages()) {
                $flashes = Cux::getInstance()->user->getFlashMessages();
                foreach ($flashes as $key => $flashMessage) {
                    ?>
                    <div class="alert alert-<?php echo $key; ?>"><?php echo $flashMessage; ?></div>
                    <?php
                }
            }
            ?>
            <div class="container">
                <?php echo $content; ?>
            </div>

        </main><!-- /.container -->

        <footer class="footer mt-auto py-3">
            <div class="container">
                <div class="float-left">&copy; <?php echo Cux::getInstance()->copyright(); ?></div>
                <div class="float-right"><?php echo Cux::getInstance()->poweredBy(); ?></div>
                <div class="clearfix"></div>
            </div>
        </footer>

        <!-- Bootstrap core JavaScript
        ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
        <script src="/cux-framework/assets/bootstrap-4.3.1/js/bootstrap.min.js"></script>
        <script src="/cux-framework/assets/jquery-toast/jquery.toast.min.js"></script>
    </body>
</html>
