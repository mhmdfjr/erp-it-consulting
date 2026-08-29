import Chart from 'chart.js/auto';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

window.Chart = Chart;
window.Alpine = Alpine;

Alpine.start();
Alpine.plugin(collapse);
