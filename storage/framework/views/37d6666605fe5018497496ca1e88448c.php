<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Merchant Dashboard - <?php echo e($merchant->name); ?>

    </title>

    <style>
        * {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    background: #eef2f7;
    color: #1e293b;
}

.dashboard-page {
    min-height: 100vh;
    padding: 20px;
}


/* =========================
   HEADER
========================= */

.dashboard-header {
    min-height: 64px;
    background: #172033;
    color: #ffffff;

    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 0 24px;

    border-radius: 8px;

    margin-bottom: 18px;
}

.dashboard-header h1 {
    display: inline;
    margin: 0;

    font-size: 21px;
    font-weight: 700;
}

.dashboard-header strong {
    font-size: 20px;
}

.header-divider {
    margin: 0 10px;
    opacity: 0.6;
}

.wireframe-label {
    font-size: 9px;
    letter-spacing: 1px;
    color: #94a3b8;
}


/* =========================
   SUMMARY CARDS
========================= */

.summary-grid {
    display: grid;

    grid-template-columns:
        repeat(3, minmax(0, 1fr));

    gap: 16px;

    margin-bottom: 18px;
}

.summary-card {
    background: #ffffff;

    border: 1px solid #cbd5e1;

    border-radius: 6px;

    min-height: 112px;

    padding: 17px 18px;

    box-shadow:
        0 2px 5px rgba(15, 23, 42, 0.06);
}

.usage-card {
    border-left: 5px solid #3478c9;
}

.revenue-card {
    border-left: 5px solid #df8a00;
}

.plan-card {
    border-left: 5px solid #26915d;
}

.card-label {
    color: #64748b;

    font-size: 12px;

    margin-bottom: 10px;
}

.card-value {
    color: #1e293b;

    font-size: 18px;

    font-weight: 700;
}

.card-subtext {
    margin-top: 6px;

    color: #64748b;

    font-size: 11px;
}

.progress-wrapper {
    display: flex;

    align-items: center;

    gap: 10px;

    margin-top: 10px;
}

.progress-wrapper > span {
    font-size: 11px;

    color: #64748b;

    min-width: 40px;
}

.progress-bar {
    height: 7px;

    flex: 1;

    background: #e2e8f0;

    border-radius: 10px;

    overflow: hidden;
}

.progress-fill {
    height: 100%;

    background: #3478c9;

    border-radius: 10px;
}


/* =========================
   MAIN GRID
========================= */

.dashboard-grid {
    display: grid;

    grid-template-columns:
        minmax(0, 2fr)
        minmax(280px, 1fr);

    gap: 16px;
}


/* =========================
   PANELS
========================= */

.dashboard-panel {
    background: #ffffff;

    border: 1px solid #cbd5e1;

    border-radius: 6px;

    box-shadow:
        0 2px 5px rgba(15, 23, 42, 0.05);

    overflow: hidden;
}

.panel-title {
    padding: 13px 15px;

    font-size: 13px;

    font-weight: 700;

    color: #1e293b;
}

.panel-title span {
    font-weight: 400;

    color: #64748b;
}


/* =========================
   CUSTOMERS TABLE
========================= */

.customers-panel {
    min-height: 230px;
}

.table-wrapper {
    padding: 0 12px 14px;
}

table {
    width: 100%;

    border-collapse: collapse;

    font-size: 11px;
}

thead {
    background: #e2e8f0;
}

th {
    padding: 7px 8px;

    text-align: left;

    color: #475569;

    font-weight: 700;

    border: 1px solid #b8c4d3;
}

td {
    padding: 7px 8px;

    border-left: 1px solid #b8c4d3;
    border-right: 1px solid #b8c4d3;
    border-bottom: 1px solid #b8c4d3;
}

tbody tr:hover {
    background: #f8fafc;
}

.usage-percentage {
    display: flex;

    align-items: center;

    gap: 8px;
}

.mini-progress {
    width: 55px;

    height: 5px;

    background: #e2e8f0;

    border-radius: 10px;

    overflow: hidden;
}

.mini-progress-fill {
    height: 100%;

    background: #3478c9;
}

.empty-state {
    text-align: center;

    color: #94a3b8;

    padding: 20px;
}


/* =========================
   CHURN
========================= */

.churn-panel {
    background: #fff5f5;

    border-color: #dc4444;

    padding-bottom: 12px;
}

.churn-title {
    color: #a82c2c;
}

.churn-title span {
    color: #a82c2c;
}

.risk-item {
    padding: 7px 15px;

    font-size: 11px;

    display: flex;

    justify-content: space-between;

    border-top: 1px solid #f2cccc;
}

.risk-name {
    color: #334155;
}

.risk-drop {
    color: #b42323;

    font-weight: 700;
}

.no-risk {
    padding: 10px 15px;

    color: #21734a;

    font-size: 11px;
}


/* =========================
   CHART
========================= */

