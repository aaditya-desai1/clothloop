 // Get the context of the canvas element
 var productSalesChartCtx = document.getElementById('product-sales-chart').getContext('2d');
 var revenueChartCtx = document.getElementById('revenue-chart').getContext('2d');
 var customerEngagementChartCtx = document.getElementById('customer-engagement-chart').getContext('2d');

 // Create the charts
 var productSalesChart = new Chart(productSalesChartCtx, {
   type: 'bar',
   data: {
     labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
     datasets: [{
       label: 'Product Likes',
       data: [10, 20, 15, 30, 25],
       backgroundColor: [
         'rgba(255, 99, 132, 0.2)',
         'rgba(54, 162, 235, 0.2)',
         'rgba(255, 206, 86, 0.2)',
         'rgba(75, 192, 192, 0.2)',
         'rgba(153, 102, 255, 0.2)'
       ],
       borderColor: [
         'rgba(255, 99, 132, 1)',
         'rgba(54, 162, 235, 1)',
         'rgba(255, 206, 86, 1)',
         'rgba(75, 192, 192, 1)',
         'rgba(153, 102, 255, 1)'
       ],
       borderWidth: 1
     }]
   },
   options: {
     scales: {
       yAxes: [{
         ticks: {
           beginAtZero: true
         }
       }]
     }
   }
 });

 var revenueChart = new Chart(revenueChartCtx, {
   type: 'line',
   data: {
     labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
     datasets: [{
       label: 'Views',
       data: [1000, 2000, 1500, 3000, 2500],
       backgroundColor: [
         'rgba(255, 99 , 132, 0.2)'
       ],
       borderColor: [
         'rgba(255, 99, 132, 1)'
       ],
       borderWidth: 1
     }]
   },
   options: {
     scales: {
       yAxes: [{
         ticks: {
           beginAtZero: true
         }
       }]
     }
   }
 });

 var customerEngagementChart = new Chart(customerEngagementChartCtx, {
   type: 'pie',
   data: {
     labels: ['Facebook', 'Instagram', 'Twitter', 'Email'],
     datasets: [{
       label: 'Customer Engagement',
       data: [40, 30, 20, 10],
       backgroundColor: [
         'rgba(255, 99, 132, 0.2)',
         'rgba(54, 162, 235, 0.2)',
         'rgba(255, 206, 86, 0.2)',
         'rgba(75, 192, 192, 0.2)'
       ],
       borderColor: [
         'rgba(255, 99, 132, 1)',
         'rgba(54, 162, 235, 1)',
         'rgba(255, 206, 86, 1)',
         'rgba(75, 192, 192, 1)'
       ],
       borderWidth: 1
     }]
   },
   options: {
     scales: {
       yAxes: [{
         ticks: {
           beginAtZero: true
         }
       }]
     }
   }
 });