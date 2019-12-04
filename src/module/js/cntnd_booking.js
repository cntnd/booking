/* cntnd_booking */
$(document).ready(function(){
  $('.cntnd_booking-date').click(function(){
    $('.cntnd_booking .table tbody tr').addClass('res_hide');
    $(this).parents('tr').toggleClass('res_hide');
  });

  $('.res_checkbox').click(function(){
    var res = $(this).children('input');
    if (res.is(':checked')){
      $(this).parents('td').addClass('reserved');
    }
    else {
      $(this).parents('td').removeClass('reserved');
    }
  });

  $('.cntnd_booking-more').click(function(){
    $(this).addClass('hide');
    $('.not-in-range').removeClass('hide');
    $('.cntnd_booking-less').removeClass('hide');
  });

  $('.cntnd_booking-less').click(function(){
    $(this).addClass('hide');
    $('.not-in-range').addClass('hide');
    $('.cntnd_booking-more').removeClass('hide');
  });

  function gatherElements(formId,elementClass){
    var elements=[];
    $('#'+formId+' .'+elementClass).each(function(){
      elements.push($(this).attr('name'));
    });
    return window.btoa(JSON.stringify(elements));
  }

  function validateDates(){
    var dates = $('.cntnd_booking-checkbox:checkbox:checked');
    if (dates.length>0){
      var valid=true;
      var interval_ms=$('#cntnd_booking-reservation').data('interval')*60; // interval is in sec and timestamp is in ms
      var old;
      dates.each(function(){
        var date = $(this).val();
        if (old!==undefined){
          var diff = date-old;
          if (diff>interval_ms){
            $('.cntnd_booking-validation-dates').show();
            valid=false;
          }
          var options = { year: 'numeric', month: 'long', day: 'numeric' };
          var d1 = new Date(old*1000).toLocaleDateString('de', options);
          var d2 = new Date(date*1000).toLocaleDateString('de', options);
          if (d1!==d2){
            $('.cntnd_booking-validation-days').show();
            valid=false;
          }
        }
        old = $(this).val();
      });
      return valid;
    }
    $('.cntnd_booking-validation-dates').show();
    return false;
  }

  $('#cntnd_booking-reservation').submit(function() {
    $('.cntnd_booking-validation').addClass('hide');  
    $('.cntnd_booking-validation-required').hide();
    $('.cntnd_booking-validation-dates').hide();
    $('.cntnd_booking-validation-days').hide();
    var required = $('#cntnd_booking-reservation .required').filter(function(){
      return ($(this).val()==='');
    });
    if (!validateDates() || required.length>0){
      $('.cntnd_booking-validation').removeClass('hide');
      if (required.length>0){
        $('.cntnd_booking-validation-required').show();
      }
      return false;
    }
    $('#cntnd_booking-fields').val(gatherElements('cntnd_booking-reservation','form-control'));
    $('#cntnd_booking-required').val(gatherElements('cntnd_booking-reservation','required'));
    return true;
  });
});