.chart-panel {
    min-height: 290px;
}

.chart-container {
    height: 235px;

    padding: 5px 10px 10px;
}

#usageChart {
    display: block;

    width: 100%;

    height: 100%;
}


/* =========================
   SYSTEM STATUS
========================= */

.status-panel {
    background: #eff9ff;

    border-color: #2493c2;

    padding-bottom: 10px;
}

.status-item {
    display: flex;

    gap: 9px;

    padding: 7px 15px;
}

.status-dot {
    width: 8px;

    height: 8px;

    border-radius: 50%;

    background: #2493c2;

    margin-top: 4px;

    flex-shrink: 0;
}

.status-item strong {
    font-size: 11px;

    color: #334155;
}

.status-item p {
    margin: 2px 0 0;

    font-size: 10px;

    line-height: 1.4;

    color: #64748b;
}


/* =========================
   RESPONSIVE
========================= */

@media (max-width: 900px) {

    .summary-grid {
        grid-template-columns: 1fr;
    }

    .dashboard-grid {
        grid-template-columns: 1fr;
    }

}

@media (max-width: 600px) {

    .dashboard-page {
        padding: 10px;
    }

    .dashboard-header {
        padding: 12px 15px;

        align-items: flex-start;

        flex-direction: column;

        gap: 8px;
    }

    .dashboard-header h1 {
        font-size: 17px;
    }

    .dashboard-header strong {
        font-size: 16px;
    }

    .wireframe-label {
        display: none;
    }

}
    </style>
</head>

<body>

