$('#burger-icon').on('click', function(event) {
    if ($('.left-menu').css('visibility') === 'hidden'){
         $('.left-menu').css({
              'visibility': 'visible',
         });
    }
    else {
         $('.left-menu').css({
              'visibility': 'hidden',
         })
    }

});


















// Код работает, но оказался не нужным
// $('#delete-button').on('click', function (event){
//     action_confirm(event, 'Вы уверены, что хотите удалить данную новость?');
// });
//
// $('#logout').on('click', function(event){
//     action_confirm(event, 'Вы уверены, что хотите выйти из аккаунта?');
// });
//
// function action_confirm (event, message){
//     event.preventDefault();
//     let result = confirm(message);
//     if (result) {
//         window.location.href = event.target.href;
//     }
// }