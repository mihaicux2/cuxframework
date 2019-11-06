<?php
use CuxFramework\utils\Cux;
?>

<nav class="navbar navbar-default navbar-fixed-top">
    <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="/">CuxFramework</a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav navbar-right">
                <?php if (Cux::getInstance()->user->isGuest()): ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Welcome, guest! <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a href="/login">Login</a></li>
                        <li><a href="/register">Register</a></li>
                        <li role="separator" class="divider"></li>
                        <li><a href="/contact">Contact</a></li>
                    </ul>
                </li>
                <?php else: ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Welcome, <?php echo Cux::getInstance()->user->getIdentity()->getName(); ?> <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a href="/account/profile">My Account</a></li>
                        <li><a href="/logout">Logout</a></li>
                        <li><a href="/contact">Contact</a></li>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>