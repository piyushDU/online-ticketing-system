import './bootstrap';

Echo.channel('purchase')
    .listen('PurchaseTicket', (e) => {
        console.log(e.order);
    });


// window.Echo.channel('purchase')
//     .listen('.App\\Events\\PurchaseTicket', (e) => {
//         console.log('New notification:', e.message);
//         // Handle the notification display
//     });
