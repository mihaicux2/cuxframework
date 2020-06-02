<?php

use CuxFramework\utils\Cux;
use CuxFramework\components\html\CuxHTML;
?>

<h1>CuxFramework - Available Commands</h1>

<div class="d-none d-sm-block">
    <table class="table table-striped table-hover table-fixed-layout2 table-bordered table-sm">
        <thead class="thead-light">
            <tr>
                <th width="30%">Command Name</th>
                <th width="45%">Command Details</th>
                <th width="15%">Core Command</th>
                <th width="10%">Options</th>
            </tr>
        </thead>
        <tbody>
                <?php foreach ($commands as $command): ?>
                <tr>
                    <td><?php echo $command["name"]; ?></td>
                    <td><?php echo $command["fullPath"]; ?></td>
                    <td><?php echo $command["coreCommand"] ? "Yes" : "No"; ?></td>
                    <td>
                        <?php echo CuxHTML::a("<span class='fas fa-question-circle'></span>", "javascript:void(0)", array("onclick" => "getCommandHelp('{$command["name"]}')", "title" => "Get command help", "class" => "text-info")); ?>
                        <?php echo CuxHTML::a("<span class='fas fa-running'></span>", "javascript:void(0)", array("onclick" => "executeCommandStep1('{$command["name"]}')", "title" => "Run command", "class" => "text-success")); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="commandDetailsBox" tabindex="-1" role="dialog" aria-labelledby="commandDetailsBoxTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="commandDetailsBoxTitle">Command Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="commandDetailsContent">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><span class='fas fa-times'></span> Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="commandExecBox" tabindex="-1" role="dialog" aria-labelledby="commandExecBoxTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="commandExecBoxTitle">Command Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="commandExecContent">
                <input type="hidden" name="commandName" id="cmdName" value="" />
                <div class="row">
                    <div class="col">
                        <?php echo CuxHTML::label("Arguments", "cmdParams"); ?>
                        <?php echo CuxHTML::textInput("cmdParams", "", array("class"=>"form-control")); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><span class='fas fa-times'></span> Close</button>
                <button type="button" class="btn btn-success" onclick="executeCommandStep2()"><span class='fas fa-running'></span> Execute</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    
    function executeCommandStep1(cmd){
        $("#commandExecBoxTitle").html("Execute command: " + cmd);
        $("#cmdName").val(cmd);
        $("#cmdParams").val("");
        $("#commandExecBox").modal("show");
    }
    
    function executeCommandStep2(){
        var cmd = $("#cmdName").val();
        var args = $("#cmdParams").val();
        
        $.blockUI({message: "<img src='/assets/img/ajax-loader.gif' />"});
        $("#commandExecBox").modal("hide");
        $.ajax({
            type: 'POST',
            url: '/cux/tools/executeCommand',
            data: {
                'cmd': cmd,
                'args': args
            },
            success: function (data) {
                $("#commandDetailsContent").html(data);
                $.unblockUI();
                $("#commandDetailsBoxTitle").html("Command details: " + cmd);
                $("#commandDetailsBox").modal("show");
            },
            error: function (err) {
                $.unblockUI();
                alert("<?php echo Cux::translate("ajax", "Error loading data", array(), "AJAX Loading error"); ?>");
                $("#commandExecBox").modal("show");
            }
        });
    }
    
    function getCommandHelp(cmd) {
        $.blockUI({message: "<img src='/assets/img/ajax-loader.gif' />"});
        $.ajax({
            type: 'GET',
            url: '/cux/tools/getCommandHelp',
            data: {
                'cmd': cmd
            },
            success: function (data) {
                $("#commandDetailsContent").html(data);
                $.unblockUI();
                $("#commandDetailsBoxTitle").html("Command details: " + cmd);
                $("#commandDetailsBox").modal("show");
            },
            error: function (err) {
                $.unblockUI();
                alert("<?php echo Cux::translate("ajax", "Error loading data", array(), "AJAX Loading error"); ?>");
            }
        });
    }
</script>
