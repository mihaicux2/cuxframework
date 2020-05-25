<?php

use CuxFramework\utils\Cux;
?>

<nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">
    <a class="navbar-brand" href="/"><?php echo Cux::translate("header", "CuxFramework Demo App", array(), "Message shown in the application header/navigation bar"); ?></a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarsExampleDefault">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item active">
                <a class="nav-link" href="/"><?php echo Cux::translate("header", "Home", array(), "Message shown in the application header/navigation bar"); ?> <span class="sr-only">(current)</span></a>
            </li>
        </ul>
        <ul class="navbar-nav">
            <?php if (Cux::getInstance()->user->isGuest()): ?>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="http://example.com" id="dropdown01" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo Cux::translate("header", "Welcome, {username}!", array("{username}" => Cux::translate("header", "guest", array(), "Guest/username")), "Message shown in the application header/navigation bar"); ?> </a>
                <div class="dropdown-menu" aria-labelledby="dropdown01">
                    <a class="dropdown-item" href="/login"><?php echo Cux::translate("header", "Login", array(), "Message shown in the application header/navigation bar"); ?></a>
                    <a class="dropdown-item" href="/contact"><?php echo Cux::translate("header", "Contact", array(), "Message shown in the application header/navigation bar"); ?></a>
                </div>
            </li>
            <?php else: ?>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="http://example.com" id="dropdown01" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img src="<?php echo Cux::getInstance()->user->getIdentity()->getProfilePic(20); ?>" class="rounded-circle" />
                    <?php echo Cux::translate("header", "Welcome, {username}!", array("{username}" => Cux::getInstance()->user->getIdentity()->getName()), "Message shown in the application header/navigation bar"); ?>
                </a>
                <div class="dropdown-menu" aria-labelledby="dropdown01">
                    <?php if (Cux::getInstance()->user->can("ADMIN_SECTION")): ?>
                    <a class="dropdown-item" href="/admin"><?php echo Cux::translate("header", "Application admin", array(), "Message shown in the application header/navigation bar"); ?></a>
                    <?php endif; ?>
                    <a class="dropdown-item" href="/account/profile">Profilul meu</a>
                    <a class="dropdown-item" href="/logout">Iesire</a>
                    <a class="dropdown-item" href="/contact">Contact</a>
                </div>
            </li>
            <?php endif; ?>
        </ul>
<!--        <form class="form-inline my-2 my-lg-0">
            <input class="form-control mr-sm-2" type="text" placeholder="Search" aria-label="Search">
            <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
        </form>-->
    </div>
</nav>
