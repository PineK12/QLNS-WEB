<!-- Footer -->
<footer class="main-footer">
    <strong>Copyright &copy; 2024 <a href="#">Hệ thống Quản lý Nhân viên</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
        <b>Version</b> 1.0.0
    </div>
</footer>
</div>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>

<script>
    // Real Data Chart
    const ctx = document.getElementById('attendanceChart');
    if (ctx) {
        // Nhận dữ liệu từ PHP
        const labels = <?php echo $chartLabelsJSON; ?>;
        const data = <?php echo $chartDataJSON; ?>;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels, // Dùng biến labels từ PHP
                datasets: [{
                    label: 'Số người đi làm',
                    data: data, // Dùng biến data từ PHP
                    borderColor: 'rgb(40, 167, 69)', // Màu xanh lá cho đẹp
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.3, // Làm đường cong mềm mại hơn
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1 // Chỉ hiện số nguyên (người)
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
</script>

</body>

</html>