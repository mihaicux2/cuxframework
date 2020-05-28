<?php

use CuxFramework\utils\Cux;
?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-danger">
            <div class="panel-heading">
                <h3 class="panel-title"><?php echo "#" . $ex->getCode() . ": " . $ex->getMessage(); ?></h3>
            </div>
            <div class="panel-body">
<?php echo Cux::translate("core.errors", "Error/exception in file {file}, line {line}", array("{file}" => "<b>" . $ex->getFile() . "</b>", "{line}" => "<b>" . $ex->getLine() . "</b>"), "Message shown on an error page"); ?>
            </div>
        </div>
    </div>
    <?php
    $traces = $ex->getTrace();
    if (!empty($traces)) {
        ?>
        <div class="col-md-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">Debug</h3>
                </div>
                <div class="panel-body">
                    <div class="d-none d-sm-block">
                        <table class="table table-striped table-hover table-bordered table-fixed-layout2">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th><?php echo Cux::translate("core.errors", "File", array(), "Message show on an error page"); ?></th>
                                    <th><?php echo Cux::translate("core.errors", "Line", array(), "Message show on an error page"); ?></th>
                                    <th><?php echo Cux::translate("core.errors", "Class", array(), "Message show on an error page"); ?></th>
                                    <th><?php echo Cux::translate("core.errors", "Method", array(), "Message show on an error page"); ?></th>
                                    <th><?php echo Cux::translate("core.errors", "Arguments", array(), "Message show on an error page"); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($traces as $i => $trace) {
                                    if ($i == 0) {
                                        if (!isset($trace["file"])) {
                                            $trace["file"] = $ex->getFile();
                                        }
                                        if (!isset($trace["line"])) {
                                            $trace["line"] = $ex->getLine();
                                        }
                                    }
                                    $class = (($i + 1) % 2) ? "table-light" : "table-active";
                                    ?>
                                    <tr class="<?php echo $class; ?>">
                                        <td><?php echo $i; ?></td>
                                        <td><?php echo isset($trace["file"]) ? $trace["file"] : "&nbsp;"; ?></td>
                                        <td><?php echo isset($trace["line"]) ? $trace["line"] : "&nbsp;"; ?></td>
                                        <td><?php echo isset($trace["class"]) ? $trace["class"] : "&nbsp;"; ?></td>
                                        <td><?php echo isset($trace["function"]) ? $trace["function"] : "&nbsp;"; ?></td>
                                        <td><a href="javascript:showArgs(<?php echo $i; ?>);"><?php echo Cux::translate("core.errors", "Show", array(), "Message show on an error page"); ?></a></td>
                                    </tr>
                                    <tr id="args-<?php echo $i; ?>" style="display: none;">
                                        <td colspan="6">
                                            <pre><?php print_r($trace["args"]); ?></pre>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-block d-sm-none">
                        <table class="table table-striped table-hover table-fixed-layout text-break">
                            <tbody>
                                <?php
                                foreach ($traces as $i => $trace) {
                                    if ($i == 0) {
                                        if (!isset($trace["file"])) {
                                            $trace["file"] = $ex->getFile();
                                        }
                                        if (!isset($trace["line"])) {
                                            $trace["line"] = $ex->getLine();
                                        }
                                    }
                                    $class = (($i + 1) % 2) ? "table-light" : "table-active";
                                    ?>
                                    <tr class="<?php echo $class; ?>">
                                        <td>
                                            <div># <?php echo $i; ?></div>
                                            <div><?php echo Cux::translate("core.errors", "File", array(), "Message show on an error page"); ?> <?php echo isset($trace["file"]) ? $trace["file"] : "&nbsp;"; ?></div>
                                            <div><?php echo Cux::translate("core.errors", "Line", array(), "Message show on an error page"); ?> <?php echo isset($trace["line"]) ? $trace["line"] : "&nbsp;"; ?></div>
                                            <div><?php echo Cux::translate("core.errors", "Class", array(), "Message show on an error page"); ?> <?php echo isset($trace["class"]) ? $trace["class"] : "&nbsp;"; ?></div>
                                            <div><?php echo Cux::translate("core.errors", "Method", array(), "Message show on an error page"); ?> <?php echo isset($trace["function"]) ? $trace["function"] : "&nbsp;"; ?></div>
                                            <div><?php echo Cux::translate("core.errors", "Arguments", array(), "Message show on an error page"); ?> <pre><?php print_r($trace["args"]); ?></pre></div>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
</div>

<script>
    function showArgs(i) {
        $("#args-" + i).slideToggle();
    }
</script>