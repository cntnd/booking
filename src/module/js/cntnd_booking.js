/* cntnd_booking */
$(document).ready(function(){
  $('.cntnd_booking-date').click(function(){
    $('.cntnd_booking .table tbody tr').addClass('res_hide');
    $(this).parents('tr').toggleClass('res_hide');
  });
});
