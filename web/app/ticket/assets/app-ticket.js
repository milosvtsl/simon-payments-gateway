/**
 * Created by ari on 11/16/2016.
 */

function appTicketAction(action, appName) {
    console.log("App Action: ", action, appName);
    switch(action) {
        case 'config':
            break;

        case 'remove':
            break;

        case 'move-up':
            break;
        case 'move-down':
            break;
        case 'move-top':
            break;

        case 'move-bottom':
            break;

        default:
            throw new Error("Invalid App Action: " + action);
    }
}