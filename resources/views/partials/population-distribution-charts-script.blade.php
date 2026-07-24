<script>
(function () {
    @php
        $populationDistributionPayload = $populationDistribution ?? [
            'age_labels' => ['0–17', '18–35', '36–59', '60+'],
            'age_by_gender' => ['all' => [0, 0, 0, 0], 'male' => [0, 0, 0, 0], 'female' => [0, 0, 0, 0]],
            'gender' => ['labels' => ['Male', 'Female'], 'values' => [0, 0], 'colors' => ['#4A90E2', '#EC4899']],
        ];
    @endphp
    const populationDistribution = @json($populationDistributionPayload);

    const ageCanvas = document.getElementById('ageDistributionChart');
    const genderCanvas = document.getElementById('genderDistributionChart');
    if (!ageCanvas && !genderCanvas) {
        return;
    }

    const isDark = () => document.documentElement.classList.contains('dark');
    const tickColor = () => (isDark() ? 'rgba(255,255,255,0.65)' : 'rgba(30,41,59,0.65)');
    const gridColor = () => (isDark() ? 'rgba(255,255,255,0.08)' : 'rgba(15,23,42,0.06)');
    const chartDefaults = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(15, 23, 42, 0.92)',
                titleFont: { family: 'Manrope', size: 12, weight: '600' },
                bodyFont: { family: 'Manrope', size: 12 },
                padding: 12,
                cornerRadius: 10,
                displayColors: true,
            },
        },
    };

    const ageByGender = populationDistribution.age_by_gender || {};
    const ageLabels = populationDistribution.age_labels || ['0–17', '18–35', '36–59', '60+'];
    const genderSubtitles = {
        all: 'Showing all genders',
        male: 'Showing male household members',
        female: 'Showing female household members',
    };
    const ageBarColors = {
        all: '#4A90E2',
        male: '#4A90E2',
        female: '#EC4899',
    };

    let ageChart = null;
    let activeAgeGender = 'all';

    function setAgeGenderButtons(active) {
        document.querySelectorAll('[data-age-gender]').forEach((button) => {
            const isActive = button.getAttribute('data-age-gender') === active;
            button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
    }

    function ageValuesFor(gender) {
        const key = ['all', 'male', 'female'].includes(gender) ? gender : 'all';
        return ageByGender[key] || [0, 0, 0, 0];
    }

    function updateAgeDistributionChart() {
        const values = ageValuesFor(activeAgeGender);
        const subtitle = document.getElementById('ageDistributionSubtitle');
        if (subtitle) {
            subtitle.textContent = genderSubtitles[activeAgeGender] || genderSubtitles.all;
        }
        setAgeGenderButtons(activeAgeGender);
        if (!ageChart) {
            return;
        }
        ageChart.data.datasets[0].data = values;
        ageChart.data.datasets[0].backgroundColor = ageBarColors[activeAgeGender] || ageBarColors.all;
        ageChart.data.datasets[0].label = activeAgeGender === 'all'
            ? 'People'
            : (activeAgeGender === 'male' ? 'Male' : 'Female');
        ageChart.update();
    }

    if (ageCanvas && typeof Chart !== 'undefined') {
        ageChart = new Chart(ageCanvas, {
            type: 'bar',
            data: {
                labels: ageLabels,
                datasets: [{
                    label: 'People',
                    data: ageValuesFor('all'),
                    backgroundColor: ageBarColors.all,
                    borderRadius: 10,
                    maxBarThickness: 56,
                }],
            },
            options: {
                ...chartDefaults,
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: tickColor(), font: { family: 'Manrope', size: 11 } },
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            color: tickColor(),
                            font: { family: 'Manrope', size: 11 },
                        },
                        grid: { color: gridColor() },
                    },
                },
            },
        });

        document.querySelectorAll('[data-age-gender]').forEach((button) => {
            button.addEventListener('click', () => {
                activeAgeGender = button.getAttribute('data-age-gender') || 'all';
                updateAgeDistributionChart();
            });
        });

        updateAgeDistributionChart();
    }

    if (genderCanvas && typeof Chart !== 'undefined') {
        const gender = populationDistribution.gender || {};
        new Chart(genderCanvas, {
            type: 'doughnut',
            data: {
                labels: gender.labels || ['Male', 'Female'],
                datasets: [{
                    data: gender.values || [0, 0],
                    backgroundColor: gender.colors || ['#4A90E2', '#EC4899'],
                    borderWidth: 0,
                    hoverOffset: 6,
                }],
            },
            options: {
                ...chartDefaults,
                cutout: '62%',
                plugins: {
                    ...chartDefaults.plugins,
                    legend: { display: false },
                },
            },
        });
    }
})();
</script>
