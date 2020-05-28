<?php
use CuxFramework\utils\Cux;
use CuxFramework\components\html\CuxHTML;
?>

<div class="row">
    <div class="col-md-12">
        <div class="registration-panel panel panel-danger">
            <div class="panel-heading">
                <h3 class="panel-title"><?php echo "#".$ex->getCode().": ".$ex->getMessage(); ?></h3>
            </div>
            <div class="panel-body">
                <div class="alert alert-danger">
                <?php echo Cux::translate("core.errors", "Your request could not be processed correctly", array(), "Message shown on an error page"); ?>
                <br />
                <?php echo Cux::translate("core.errors", "Please click {here} to return to the main page", array("{here}" => CuxHTML::a(Cux::translate("core.errors", "here", array(), "Click here"), Cux::getInstance()->urlManager->createAbsoluteUrl("/"))), "Message shown on an error page"); ?>
                </div>
            </div>
        </div>
    </div>
</div>