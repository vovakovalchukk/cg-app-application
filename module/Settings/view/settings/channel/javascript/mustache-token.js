$("#<?= $tableId ?>").on("renderColumn", function(event, cgmustache, template, column, data) {
    data.tokenClass = function() {
        if (data.expiryDate == undefined) {
            return "empty";
        }

        if (data.expiryDate <= 0) {
            return "expired";
        }

        return "";
    };

    data.tokenStatus = function() {
        if (data.expiryDate == undefined) {
            return "-";
        }

        if (data.expiryDate <= 0) {
            return "Expired";
        }

        var timeFrames = {
            Week: 60 * 60 * 24 * 7,
            Day: 60 * 60 * 24,
            Hour: 60 * 60,
            Minute: 60,
            Second: 1
        };

        for (var timeFrame in timeFrames) {
            var time = Math.floor(data.expiryDate / timeFrames[timeFrame]);
            if (time == 0) {
                continue;
            }

            var string = time + " " + timeFrame;
            if (time > 1) {
                string += "s";
            }
            return string;
        }

        return "Now";
    };
});