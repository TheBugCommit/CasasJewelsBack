document.addEventListener('DOMContentLoaded', function() {
    const filterId = document.getElementById('filterId');
    const filterName = document.getElementById('filterName');
    const filterStatus = document.getElementById('filterStatus');
    const filterDate = document.getElementById('filterDate');
    const filterLimit = document.getElementById('limitSelect');

    const rows = document.querySelectorAll('.order-row');

    if (!filterId || rows.length === 0) return;

    function filterTable() {
        const idVal = filterId.value.toLowerCase();
        const nameVal = filterName.value.toLowerCase();
        const statusVal = filterStatus.value.toLowerCase();
        const dateVal = filterDate.value;

        rows.forEach(row => {
            const colId = row.querySelector('.col-id').innerText.toLowerCase();
            const colName = row.querySelector('.col-name').innerText.toLowerCase();
            const colStatus = row.querySelector('.col-status').getAttribute('data-status');
            const colDateRaw = row.querySelector('.col-date').innerText;

            let matchesDate = true;
            if (dateVal) {
                const [year, month, day] = dateVal.split('-');
                const targetDateStr = `${day}/${month}/${year}`;
                if (!colDateRaw.startsWith(targetDateStr)) {
                    matchesDate = false;
                }
            }

            const matchesId = colId.includes(idVal);
            const matchesName = colName.includes(nameVal);
            const matchesStatus = statusVal === '' || colStatus === statusVal;

            if (matchesId && matchesName && matchesStatus && matchesDate) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    filterId.addEventListener('input', filterTable);
    filterName.addEventListener('input', filterTable);
    filterStatus.addEventListener('change', filterTable);
    filterDate.addEventListener('input', filterTable);
    filterLimit.addEventListener('change', (e) => window.location.href= e.target.value)
});
