<template>
  <div class="chart-host">
    <Pie
      v-if="type === 'pie' && hasData"
      ref="chartRef"
      :data="chartData"
      :options="mergedOptions"
    />
    <Bar
      v-else-if="type === 'bar' && hasData"
      ref="chartRef"
      :data="chartData"
      :options="mergedOptions"
    />
    <PolarArea
      v-else-if="type === 'polarArea' && hasData"
      ref="chartRef"
      :data="chartData"
      :options="mergedOptions"
    />
  </div>
</template>

<script>
import { Pie, Bar, PolarArea } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  CategoryScale,
  LinearScale,
  BarElement,
  RadialLinearScale,
} from 'chart.js'

ChartJS.register(
  Title,
  Tooltip,
  Legend,
  ArcElement,
  CategoryScale,
  LinearScale,
  BarElement,
  RadialLinearScale
)

export default {
  name: 'DashboardStatsChart',
  components: { Pie, Bar, PolarArea },
  props: {
    type: {
      type: String,
      default: 'pie',
      validator: (v) => ['pie', 'bar', 'polarArea'].includes(v),
    },
    chartData: {
      type: Object,
      required: true,
    },
    chartOptions: {
      type: Object,
      default: () => ({}),
    },
  },
  computed: {
    hasData() {
      return Array.isArray(this.chartData?.datasets?.[0]?.data)
        && this.chartData.datasets[0].data.length > 0
    },
    mergedOptions() {
      const isBar = this.type === 'bar'
      return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: isBar ? 'top' : 'right',
          },
          tooltip: {
            callbacks: {
              label(ctx) {
                const value = ctx.raw ?? 0
                const data = ctx.dataset.data || []
                const sum = data.reduce((a, b) => a + Number(b), 0)
                const pct = sum > 0 ? ((Number(value) / sum) * 100).toFixed(1) : '0.0'
                const label = ctx.label || ctx.dataset.label || ''
                return `${label}: ${value} (${pct}%)`
              },
            },
          },
        },
        scales: isBar
          ? {
              y: {
                beginAtZero: true,
                ticks: { precision: 0 },
              },
            }
          : undefined,
        ...this.chartOptions,
      }
    },
  },
  methods: {
    toBase64Image() {
      const chart = this.$refs.chartRef?.chart
      if (!chart) {
        return null
      }
      return chart.toBase64Image('image/png', 1)
    },
  },
}
</script>

<style scoped>
.chart-host {
  position: relative;
  width: 100%;
  height: 360px;
}
</style>
