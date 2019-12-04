<script>
$(document).ready(function(){
  $('.cntnd_booking-admin-choose').click(function(){
    $('.card.cntnd_booking').removeClass('focus');
    var res = $(this).parents('.card.cntnd_booking');
    var admin = $('.cntnd_booking-admin-action');
    var offset = $('.cntnd_booking-admin-action h5').outerHeight(true);
    admin.width(admin.width());
    admin.css('position','absolute').css('top',(res.position().top-offset));
    res.addClass('focus');
    $('#cntnd_booking-admin input[name=resid]').val(res.data('resid'));
  });

  $('.cntnd_booking-admin-cancel').click(function(){
    $('.card.cntnd_booking').removeClass('focus');
    $('.cntnd_booking-admin-action').css('position','static');
    $('#cntnd_booking-admin input[name=resid]').val('');
  });
});
</script>
