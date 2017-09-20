$(document).ready(function() {
    $.get("/sales/orderCounts", function(data) {
        console.log(data);
        let canvas = document.getElementById("salesChart").getContext('2d');
        let myChart = new Chart(canvas, {
            type: 'line',
            data: {
                datasets: [{
                    label: 'ebay',
                    data: data.data,
                    borderColor: 'blue',
                    fill: false
                }]
            },
            options: {
                elements: {
                    line: {
                        tension: 0, // disables bezier curves
                    }
                },
                responsive: false,
                scales: {
                    xAxes: [{
                        type:'time',
                        bonds: 'data',
                        distribution: 'linear',
                        ticks: {
                            source: 'data'
                        },
                        time: {
                            unit: 'day',
                            displayFormats: {
                                day: 'll'
                            }
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            beginAtZero:true
                        }
                    }]
                }
            }
        });
    });
});
