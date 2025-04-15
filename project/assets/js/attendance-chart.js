document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('attendanceChart');
    if (!ctx) return;

    const chartData = JSON.parse(ctx.getAttribute('data-chart'));
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.map(item => item.student),
            datasets: [{
                label: 'Attendance Percentage',
                data: chartData.map(item => item.percentage),
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Attendance Percentage'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Students'
                    }
                }
            }
        }
    });
}); 