<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
$(document).ready(function(){
  $('.cntnd_booking_daterange').daterangepicker({"locale": {
        "format": "DD.MM.YYYY",
        "separator": " - ",
        "applyLabel": "Auswählen",
        "cancelLabel": "Abbrechen",
        "fromLabel": "von",
        "toLabel": "bis",
        "customRangeLabel": "Custom",
        "weekLabel": "W",
        "daysOfWeek": [
            "So",
            "Mo",
            "Di",
            "Mi",
            "Do",
            "Fr",
            "Sa"
        ],
        "monthNames": [
            "Januar",
            "Februar",
            "März",
            "April",
            "Mai",
            "Juni",
            "Juli",
            "August",
            "September",
            "Oktober",
            "November",
            "Dezember"
        ],
        "firstDay": 1
    }});
});
</script>
