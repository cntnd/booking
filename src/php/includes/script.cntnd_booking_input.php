<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
    $(document).ready(function () {
        $('.blocked_day').click(function () {
            var blocked_days = {};
            $('.blocked_day').each(function (index) {
                blocked_days[$(this).attr('data-day')] = $(this).is(':checked');
            });
            $('#reset_config').val(window.btoa(JSON.stringify(blocked_days)));
        });

        $('.cntnd_booking_daterange').daterangepicker({
            "locale": {
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
            }
        });

        $('#recurrent').change(function () {
            $("#interval").prop("disabled", !$(this).is(':checked'))
            if ($("#interval").is(':checked')) {
                $("#interval_configuration").prop("disabled", !$(this).is(':checked'))
            }
        });

        $('#interval').change(function () {
            $("#interval_configuration").prop("disabled", !$(this).is(':checked'))
        });
    });
</script>
