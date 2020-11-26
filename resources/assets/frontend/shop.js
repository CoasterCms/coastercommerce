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
}

$(document).ready(function() {

    $.ajaxPrefilter(function (options, originalOptions, xhr) { // this will run before each request
        var token = $('meta[name="csrf-token"]').attr('content');
        if (token) {
            return xhr.setRequestHeader('X-CSRF-TOKEN', token);
        }
    });

    $('#searchform').submit(function(event) {
        event.preventDefault();
        window.location.href = '/search?q='+$('#s').val();
    });

    $('.confirm').click(function () {
        var confirm = $(this).data('confirm');
        confirm = confirm ? ' ' + confirm : '';
        return window.confirm('Are you sure' + confirm + '?');
    });

});