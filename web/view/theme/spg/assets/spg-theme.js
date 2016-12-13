/**
 * Created by ari on 10/19/2016.
 */

function toggleNavMenu(e) {
    if(e) e.preventDefault();
    var body = document.body;
    if(body.classList.contains('layout-full')) {
        body.classList.remove('layout-full');
        body.classList.add('layout-narrow');
    } else {
        body.classList.add('layout-full');
        body.classList.remove('layout-narrow');
    }

    localStorage.setItem('layout-narrow', body.classList.contains('layout-narrow') ? '1' : '0');
}

// Initialize
document.addEventListener("DOMContentLoaded", function(e) {

    if(localStorage.getItem('layout-narrow') === '1') {
        document.body.classList.remove('layout-full');
        document.body.classList.add('layout-narrow');
    }
    function onResize(e) {
        if(document.body.classList.contains('layout-vertical'))
            return;

        var height = (e.srcElement || e.currentTarget).innerHeight;
        var width = (e.srcElement || e.currentTarget).innerWidth;
        if(width >= 920) { // > height / 1.2
            if(document.body.classList.contains('layout-narrow')) {
                document.body.classList.remove('layout-narrow');
                document.body.classList.add('layout-full');
                console.log("Changing body class to: layout-full");
            }
        } else {
            if(!document.body.classList.contains('layout-narrow')) {
                document.body.classList.add('layout-narrow');
                document.body.classList.remove('layout-full');
                console.log("Changing body class to: layout-narrow");
            }
        }
    }
    setTimeout(function(e) {
        onResize({
            srcElement: window
        });
    }, 100);
    window.onresize = onResize;

    switch(location.host.toLowerCase()) {
        case 'localhost':
            break;

        case 'access.simonpayments.com:81':
        case 'access.simonpayments.com':
            // Force SSL
            if (location.protocol != 'https:')
                location.href = 'https:' + window.location.href.substring(window.location.protocol.length);
            break;
    }


});