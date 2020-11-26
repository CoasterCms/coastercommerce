
(function () {
    if ( typeof window.CustomEvent === "function" ) return false;
    function CustomEvent ( event, params ) {
        params = params || { bubbles: false, cancelable: false, detail: undefined };
        var evt = document.createEvent( 'CustomEvent' );
        evt.initCustomEvent( event, params.bubbles, params.cancelable, params.detail );
        return evt;
    }
    CustomEvent.prototype = window.Event.prototype;
    window.CustomEvent = CustomEvent;
})();

var alertTitles = {
    danger: 'Error',
    info: 'Notice',
    success: 'Success',
    warning: 'Warning'
};

function commerceAlert(alertClass, alertContent) {
    var newAlert = $('#commerceAlert').clone();
    newAlert.append('<b>' + alertTitles[alertClass] + ':</b> ' + alertContent)
        .addClass('alert-' + alertClass).show();
    $('#commerceAlerts').append(newAlert);
    setTimeout(function () {
        //newAlert.fadeOut(2500, function () {
        //    $(this).remove();
        //});
    }, 7500);
    $('html, body').stop().animate({scrollTop: 0}, 500);
}

function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+ d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) === ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) === 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function switchLabelUpdate() {
    $(this).parent().find('.custom-control-label').html($(this).prop('checked') ? 'Yes' : 'No');
}

jQuery(document).ready(function ($) {

    $('.confirm').click(function () {
        var confirm = $(this).data('confirm');
        confirm = confirm ? ' ' + confirm : '';
        return window.confirm('Are you sure' + confirm + '?');
    });

    function toggleSidebar() {
        $('.sidebar').toggleClass('hide');
        $('footer').toggleClass('smallleft');
        $('.navbar').toggleClass('smallleft');
        $('.mainwrap').toggleClass('smallleft').toggleClass('smallleftmargin');
        $('.closearrow').toggleClass('smallleft').toggleClass('rotate');
        setCookie('cc-hide-sidebar', $('.sidebar').hasClass('hide'), 365);
    }
    if (getCookie('cc-hide-sidebar') === 'true') {
        toggleSidebar();
    }
    $('.closearrow').click(toggleSidebar);

    $('.custom-switch > input').change(switchLabelUpdate).trigger('change');

    tinymce.init({
        selector: 'textarea.tinymce',
        plugins: 'link table lists paste code image media filemanager responsivefilemanager fullscreen',
        menubar: 'edit view insert format tools table',
        toolbar: 'undo redo | bold italic underline strikethrough | forecolor backcolor casechange permanentpen formatpainter fontsizeselect formatselect removeformat | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist checklist | insertfile image ',
        height: 300,
        external_filemanager_path: '/coaster/filemanager/',
        filemanager_title: 'Responsive Filemanager'
    });

    $('input.datetime').datetimepicker({
        format: 'Y-m-d H:i:s'
    });

    $('select.select2').select2({
        width: '100%'
    });

    $('select.select2-p').select2({
        width: '100%',
        minimumInputLength: 3
    });

    $('.length-guide').on('input', function () {

        var min = parseInt($(this).data('min'));
        var max = parseInt($(this).data('max'));
        min = min < 0 ? 0 : min;
        max = max <= 0 ? 1 : max;

        var wrapEl = $(this).parent();
        var barEl = wrapEl.find('.progress-bar').first();

        if (!barEl.length) {
            wrapEl.append(
                '<div class="row pt-2" style="padding: 0 ;">' +
                '<div class="col-3 col-lg-2 text-center"></div>' +
                '<div class="col-9 col-lg-10">' +
                '<div class="progress" style="height: 5px; margin: 7px 0;">' +
                '<div class="progress-bar"></div>' +
                '</div>' +
                '</div>' +
                '</div>'
            );
            barEl = wrapEl.find('.progress-bar').first();
        }

        var numberEl = wrapEl.find('.text-center').first();

        var length = $(this).val().length;

        var barWidth = length/max * 100;
        barWidth = barWidth > 100 ? 100 : barWidth;

        barEl.css('width', barWidth + '%');
        if (length >= min && length <= max) {
            barEl.addClass('bg-success');
            barEl.removeClass('bg-danger');
        } else {
            barEl.addClass('bg-danger');
            barEl.removeClass('bg-success');
        }

        var lengthText;
        if (length > max) {
            lengthText = 'Too Long';
        } else if (length < min) {
            lengthText = 'Too Short';
        } else {
            lengthText = 'Good';
        }
        numberEl.text(length + ' (' + lengthText + ')');

    }).trigger('input');

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.fn.fileinputThemes.fa = {
        fileActionSettings: {
            removeIcon: '<i class="fa fa-trash"></i>',
            uploadIcon: '<i class="fa fa-upload"></i>',
            uploadRetryIcon: '<i class="fa fa-redo-alt"></i>',
            downloadIcon: '<i class="fa fa-download"></i>',
            zoomIcon: '<i class="fa fa-search-plus"></i>',
            dragIcon: '<i class="fa fa-arrows-alt"></i>',
            indicatorNew: '<i class="fa fa-plus-circle text-warning"></i>',
            indicatorSuccess: '<i class="fa fa-check-circle text-success"></i>',
            indicatorError: '<i class="fa fa-exclamation-circle text-danger"></i>',
            indicatorLoading: '<i class="fa fa-hourglass text-muted"></i>',
            indicatorPaused: '<i class="fa fa-pause text-info"></i>'
        },
        layoutTemplates: {
            fileIcon: '<i class="fa fa-file kv-caption-icon"></i> '
        },
        previewZoomButtonIcons: {
            prev: '<i class="fa fa-caret-left fa-lg"></i>',
            next: '<i class="fa fa-caret-right fa-lg"></i>',
            toggleheader: '<i class="fa fa-window-maximize"></i>',
            fullscreen: '<i class="fa fa-window-restore"></i>',
            borderless: '<i class="fa fa-compress"></i>',
            close: '<i class="fa fa-times-circle"></i>'
        },
        previewFileIcon: '<i class="fa fa-file"></i>',
        browseIcon: '<i class="fa fa-folder-open"></i>',
        removeIcon: '<i class="fa fa-trash"></i>',
        cancelIcon: '<i class="fa fa-ban"></i>',
        pauseIcon: '<i class="fa fa-pause"></i>',
        uploadIcon: '<i class="fa fa-upload"></i>',
        msgValidationErrorIcon: '<i class="fa fa-exclamation-circle"></i> '
    };

    $.extend( $.fn.dataTableExt.oSort, {
        "datetime-pre": function ( date ) {

            if (!date) {
                return 0;
            }

            var dateParts = date.split(/\s/);
            var eu_date = dateParts[1].split(/\//).reverse().join('');
            var eu_time = dateParts[0].replace(/:/g, '');

            return (eu_date + eu_time) * 1;
        },

        "datetime-asc": function ( a, b ) {
            return ((a < b) ? -1 : ((a > b) ? 1 : 0));
        },

        "datetime-desc": function ( a, b ) {
            return ((a < b) ? 1 : ((a > b) ? -1 : 0));
        }
    });

    $.extend( $.fn.dataTableExt.oSort, {
        "price-pre": function (price) {

            if (!price) {
                return 0;
            }

            return parseFloat(price.replace(/[^0-9.]/g, ''));
        }
    });

});