<div class="dashboard-page">

    
    <header class="dashboard-header">

        <div>
            <h1>Merchant Dashboard</h1>

            <span class="header-divider">—</span>

            <strong><?php echo e($merchant->name); ?></strong>
        </div>

        <span class="wireframe-label">
            SUBSCRIPTION BILLING
        </span>

    </header>


    
    <section class="summary-grid">

        
        <div class="summary-card usage-card">

            <div class="card-label">
                Current Cycle Usage
            </div>

            <div class="card-value">
                <?php echo e(number_format($dashboard['current_cycle']['usage'])); ?>

                /
                <?php echo e(number_format($dashboard['current_cycle']['included_units'])); ?>

                units
            </div>

            <div class="progress-wrapper">

                <div class="progress-bar">

                    <div
                        class="progress-fill"
                        style="width: <?php echo e(min($dashboard['current_cycle']['usage_percentage'], 100)); ?>%">
                    </div>

                </div>

                <span>
                    <?php echo e(number_format($dashboard['current_cycle']['usage_percentage'], 1)); ?>%
                </span>

            </div>

        </div>


        
        <div class="summary-card revenue-card">

            <div class="card-label">
                Projected Overage Revenue
            </div>

            <div class="card-value">

                ₹<?php echo e(number_format(
                    $dashboard['projected_overage_revenue'],
                    2
                )); ?>


            </div>

        </div>


        
        <div class="summary-card plan-card">

            <div class="card-label">
                Active Plan
            </div>

            <?php if($dashboard['active_plan']): ?>

                <div class="card-value">
                    <?php echo e($dashboard['active_plan']['name']); ?>

                    — 
                    <?php echo e(ucfirst($dashboard['active_plan']['billing_cycle'])); ?>

                </div>

                <div class="card-subtext">
                    <?php echo e($dashboard['active_plan']['subscription_count']); ?>

                    active subscription(s)
                </div>

            <?php else: ?>

                <div class="card-value">
                    No active plan
                </div>

            <?php endif; ?>

        </div>

    </section>


    
    <section class="dashboard-grid">


        
        <div class="dashboard-panel customers-panel">

            <div class="panel-title">
                Top 5 Customers by Usage
                <span>(this cycle)</span>
            </div>

            <div class="table-wrapper">

                <table>

                    <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Usage</th>
                        <th>% of Allowance</th>
                    </tr>
                    </thead>

                    <tbody>

                    <?php $__empty_1 = true; $__currentLoopData = $dashboard['top_customers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                        <tr>

                            <td>
                                <?php echo e($customer['name']); ?>

                            </td>

                            <td>
                                <?php echo e(number_format($customer['usage'])); ?>

                            </td>

                            <td>

                                <div class="usage-percentage">

                                    <div class="mini-progress">

                                        <div
                                            class="mini-progress-fill"
                                            style="width: <?php echo e(min($customer['usage_percentage'], 100)); ?>%">
                                        </div>

                                    </div>

                                    <span>
                                        <?php echo e(number_format(
                                            $customer['usage_percentage'],
                                            0
                                        )); ?>%
                                    </span>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                        <tr>

                            <td colspan="3"
                                class="empty-state">

                                No usage data available.

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>


        
        <div class="dashboard-panel churn-panel">

            <div class="panel-title churn-title">
                ⚠ Churn Risk
                <span>(usage ↓ &gt;50% MoM)</span>
            </div>

            <?php $__empty_1 = true; $__currentLoopData = $dashboard['churn_risk']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                <div class="risk-item">

                    <div class="risk-name">
                        <?php echo e($customer['name']); ?>

                    </div>

                    <div class="risk-drop">
                        <?php echo e(number_format(
                            $customer['drop_percentage'],
                            0
                        )); ?>% drop
                    </div>

                </div>

            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                <div class="no-risk">

                    ✓ No customers with &gt;50% usage drop

                </div>

            <?php endif; ?>

        </div>


        
        <div class="dashboard-panel chart-panel">

            <div class="panel-title">
                Daily Usage Trend
                <span>(last 30 days)</span>
            </div>

            <div class="chart-container">

                <canvas id="usageChart"></canvas>

            </div>

        </div>


        
        <div class="dashboard-panel status-panel">

            <div class="panel-title">
                System status
                <span>(informational)</span>
            </div>

            <div class="status-item">

                <span class="status-dot"></span>

                <div>
                    <strong>Plan pricing cache</strong>
                    <p>
                        <?php echo e($dashboard['system_status']['cache']); ?>

                    </p>
                </div>

            </div>


            <div class="status-item">

                <span class="status-dot"></span>

                <div>
                    <strong>Nightly aggregation job</strong>
                    <p>
                        <?php echo e($dashboard['system_status']['aggregation']); ?>

                    </p>
                </div>

            </div>


            <div class="status-item">

                <span class="status-dot"></span>

                <div>
                    <strong>Usage endpoint</strong>
                    <p>
                        <?php echo e($dashboard['system_status']['rate_limit']); ?>

                    </p>
                </div>

            </div>

        </div>

    </section>

</div>


<script>

    const usageData = <?php echo json_encode($dashboard['daily_usage'], 15, 512) ?>;

    const canvas = document.getElementById('usageChart');

    const ctx = canvas.getContext('2d');

    function drawChart() {

        const width = canvas.clientWidth;
        const height = canvas.clientHeight;

        const ratio = window.devicePixelRatio || 1;

        canvas.width = width * ratio;
        canvas.height = height * ratio;

        ctx.scale(ratio, ratio);

        ctx.clearRect(0, 0, width, height);

        if (!usageData.length) {
            return;
        }

        const padding = {
            top: 20,
            right: 20,
            bottom: 30,
            left: 45
        };

        const chartWidth =
            width - padding.left - padding.right;

        const chartHeight =
            height - padding.top - padding.bottom;

        const maxUsage = Math.max(
            ...usageData.map(item => item.usage),
            1
        );

        // Horizontal grid lines
        ctx.strokeStyle = '#e2e8f0';
        ctx.lineWidth = 1;

        for (let i = 0; i <= 4; i++) {

            const y =
                padding.top +
                (chartHeight / 4) * i;

            ctx.beginPath();

            ctx.moveTo(
                padding.left,
                y
            );

            ctx.lineTo(
                width - padding.right,
                y
            );

            ctx.stroke();
        }

        // Chart line
        ctx.beginPath();

        usageData.forEach((item, index) => {

            const x =
                padding.left +
                (index /
                    Math.max(usageData.length - 1, 1))
                * chartWidth;

            const y =
                padding.top +
                chartHeight -
                (item.usage / maxUsage)
                * chartHeight;

            if (index === 0) {
                ctx.moveTo(x, y);
            } else {
                ctx.lineTo(x, y);
            }

        });

        ctx.strokeStyle = '#3478c9';
        ctx.lineWidth = 2.5;
        ctx.stroke();


        // Points
        usageData.forEach((item, index) => {

            const x =
                padding.left +
                (index /
                    Math.max(usageData.length - 1, 1))
                * chartWidth;

            const y =
                padding.top +
                chartHeight -
                (item.usage / maxUsage)
                * chartHeight;

            ctx.beginPath();

            ctx.arc(
                x,
                y,
                2.5,
                0,
                Math.PI * 2
            );

            ctx.fillStyle = '#3478c9';

            ctx.fill();

        });


        // Date labels
        ctx.fillStyle = '#64748b';

        ctx.font = '10px Arial';

        const labelIndexes = [
            0,
            Math.floor((usageData.length - 1) / 2),
            usageData.length - 1
        ];

        labelIndexes.forEach(index => {

            if (!usageData[index]) {
                return;
            }

            const x =
                padding.left +
                (index /
                    Math.max(usageData.length - 1, 1))
                * chartWidth;

            ctx.fillText(
                usageData[index].label,
                x - 15,
                height - 8
            );

        });

    }

    drawChart();

    window.addEventListener(
        'resize',
        drawChart
    );

</script>

</body>
</html><?php /**PATH D:\Prabu\Projects\subscription-billing-metering\resources\views/dashboard.blade.php ENDPATH**/ ?>