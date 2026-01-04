document.addEventListener('DOMContentLoaded', () => {
    const enterpriseDropdown = document.getElementById('enterprise-dropdown');
    const metersDropdown = document.getElementById('meter-dropdown');
    const readingsTableBody = document.querySelector('#readings-table-body');
    const accumulatedConsumptionLabel = document.getElementById('accumulatedConsumption-label');
    const waterPressureLabel = document.getElementById('waterPressure-label');

    // Close panel buttons
    document.querySelectorAll('.closeDetailPanel').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('detailPanel').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
            document.body.style.overflow = 'auto';
        });
    });

    // Make the function global
    window.openDetailPanel = function(readingID) {
        fetch(getReadingDetailUrl + '?id=' + readingID)
            .then(response => response.json())
            .then(detail => {
                const detailPanel = document.getElementById('detailPanel');
                const overlay = document.getElementById('overlay');

                // Show panel
                overlay.style.display = 'block';
                detailPanel.style.display = 'block';
                document.body.style.overflow = 'hidden';
                requestAnimationFrame(() => {
                    detailPanel.classList.add('show');
                });

                // Fill the fields
                document.getElementById("detailTechnician").value = detail.technician;
                document.getElementById("detailMeterAddress").value = detail.meterAddress;
                document.getElementById('detailReadingId').value = detail.id;
                document.getElementById('detailReadingValue').value = detail.reading;
                document.getElementById('detailAccumulatedConsumption').value = detail.accumulatedConsumption;
                document.getElementById('detailWaterPressure').value = detail.waterPressure;
                document.getElementById('detailDesc').value = detail.desc;
                document.getElementById('detailDate').value = detail.date;

                if(detail.statusClass === 18){
                    document.getElementById("detailStatusBadge").className = "badge bg-warning px-3 py-2 text-warning";
                    document.getElementById("detailStatusBadge").textContent = "COM PROBLEMAS";
                }else{
                    document.getElementById("detailStatusBadge").className = "badge bg-success px-3 py-2 text-success";
                    document.getElementById("detailStatusBadge").textContent = "SEM PROBLEMAS";
                }

                // Show/hide wrench icon
                const icon = document.getElementById('detailWrenchIcon');
                if (detail.readingType === 1) {
                    document.getElementById("detailProblemContainer").style.display = "block";
                    document.getElementById("detailProblemType").value = detail.problemType;
                    icon.style.display = 'inline-block';
                } else {
                    document.getElementById("detailProblemContainer").style.display = "none";
                    icon.style.display = 'none';
                }
            })
            .catch(err => console.error('Error loading detail reading:', err));
    }

    function loadReadingsEnterprise(meterIds) {
        const fetchPromises = meterIds.map(id =>
            fetch(getReadingsUrl + '?meterID=' + id).then(response => response.json())
        );

        Promise.all(fetchPromises)
            .then(results => {
                const allReadings = results.flat();
                readingsTableBody.innerHTML = '';

                if (allReadings.length === 0) {
                    readingsTableBody.innerHTML = `<tr><td colspan="4" class="text-muted text-center">Sem leituras.</td></tr>`;
                    return;
                }

                let allConsumption = 0;
                let averagePressureTotal = 0;

                allReadings.forEach(r => {
                    allConsumption += parseInt(r.accumulatedConsumption, 10);
                    averagePressureTotal += parseInt(r.waterPressure, 10);

                    readingsTableBody.insertAdjacentHTML('beforeend', `
                        <tr>
                            <td>${r.id} ${r.wasFix ? `<i class="fas fa-wrench ms-2"></i>` : ''}</td>
                            <td>${r.value} m³</td>
                            <td>${r.readingDate}</td>
                            <td>
                                <button class="btn btn-outline-primary btn-sm fw-semibold shadow-sm"
                                        style="transition: all 0.2s ease-in-out;"
                                        onmouseover="this.style.transform='scale(1.05)';"
                                        onmouseout="this.style.transform='scale(1)';"
                                        onclick="openDetailPanel(${r.id})">
                                    Ver Detalhes
                                </button>
                            </td>
                        </tr>
                    `);
                });

                const averagePressure = (averagePressureTotal / allReadings.length).toFixed(2);
                accumulatedConsumptionLabel.innerHTML = allConsumption;
                waterPressureLabel.innerHTML = averagePressure;
            })
            .catch(err => console.error('Error loading readings:', err));
    }

    enterpriseDropdown.addEventListener('change', function () {
        const enterpriseId = this.value;

        // Clear existing items
        metersDropdown.innerHTML = '<option>Carregando...</option>';

        // Yii-generated URL
        fetch(getMetersUrl + '?enterpriseID=' + enterpriseId)
            .then(response => response.json())
            .then(data => {
                const meterIds = [];

                // Clear existing options
                metersDropdown.innerHTML = '';

                // Add placeholder
                metersDropdown.insertAdjacentHTML('beforeend',
                    `<option value="">Selecione um contador</option>`
                );

                // Insert the meters
                data.forEach(meter => {
                    meterIds.push(meter.id);
                    metersDropdown.insertAdjacentHTML('beforeend',
                        `<option value="${meter.id}">${meter.name}</option>`
                    );
                });

                loadReadingsEnterprise(meterIds);
            })
            .catch(err => console.error('Error loading meters:', err));
    });

    metersDropdown.addEventListener('change', function () {
        const meterId = this.value;
        if (!meterId) return;

        fetch(getReadingsUrl + '?meterID=' + meterId)
            .then(response => response.json())
            .then(readings => {
                // Clear table rows
                readingsTableBody.innerHTML = '';

                if (readings.length === 0) {
                    readingsTableBody.innerHTML =
                        `<tr><td colspan="4" class="text-muted text-center">Sem leituras.</td></tr>`;
                    return;
                }

                allConsumption = 0;
                averagePressureTotal = 0;
                averagePressureQuantity = 0;
                // Insert readings into table
                readings.forEach(r => {
                    allConsumption += parseInt(r.accumulatedConsumption, 10);
                    averagePressureTotal += parseInt(r.waterPressure, 10);
                    readingsTableBody.insertAdjacentHTML('beforeend', `
                    <tr>
                        <td>${r.id} ${r.wasFix ? `<i class="fas fa-wrench ms-2"></i>` : ``}</td>
                        <td>${r.value} m³</td>
                        <td>${r.readingDate}</td>
                        <td> 
                            <button class="btn btn-outline-primary btn-sm fw-semibold shadow-sm"
                                    style="transition: all 0.2s ease-in-out;"
                                    onmouseover="this.style.transform='scale(1.05)';"
                                    onmouseout="this.style.transform='scale(1)';"
                                    onclick="openDetailPanel(${r.id})">
                                Ver Detalhes
                            </button>
                        </td>
                    </tr>
                `);
                });

                averagePressure = averagePressureTotal / readings.length;
                averagePressure = averagePressure.toFixed(2);
                accumulatedConsumptionLabel.innerHTML = allConsumption;
                waterPressureLabel.innerHTML = averagePressure;
            });
    });
});
