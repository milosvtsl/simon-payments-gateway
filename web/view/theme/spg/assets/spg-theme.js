/**
 * Created by ari on 10/19/2016.
 */

function toggleNavMenu(e) {
    if(e) e.preventDefault();
    var body = document.body;
    if(body.classList.contains('menu-full')) {
        body.classList.remove('menu-full');
        body.classList.add('menu-small');
    } else {
        body.classList.add('menu-full');
        body.classList.remove('menu-small');
    }
}

// Initialize
document.addEventListener("DOMContentLoaded", function(e) {
//     window.onresize = onResize;

    function onResize(e) {
        var height = (e.srcElement || e.currentTarget).innerHeight;
        var width = (e.srcElement || e.currentTarget).innerWidth;
        if(width > 920) { // > height / 1.2
            if(!document.body.classList.contains('layout-horizontal')) {
                document.body.classList.add('layout-horizontal');
                document.body.classList.remove('layout-vertical');
                console.log("Changing body layout to: layout-horizontal");
            }
        } else {
            if(!document.body.classList.contains('layout-vertical')) {
                document.body.classList.add('layout-vertical');
                document.body.classList.remove('layout-horizontal');
                console.log("Changing body layout to: layout-vertical");
            }
        }
    }
//     setTimeout(function(e) {
//         onResize({
//             srcElement: window
//         });
//     }, 100);

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