$(document).ajaxError(function (event, jqxhr, settings, thrownError) {

    try {
        $.unblockUI();
    } catch (e) {
    }

//    for (var i = setTimeout(function () {}, 0); i > 0; i--) {
//        window.clearInterval(i);
//        window.clearTimeout(i);
//        if (window.cancelAnimationFrame)
//            window.cancelAnimationFrame(i);
//    }

    switch (jqxhr.status) {
        case 401:
        case 302:
            alert("Pentru aceasta actiune, trebuie sa fiti autentificat!");
            location.href = "/login";
            break;
            ;
        case 200:
            break;
        default:
            alert(jqxhr.responseText);
            break;
    }

});

function show_dialog(selector, title, width, height, butoane, isClosable) {

    if (isClosable == undefined) {
        isClosable = true
    }

    $(selector).dialog({
        width: width,
        height: height,
        title: title,
        modal: true,
        buttons: butoane,
        closeOnEscape: isClosable,
        close: function () {
            $(selector).dialog("destroy");
            $(selector).hide();
        }
    }).show();

    if (isClosable) {
        $(".ui-dialog-titlebar-close").show();
    } else {
        $(".ui-dialog-titlebar-close").hide();
    }

}

function cancel_dialog(selector) {
    $(selector).dialog("close");
//    $(selector).dialog("destroy");
}

function showToast(toastClass, message) {
    $.toast({
        text: message, // Text that is to be shown in the toast
//        heading: '<?php echo $class; ?>', // Optional heading to be shown on the toast
        icon: toastClass, // Type of toast icon
        showHideTransition: 'slide', // fade, slide or plain
        allowToastClose: true, // Boolean value true or false
        hideAfter: 8000, // false to make it sticky or number representing the miliseconds as time after which toast needs to be hidden
        stack: 10, // false if there should be only one toast at a time or a number representing the maximum number of toasts to be shown at a time
        position: 'top-center', // bottom-left or bottom-right or bottom-center or top-left or top-right or top-center or mid-center or an object representing the left, right, top, bottom values
        textAlign: 'left', // Text alignment i.e. left, right or center
        loader: true, // Whether to show loader or not. True by default
        loaderBg: '#9EC600', // Background color of the toast loader
        beforeShow: function () {}, // will be triggered before the toast is shown
        afterShown: function () {}, // will be triggered after the toat has been shown
        beforeHide: function () {}, // will be triggered before the toast gets hidden
        afterHidden: function () {}  // will be triggered after the toast has been hidden
    });
}