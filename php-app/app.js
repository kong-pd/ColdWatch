const API = 'http://localhost:5000';

function getStatus(temp, location) {
  if (location === 'Cold Storage Room') {
    if (temp > 0)  return 'high';
    if (temp < -5) return 'low';
  } else {
    if (temp > 8) return 'high';
    if (temp < 2) return 'low';
  }
  return 'ok';
}

async function updateReadings() {
  const res  = await fetch(`${API}/readings`);
  const data = await res.json();

  const latest = {};
  data.forEach(r => {
    if (!latest[r.sensor_name]) latest[r.sensor_name] = r;
  });

  document.getElementById('sensor-cards').innerHTML = Object.values(latest).map(r => {
    const temp   = parseFloat(r.temperature);
    const status = getStatus(temp, r.location);
    const label  = status === 'high' ? 'HIGH ALERT' : status === 'low' ? 'LOW ALERT' : 'NORMAL';
    return `
      <div class="card ${status}">
        <div style="font-weight:600">${r.sensor_name}</div>
        <div class="meta">${r.location}</div>
        <div class="temp ${status}">${temp}°C</div>
        <span class="badge ${status}">${label}</span>
        <div class="meta" style="margin-top:8px">Updated: ${r.recorded_at}</div>
      </div>`;
  }).join('');
}

async function updateAlerts() {
  const res  = await fetch(`${API}/alerts`);
  const data = await res.json();

  const tbody = document.getElementById('alert-rows');
  if (!data.length) {
    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#64748b">No alerts yet</td></tr>';
    return;
  }
  tbody.innerHTML = data.map(a => `
    <tr>
      <td>${a.created_at}</td>
      <td>${a.sensor_name}</td>
      <td>${a.temperature}°C</td>
      <td><span class="pill ${a.breach_type}">${a.breach_type}</span></td>
      <td>${a.threshold_value}°C</td>
    </tr>`).join('');
}

function refresh() {
  updateReadings();
  updateAlerts();
}

refresh();
setInterval(refresh, 5000);