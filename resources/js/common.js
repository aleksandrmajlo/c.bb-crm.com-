
$('body').on('click','.icon-caret',function (){
    $(this).parents('.list-unstyled').toggleClass('open-list')
    $(this).parents('.list-unstyled-fields').toggleClass('open-list')
})