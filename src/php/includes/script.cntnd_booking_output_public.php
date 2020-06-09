<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.25.3/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.25.3/locale/de-ch.js"></script>
<script src="https://cdn.jsdelivr.net/npm/knockout@3.5.0/build/output/knockout-latest.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/knockout.mapping@2.4.3/knockout.mapping.min.js"></script>

<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>
<script>
$(document ).ready(function(){
    /* cntnd_booking */
    function getDatesDiff(start_date, end_date, date_format = "YYYY-MM-DD"){
        const getDateAsArray = date => {
            return moment(date.split(/\D+/), date_format);
        };
        const diff = getDateAsArray(end_date).diff(getDateAsArray(start_date), "days") + 1;
        const dates = [];
        for (let i = 0; i < diff; i++) {
            const nextDate = getDateAsArray(start_date).add(i, "day");
            // WEEKEND
            const isWeekEndDay = nextDate.isoWeekday() > 5;
            //if (!isWeekEndDay)
            dates.push(nextDate.format(date_format))
        }
        return dates;
    };

    function getFirstDay(date = moment()){
        var startOfMonth = moment(date).startOf('month');
        var displayFirstDay = startOfMonth.isoWeekday();
        if (displayFirstDay===1){
            displayFirstDay=8;
        }
        return startOfMonth.subtract(displayFirstDay-1,'days');
    }

    function getLastDay(date = moment()){
        var endOfMonth   = moment(date).endOf('month');
        var displayLastDay = endOfMonth.isoWeekday();
        if (displayLastDay===7){
            displayLastDay=0;
        }
        return endOfMonth.add(7-displayLastDay,'days');
    }

    function getLastDaySimplified(date = moment()){
        return getFirstDay(date).add(6,'weeks').subtract(1,'days');
    }

    function dateToInt(date){
        return parseInt(date.replace(/[-]/g,''));
    }

    function Reservation(){
        var self = this;

        self.start = ko.observable();
        self.end = ko.observable();
        self.description = ko.observable();
        self.endDateAfterStart = ko.observable(false);

        self.reservation = ko.computed(function(){
            return ko.toJSON({
                startDate: self.start(),
                endDate: self.end(),
                description: self.description()
            });
        }, this);

        self.date = function(date,isEndDate,endDateAfterStart){
            self.endDateAfterStart(endDateAfterStart);

            if (isEndDate){
                self.end(date);
            }
            else if (self.start()===undefined){
                self.start(date);
            }
            else if ((self.start()!==undefined && self.end()!==undefined)){
                self.end(undefined);
                self.start(date);
            }
            else {
                var currentStart = dateToInt(self.start());
                var parsedDate = dateToInt(date);
                if (currentStart<parsedDate){
                    self.end(date);
                }
                else {
                    self.end(self.start());
                    self.start(date);
                }
            }
        };

        self.dates = ko.computed(function(){
            if (self.start()!==undefined &&  self.end()!==undefined){
                return getDatesDiff(self.start(), self.end());
            }
            return [];
        }, this);

        self.onlyStartDate = ko.computed(function(){
            return (self.start()!==undefined && self.end()===undefined);
        }, this);

        self.onlyEndDate = ko.computed(function(){
            return (self.end()!==undefined && self.start()===undefined);
        }, this);

        self.hasDate = ko.computed(function(){
            return (self.end()!==undefined || self.start()!==undefined);
        }, this);

        self.isReservation = function(date){
            if (self.start()!==undefined &&  self.end()!==undefined){
                var startDate = dateToInt(self.start());
                var endDate = dateToInt(self.end());
                var parsedDate = dateToInt(date);
                if (parsedDate>=startDate && parsedDate<=endDate){
                    return true;
                }
            }
            return false;
        };

        self.isEndDate = function(date){
            if (self.end()!==undefined){
                var endDate = dateToInt(self.end());
                var parsedDate = dateToInt(date);
                if (endDate===parsedDate){
                    return true;
                }
            }
            return false;
        };

        self.isStartDate = function(date){
            if (self.start()!==undefined){
                var startDate = dateToInt(self.start());
                var parsedDate = dateToInt(date);
                if (startDate===parsedDate){
                    return true;
                }
            }
            return false;
        };

        self.isStartDateOnly = function(date){
            if (self.start()!==undefined && self.end()===undefined){
                var startDate = dateToInt(self.start());
                var parsedDate = dateToInt(date);
                if (startDate===parsedDate){
                    return true;
                }
            }
            return false;
        };

        self.isEndDateOnly = function(date){
            if (self.end()!==undefined && self.start()===undefined){
                var endDate = dateToInt(self.end());
                var parsedDate = dateToInt(date);
                if (endDate===parsedDate){
                    return true;
                }
            }
            return false;
        };
    };

    function BookingDate(date,booking = undefined){
        var self = this;

        self.date = ko.observable(date);
        self.isStartDate = ko.observable(false);
        self.isEndDate = ko.observable(false);
        self.isBooking = ko.observable(false);
        self.description = ko.observable();

        if (booking!==undefined){
            self.isStartDate = ko.observable(booking.startDate);
            self.isEndDate = ko.observable(booking.endDate);
            self.isBooking = ko.observable(booking.isBooking);
            self.description = ko.observable(booking.description);
        }
    }

    function BookingViewModel(){
        var self = this;

        self.reservation = ko.observable(new Reservation());

        self.bookings = ko.observableArray(Array.from(bookings.values()));
        self.bookingDates = ko.observableArray(Array.from(bookings.keys()));

        self.book = function(booking){
            if (!booking.isBooking() || (booking.isStartDate() || booking.isEndDate())){
                var endDateAfterStart = (booking.isBooking() && booking.isEndDate());

                if (self.reservation().onlyEndDate() &&
                    (dateToInt(booking.date()) > dateToInt(self.reservation().end()))){
                    // if reservation has only End date, first date is at a date with booking start
                    alert('ERROR');
                }
                else if (self.reservation().endDateAfterStart() &&
                    (dateToInt(booking.date()) < dateToInt(self.reservation().start()))){
                    // if reservation has endDateAfterStart true, then end date has to be after(!) start date (first date), while there is a booking before
                    alert('ERROR');
                }
                else if (self.reservation().hasDate() && checkBookings(booking.date())){
                    // if reservation has a booking in between
                    alert('ERROR');
                }
                else {
                    var isEndDate = booking.isStartDate();
                    self.reservation().date(booking.date(),isEndDate,endDateAfterStart);
                }
            }
        };

        function checkBookings(date){
            var reservationDate = self.reservation().start();
            if (reservationDate===undefined){
                reservationDate = self.reservation().end();
            }

            var startDate=reservationDate;
            var endDate=date;
            if (dateToInt(reservationDate) > dateToInt(date)){
                startDate=date;
                endDate=reservationDate;
            }

            var reservations = getDatesDiff(startDate, endDate);
            return reservations.some(d => self.bookingDates().includes(d));
        };

        self.date = ko.observable(moment());

        self.dates = ko.computed(function(){
            var firstDay = getFirstDay(self.date()).format('YYYY-MM-DD');
            var lastDay = getLastDaySimplified(self.date()).format('YYYY-MM-DD');
            return getDatesDiff(firstDay, lastDay).map(function(date){
                return new BookingDate(date,bookings.get(date));
            });
        }, this);

        self.hasBookingEnd = function(booking){
            var hasBooking = 'none';

            if (booking.isEndDate()){
                hasBooking = ' booking__end';
            }
            else if (self.reservation().isReservation(booking.date())) {
                if (self.reservation().isEndDate(booking.date())){
                    hasBooking = ' reservation booking__end';
                }
                else if (!self.reservation().isStartDate(booking.date())){
                    hasBooking = ' reservation';
                }
            }
            else if (self.reservation().isEndDateOnly(booking.date())){
                hasBooking = ' booking__end booking__click';
            }
            else if (booking.isBooking() && !booking.isStartDate()) {
                hasBooking = '';
            }

            return hasBooking;
        };

        self.hasBookingStart = function(booking){
            var hasBooking = 'none';
            if (self.reservation().isStartDateOnly(booking.date())){
                hasBooking = ' booking__click';
                if (booking.isEndDate()){
                    hasBooking += ' booking__start';
                }
            }
            else if (self.reservation().isStartDate(booking.date())){
                hasBooking = ' reservation booking__start';
            }
            else if (booking.isStartDate()){
                hasBooking = ' booking__start';
            }
            return hasBooking;
        };

        self.month = ko.computed(function(){
            return self.date().format("MMMM YYYY");
        }, this);

        self.nextMonth = function(){
            self.date(self.date().add(1,'month'));
        };

        self.previousMonth = function(){
            self.date(self.date().subtract(1,'month'));
        };

        self.currentMonth = function(){
            self.date(moment());
        };

        self.isCurrentMonth = function(currentDate){
            if (moment(currentDate).format('M')===self.date().format('M')){
                return 'day__month-current';
            }
            return '';
        };

        self.reset = function(){
            self.reservation(new Reservation());
            self.currentMonth();
        };
    };

    ko.bindingHandlers.dayOfMonth = {
        init: function(element, valueAccessor, allBindings, viewModel, bindingContext) {
            var value = valueAccessor();
            var strDate = moment(value).format('D');
            $(element).text(strDate);

            var isWeekEndDay = moment(value).isoWeekday() > 5;
            if (isWeekEndDay){
                $(element).addClass('weekend');
            }
            else {
                $(element).removeClass('weekend');
            }
        },
        update: function(element, valueAccessor, allBindings, viewModel, bindingContext) {
            var value = valueAccessor();
            var strDate = moment(value).format('D');
            $(element).text(strDate);

            var isWeekEndDay = moment(value).isoWeekday() > 5;
            if (isWeekEndDay){
                $(element).addClass('weekend');
            }
            else {
                $(element).removeClass('weekend');
            }
        }
    };

    ko.bindingHandlers.tooltip = {
        init: function(element, valueAccessor) {
            var value = valueAccessor();
            if (value!=undefined){
                tippy(element, {
                    content: value,
                });
            }
        }
    };

    ko.applyBindings(new BookingViewModel());
});
</script>
